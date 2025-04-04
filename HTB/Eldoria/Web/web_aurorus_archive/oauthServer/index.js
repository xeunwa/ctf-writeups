// index.js
require('dotenv').config();
const bodyParser = require('body-parser');
const express = require('express');
const session = require('express-session');
const OAuthServer = require('@node-oauth/express-oauth-server'); // Corrected import
const createModel = require('./model');
const DB = require('./db');
const bcrypt = require('bcrypt');
const crypto = require('crypto');
const path = require('path');

const db = new DB();
const app = express();

// Initialize the OAuth server with @node-oauth/express-oauth-server
app.oauth = new OAuthServer({
  model: createModel(db),
  allowBearerTokensInQueryString: true,
  accessTokenLifetime: 3600, // 1 hour
  refreshTokenLifetime: 1209600 // 14 days
});

// Initialize the system client with an absolute redirect URI
db.saveClient({
  id: process.env.CLIENT_ID,
  secret: process.env.CLIENT_SECRET,
  grants: ['password', 'client_credentials', 'authorization_code'],
  redirectUris: [process.env.OAUTH_REDIRECT_URI || '/callback'],
  allowedScopes: ['read', 'write']
});

// Middleware setup
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

const sessionSecret = process.env.SESSION_SECRET || crypto.randomBytes(32).toString('hex');

app.use(session({
  secret: sessionSecret,
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'Strict'
  }
}));

// Set view engine to EJS and set views directory
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// -----------------------
// Authentication Routes
// -----------------------

// GET /oauth/register - Show registration form
app.get('/oauth/register', (req, res) => {
  res.render('register', { error: null });
});

// POST /oauth/register - Handle user registration
app.post('/oauth/register', async (req, res) => {
  const { username, password } = req.body;
  const existingUser = db.findUserByUsername(username);
  if (existingUser) {
    return res.render('register', { error: 'Username already exists.' });
  }
  if (!username || !password) {
    return res.render('register', { error: 'Username and password are required.' });
  }
  try {
    const user = await db.saveUser({ username, password });
    req.session.user = user;
    res.redirect('/oauth/user-info');
  } catch (err) {
    console.error(err);
    res.render('register', { error: 'Registration failed. Please try again.' });
  }
});

// GET /oauth/ - Show login form
app.get('/oauth/', (req, res) => {
  res.render('login', { error: null });
});

// POST /oauth/login - Handle user login
app.post('/oauth/login', async (req, res) => {
  const { username, password } = req.body;
  const user = db.findUserByUsername(username);
  if (!user) {
    return res.render('login', { error: 'Invalid username or password.' });
  }
  const match = await bcrypt.compare(password, user.password);
  if (!match) {
    return res.render('login', { error: 'Invalid username or password.' });
  }
  req.session.user = user;

  // Check if there is an ongoing authorization request
  if (req.session.authRequest) {
    const { query } = req.session.authRequest;
    req.session.authRequest = null;
    return res.redirect(`/oauth/authorize?${query}`);
  }

  res.redirect('/oauth/user-info');
});

// GET /oauth/logout - Handle user logout
app.get('/oauth/logout', (req, res) => {
  req.session.destroy();
  res.redirect('/oauth/');
});

// GET /oauth/user-info - Display user information (Protected Route)
app.get('/oauth/user-info', ensureAuthenticated, (req, res) => {
  res.render('user-info', { user: req.session.user });
});

// -----------------------
// OAuth Routes
// -----------------------

// GET /oauth/authorize - Authorization endpoint
app.get('/oauth/authorize', ensureAuthenticated, (req, res) => {
  const { response_type, client_id, redirect_uri, scope, state } = req.query;

  // Validate client_id
  const client = db.findClient(client_id);
  if (!client) {
    return res.status(400).send('Invalid client_id');
  }

  // Validate redirect_uri
  if (!client.redirectUris.includes(redirect_uri)) {
    return res.status(400).send('Invalid redirect_uri');
  }

  if (response_type !== 'code') {
    return res.status(400).send('Unsupported response_type');
  }

  // Render consent form
  res.render('consent', {
    client,
    scope,
    state,
    redirect_uri,
    response_type,
    client_id
  });
});

// POST /oauth/authorize - Handle consent decision
app.post('/oauth/authorize', ensureAuthenticated, async (req, res) => {
  const { approve, deny, response_type, client_id, redirect_uri, scope, state } = req.body;
  console.log(req.body);

  // Validate client_id
  const client = db.findClient(client_id);
  if (!client) {
    return res.status(400).send('Invalid client_id');
  }

  // Validate redirect_uri
  if (!client.redirectUris.includes(redirect_uri)) {
    return res.status(400).send('Invalid redirect_uri');
  }

  // Validate response_type
  if (response_type !== 'code') {
    return res.status(400).send('Unsupported response_type');
  }

  if (deny) {
    const error = 'access_denied';
    const error_description = 'The user denied access to the application.';
    const redirectUrl = `${redirect_uri}?error=${encodeURIComponent(error)}&error_description=${encodeURIComponent(error_description)}${state ? `&state=${encodeURIComponent(state)}` : ''}`;
    return res.redirect(redirectUrl);
  }

  if (approve) {
    // Generate authorization code
    const code = crypto.randomBytes(20).toString('hex');

    // Save authorization code
    const user = req.session.user;
    db.saveAuthorizationCode(code, client, user, scope);

    // Redirect back to client with code and state
    const redirectUrl = `${redirect_uri}?code=${encodeURIComponent(code)}${state ? `&state=${encodeURIComponent(state)}` : ''}`;
    return res.redirect(redirectUrl);
  }

  // If neither approve nor deny, redirect back with error
  const error = 'invalid_request';
  const error_description = 'Invalid consent response.';
  const redirectUrl = `${redirect_uri}?error=${encodeURIComponent(error)}&error_description=${encodeURIComponent(error_description)}${state ? `&state=${encodeURIComponent(state)}` : ''}`;
  return res.redirect(redirectUrl);
});

// Token endpoint using @node-oauth/express-oauth-server's middleware
app.post('/oauth/token', app.oauth.token());

app.get('/oauth/user-info/json', async (req, res) => {
  const authHeader = req.headers['authorization'];
  if (!authHeader) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  const tokenMatch = authHeader.match(/^Bearer (.+)$/);
  if (!tokenMatch) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  const accessToken = tokenMatch[1];
  const tokenMeta = db.findAccessToken(accessToken);

  if (!tokenMeta) {
    return res.status(401).json({ error: 'Invalid token' });
  }

  const user = db.findUserById(tokenMeta.userId);
  if (!user) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  res.json({
    id: user.id,
    username: user.username,
    // Add other user fields as necessary
  });
});

// -----------------------
// Start Server
// -----------------------
const PORT = process.env.OAUTH_PORT || 8080;
app.listen(PORT, () => {
  console.debug(`[Provider]: listens to http://localhost:${PORT}`);
});

// -----------------------
// Helper Middleware
// -----------------------
function ensureAuthenticated(req, res, next) {
  if (req.session.user) {
    return next();
  }
  // Save the original request for after login
  req.session.authRequest = {
    query: req.url.split('?')[1] || ''
  };
  res.redirect('/oauth/');
}
