// routes/views.js
const express = require('express');
const router = express.Router();
const {
  getUserById,
  getSubmissionsByUser,
  getBidsByUser,
  getAllAuctions,
  getAuctionById
} = require('../db');

// Middleware: Attach user data if logged in (using async/await)
router.use(async (req, res, next) => {
  if (req.session.userId) {
    try {
      // Note: getUserById returns an array of rows
      const userRows = await getUserById(req.session.userId);
      if (userRows && userRows.length > 0) {
        res.locals.username = userRows[0].username;
        // Optionally, you can also set req.session.username for later use
        req.session.username = userRows[0].username;
      }
    } catch (error) {
      console.error('Error fetching user:', error);
    }
  }
  next();
});

// New route for My Bids
router.get('/my-bids', async (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  try {
    const bids = await getBidsByUser(req.session.userId);
    console.log(bids)
    res.render('my_bids.html', {
      title: 'My Bids',
      bids
    });
  } catch (error) {
    console.error('Error fetching bids:', error);
    res.render('my_bids.html', { title: 'My Bids', bids: [] });
  }
});

// New route for My Submissions
router.get('/my-submissions', async (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  try {
    const submissions = await getSubmissionsByUser(req.session.userId);
    res.render('my_submissions.html', {
      title: 'My Submissions',
      submissions
    });
  } catch (error) {
    console.error('Error fetching submissions:', error);
    res.render('my_submissions.html', { title: 'My Submissions', submissions: [] });
  }
});

// GET / -> Dashboard: show auctions
router.get('/', async (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  try {
    // Fetch all auctions for display on the dashboard.
    const auctions = await getAllAuctions();
    // Pass auctions data to dashboard.html
    res.render('dashboard.html', {
      title: 'Dashboard',
      auctions
    });
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    res.render('dashboard.html', { auctions: [] });
  }
});




// GET /auction/:id -> a dedicated detail page if needed
router.get('/auction/:id', async (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  const auctionId = req.params.id;
  try {
    // Assuming getAuctionById returns an array; adjust if it returns a single object
    const auctionRows = await getAuctionById(auctionId);
    if (!auctionRows || auctionRows.length === 0) {
      console.log("No such auction ID:", auctionId);
      return res.redirect("/auction");
    }
    const auction = auctionRows[0];
    res.render("auction_details.html", {
      title: "Auction Details",
      auction
    });
  } catch (error) {
    console.error("Error fetching auction details:", error);
    return res.redirect("/auction");
  }
});

// GET /callback
router.get('/callback', (req, res) => {
  res.render('callback.html', { title: 'OAuth Callback' });
});

// GET /login
router.get('/login', (req, res) => {
  res.render('login.html', { title: 'Login' });
});

// GET /submit
router.get('/submit', (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  res.render('submit.html', { title: 'Submit Resource' });
});

// GET /logout
router.get('/logout', (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  req.session.destroy(err => {
    if (err) {
      console.error('Error during logout:', err);
      return res.status(500).json({ success: false, message: 'Internal server error.' });
    }
    res.clearCookie('connect.sid');
    return res.redirect('/login');
  });
});

module.exports = router;
