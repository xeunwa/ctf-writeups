import express from 'express';
import session from 'express-session';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import nunjucks from 'nunjucks';
import crypto from 'crypto';
import router from './routes.js';
import { seedDatabase } from './db.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const app = express();

// Configure nunjucks to look for templates in the "views" folder.
const env = nunjucks.configure('views', {
  autoescape: true,
  express: app
});

// Add custom filters for number formatting and dates.
env.addFilter('number', (value) => Number(value).toLocaleString());
env.addFilter('date', (value, format) => new Date(value).toLocaleString());
env.addFilter('json', (value) => JSON.stringify(value));

// Set nunjucks as the view engine.
app.set('view engine', 'njk');

// Generate a random session secret using crypto if not provided by environment.
const sessionSecret = process.env.SESSION_SECRET || crypto.randomBytes(32).toString('hex');

// Middleware for parsing bodies and sessions.
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(session({
  secret: sessionSecret,
  resave: false,
  saveUninitialized: false
}));

// Serve static files from the "public" folder.
app.use(express.static(join(__dirname, 'public')));

// Mount our routes.
app.use(router);

// You can choose to seed the database if needed.
seedDatabase().then(() => {
  const PORT = process.env.PORT || 3000;
  app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
  });
});
