require('dotenv').config();
const express = require('express');
const session = require('express-session');
const path = require('path');
const nunjucks = require('nunjucks');
const crypto = require('crypto');  // Import crypto module
const viewRoutes = require('./routes/views');
const apiRoutes = require('./routes/api');
const adminRoutes = require('./routes/admin');

const sessionSecret = process.env.SESSION_SECRET || crypto.randomBytes(32).toString('hex');

// Initialize Express
const app = express();

// Nunjucks setup
nunjucks.configure('views', {
    autoescape: true,
    express: app,
    watch: true, // optional in dev
});

// Parse request bodies
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Session management
app.use(session({
    secret: sessionSecret,
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        sameSite: 'lax',
        maxAge: 24 * 60 * 60 * 1000
    }
}));

// Static files
app.use(express.static(path.join(__dirname, 'public')));

// Routes
app.use('/', viewRoutes);
app.use('/api', apiRoutes);
app.use('/', adminRoutes);

// Start server
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
