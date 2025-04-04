// routes/api.js
const express = require('express');
const {
  verifyUserCredentials,
  createUser,
  getUserByUsername,
  createSubmission,
  getSubmissionsByUser,
  approveSubmission,
  createAuction,
  getAllAuctions,
  getAuctionById,
  placeBid,
  getBidsForAuction
} = require('../db');
const { processURLWithBot } = require("../bot");
const axios = require('axios');

const router = express.Router();

function isAuthenticated(req, res, next) {
  if (req.session.userId) {
    return next();
  }
  return res.status(401).json({ success: false, message: 'Unauthorized' });
}

function isAdmin(req, res, next) {
  if (res.locals.username === 'admin') {
    return next();
  }
  return res.status(403).json({ success: false, message: 'Forbidden: admin only.' });
}

router.get('/config', (req, res) => {
  res.json({ oauthClientId: process.env.OAUTH_CLIENT_ID });
});


router.post('/oauthLogin', async (req, res) => {
  const { code } = req.body;
  try {
    // 1) Exchange the authorization code for an access token
    const tokenResponse = await axios.post(
      process.env.OAUTH_TOKEN_URL,
      new URLSearchParams({
        grant_type: 'authorization_code',
        code: code,
        redirect_uri: process.env.OAUTH_REDIRECT_URI,
        client_id: process.env.OAUTH_CLIENT_ID,
        client_secret: process.env.OAUTH_CLIENT_SECRET,
      }),
      {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      }
    );

    const accessToken = tokenResponse.data.access_token;
    if (!accessToken) {
      return res.status(400).json({
        success: false,
        message: 'No access token returned.',
      });
    }

    const userInfoResponse = await axios.get(process.env.OAUTH_USERINFO_URL, {
      headers: { Authorization: `Bearer ${accessToken}` },
    });
    const oauthUser = userInfoResponse.data;
    const existingUser = await getUserByUsername(oauthUser.username);
    if (!existingUser || existingUser.length === 0) {
      const newUserId = await createUser(oauthUser.username, 'oauth_default_password');
      req.session.userId = newUserId;
      return res.json({
        success: true,
        message: 'OAuth login successful.',
      });
    } else {
      req.session.userId = existingUser[0].id;
      return res.json({
        success: true,
        message: 'OAuth login successful.',
      });
    }
  } catch (error) {
    console.error('OAuth Login Error:', error.response?.data || error.message);
    return res
      .status(500)
      .json({ success: false, message: 'OAuth login failed.' });
  }
});

router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    if (!username || !password) {
      return res.status(400).json({ success: false, message: 'Username and password are required.' });
    }

    const { isValid, user } = await verifyUserCredentials(username, password);
    if (!isValid || !user) {
      return res.status(401).json({ success: false, message: 'Invalid username or password.' });
    }

    req.session.userId = user.id;
    return res.json({ success: true, message: 'Login successful.' });
  } catch (err) {
    console.error('Error during login:', err);
    return res.status(500).json({ success: false, message: 'Internal server error.' });
  }
});

router.get('/submissions', isAuthenticated, async (req, res) => {
  try {
    const submissions = await getSubmissionsByUser(req.session.userId);
    return res.json({ success: true, submissions });
  } catch (err) {
    console.error('Error fetching submissions:', err);
    return res.status(500).json({ success: false, message: 'Internal server error.' });
  }
});

router.post('/submissions', isAuthenticated, async (req, res) => {
  try {
    const userId = req.session.userId;
    const { name, description, url, category } = req.body;
    const newSubmission = await createSubmission({ name, description, url, category, userId });

    res.status(201).json({ success: true, submission: newSubmission });

    setImmediate(async () => {
      try {
        console.log(`Processing URL in the background: ${url}`);
        await processURLWithBot(url);
      } catch (botError) {
        console.error("Bot encountered an error:", botError);
      }
    });
  } catch (err) {
    console.error('Error creating submission:', err);
    const message = err.message.includes('required') || err.message.includes('Invalid URL')
      ? err.message
      : 'Internal server error.';
    return res.status(500).json({ success: false, message });
  }
});

router.put('/submissions/:id/approve', isAuthenticated, isAdmin, async (req, res) => {
  try {
    const submissionId = req.params.id;
    const updatedSubmission = await approveSubmission(submissionId);
    return res.json({ success: true, submission: updatedSubmission });
  } catch (err) {
    console.error('Error approving submission:', err);
    const status = err.message.includes('not found') ? 404 : 500;
    return res.status(status).json({ success: false, message: err.message || 'Internal server error.' });
  }
});

router.post('/auctions', isAuthenticated, isAdmin, async (req, res) => {
  try {
    const { resourceId, startingBid, endTime } = req.body;
    const newAuction = await createAuction({ resourceId, startingBid, endTime });
    return res.status(201).json({ success: true, auction: newAuction });
  } catch (err) {
    console.error('Error creating auction:', err);
    const message = err.message.includes('required') || err.message.includes('Invalid')
      ? err.message
      : 'Internal server error.';
    return res.status(500).json({ success: false, message });
  }
});

router.get('/auctions', async (req, res) => {
  try {
    const auctions = await getAllAuctions();
    return res.json({ success: true, auctions });
  } catch (err) {
    console.error('Error fetching auctions:', err);
    return res.status(500).json({ success: false, message: 'Internal server error.' });
  }
});

router.get('/auctions/:id', async (req, res) => {
  try {
    const auctionId = req.params.id;
    const auction = await getAuctionById(auctionId);
    if (!auction) {
      return res.status(404).json({ success: false, message: 'Auction not found.' });
    }
    return res.json({ success: true, auction });
  } catch (err) {
    console.error('Error fetching auction:', err);
    return res.status(500).json({ success: false, message: 'Internal server error.' });
  }
});

router.post('/auctions/:id/bids', isAuthenticated, async (req, res) => {
  try {
    const auctionId = req.params.id;
    const userId = req.session.userId;
    const { bid } = req.body;

    if (bid.length > 10) {
      return res.status(400).json({ success: false, message: 'Too long' });
    }
    await placeBid(auctionId, userId, bid);
    return res.json({ success: true });
  } catch (err) {
    console.error('Error placing bid:', err);
    const status = err.message.includes('Invalid') ? 400
                  : (err.message.includes('not found') || err.message.includes('closed')) ? 404
                  : 500;
    return res.status(status).json({ success: false, message: err.message || 'Internal server error.' });
  }
});

router.get('/auctions/:id/bids', async (req, res) => {
  try {
    const auctionId = req.params.id;
    const bids = await getBidsForAuction(auctionId);
    return res.json({ success: true, bids });
  } catch (err) {
    console.error('Error fetching bids:', err);
    return res.status(500).json({ success: false, message: 'Internal server error.' });
  }
});

module.exports = router;
