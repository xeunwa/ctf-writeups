const { Pool } = require("pg");
const validator = require("validator");

// Single PostgreSQL connection pool (using the unified DB_USER and DB_PASSWORD)
const db = new Pool({
  user: process.env.DB_USER,         // Now, for example, "postgres"
  host: process.env.DB_HOST,
  database: process.env.DB_NAME,
  password: process.env.DB_PASSWORD,   // The password for the single DB user
  port: process.env.DB_PORT || 5432,
  ssl: false, // Change this if using a secure connection
});

// **Function to Run Read-Only Queries**
async function runReadOnlyQuery(query, params = []) {
  try {
    const client = await db.connect();
    const result = await client.query(query, params);
    client.release();
    return result.rows;
  } catch (error) {
    console.error("PostgreSQL Query Error:", error);
    throw error;
  }
}

// Initialize the database schema
async function initializeDatabase() {
  try {
    // Create users table
    await db.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
      )
    `);

    // Create submissions table
    await db.query(`
      CREATE TABLE IF NOT EXISTS submissions (
        id SERIAL PRIMARY KEY,
        name TEXT NOT NULL,
        description TEXT,
        url TEXT NOT NULL,
        category TEXT,
        userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        approved BOOLEAN DEFAULT FALSE,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create auctions table
    await db.query(`
      CREATE TABLE IF NOT EXISTS auctions (
        id SERIAL PRIMARY KEY,
        resourceId INTEGER NOT NULL REFERENCES submissions(id) ON DELETE CASCADE,
        startingBid REAL NOT NULL,
        currentBid text DEFAULT NULL,
        endTime TIMESTAMP NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create bids table
    await db.query(`
      CREATE TABLE IF NOT EXISTS bids (
        id SERIAL PRIMARY KEY,
        auctionId INTEGER NOT NULL REFERENCES auctions(id) ON DELETE CASCADE,
        userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        amount TEXT NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    console.log("Database initialized.");
    // Ensure admin user
    await initializeAdminUser();
  } catch (error) {
    console.error("Database initialization error:", error);
  }
}

// Create or fetch admin user
async function initializeAdminUser() {
  const adminUsername = "admin";
  const adminPassword = process.env.ADMIN_PASSWORD; // Fetch from .env

  if (!adminPassword) {
    console.error("ERROR: ADMIN_PASSWORD is not set in environment variables.");
    return;
  }

  try {
    const result = await db.query("SELECT id FROM users WHERE username = $1", [adminUsername]);
    if (result.rows.length > 0) {
      console.log(`Admin user '${adminUsername}' already exists.`);
      return;
    }

    // Store the password in plain text (for this example)
    await db.query(
      "INSERT INTO users (username, password) VALUES ($1, $2)",
      [adminUsername, adminPassword]
    );

    console.log("Admin user created successfully.");
    insertSampleData();
  } catch (error) {
    console.error("Error creating admin user:", error);
  }
}

// Function to create a new user (stores password in plain text)
async function createUser(username, password) {
  const result = await db.query(
    "INSERT INTO users (username, password) VALUES ($1, $2) RETURNING id",
    [username, password]
  );
  return result.rows[0].id;
}

// Function to verify user credentials
async function verifyUserCredentials(username, password) {
  const user = await getUserByUsername(username);
  if (!user.length) return { isValid: false, user: null };
  return { isValid: password === user[0].password, user: user[0] };
}

// Function to get user by username (using read-only access)
async function getUserByUsername(username) {
  return await runReadOnlyQuery("SELECT * FROM users WHERE username = $1", [username]);
}

// Function to get user by ID (using read-only access)
async function getUserById(id) {
  return await runReadOnlyQuery("SELECT * FROM users WHERE id = $1", [id]);
}

// *** NEW: Function to create a submission ***
async function createSubmission({ name, description, url, category, userId }) {
  // By default, submissions are not approved.
  const result = await db.query(
    "INSERT INTO submissions (name, description, url, category, userId, approved) VALUES ($1, $2, $3, $4, $5, false) RETURNING *",
    [name, description, url, category, userId]
  );
  return result.rows[0];
}

// Function to fetch all submissions by a user (Read-Only)
async function getSubmissionsByUser(userId) {
  return await runReadOnlyQuery(
    "SELECT * FROM submissions WHERE userId = $1 ORDER BY createdAt DESC",
    [userId]
  );
}

// Function to approve a submission (Admin)
async function approveSubmission(id) {
  await db.query(
    "UPDATE submissions SET approved = TRUE, updatedAt = CURRENT_TIMESTAMP WHERE id = $1",
    [id]
  );
  return runReadOnlyQuery("SELECT * FROM submissions WHERE id = $1", [id]);
}

// Function to create an auction
async function createAuction({ resourceId, startingBid, endTime }) {
  const result = await db.query(
    "INSERT INTO auctions (resourceId, startingBid, endTime) VALUES ($1, $2, $3) RETURNING *",
    [resourceId, startingBid, endTime]
  );
  return result.rows[0];
}

// Function to fetch all auctions (Read-Only)
async function getAllAuctions() {
  return await runReadOnlyQuery(
    `SELECT auctions.*, submissions.name AS resourceName
     FROM auctions JOIN submissions ON auctions.resourceId = submissions.id
     ORDER BY auctions.endTime DESC`
  );
}

// Function to place a bid
async function placeBid(auctionId, userId, bidAmount) {
  await db.query(
    "INSERT INTO bids (auctionId, userId, amount) VALUES ($1, $2, $3)",
    [auctionId, userId, bidAmount]
  );
  await db.query(
    "UPDATE auctions SET currentBid = (SELECT MAX(amount) FROM bids WHERE auctionId = $1) WHERE id = $1",
    [auctionId]
  );
}

// Function to fetch all bids for a given auction (for the API)
async function getBidsForAuction(auctionId) {
  return await runReadOnlyQuery(
    `SELECT b.*, s.name as resourceName, u.username as bidder
     FROM bids b
     JOIN auctions a ON b.auctionId = a.id
     JOIN submissions s ON a.resourceId = s.id
     JOIN users u ON b.userId = u.id
     WHERE b.auctionId = $1
     ORDER BY b.createdAt DESC`,
    [auctionId]
  );
}

// Function to fetch all bids placed by a specific user (for the dashboard)
async function getBidsByUser(userId) {
  return await runReadOnlyQuery(
    `SELECT b.*, s.name as resourceName, u.username as bidder
     FROM bids b
     JOIN auctions a ON b.auctionId = a.id
     JOIN submissions s ON a.resourceId = s.id
     JOIN users u ON b.userId = u.id
     WHERE b.userId = $1
     ORDER BY b.createdAt ASC`,
    [userId]
  );
}

// Function to fetch auction details by id with bids attached
async function getAuctionById(id) {
  const auctions = await runReadOnlyQuery(
    `SELECT auctions.*, submissions.name AS resourceName
     FROM auctions JOIN submissions ON auctions.resourceId = submissions.id
     WHERE auctions.id = $1`,
    [id]
  );
  if (auctions.length === 0) return [];
  const auction = auctions[0];
  auction.bids = await getBidsForAuction(id);
  return [auction];
}

// Function to insert sample data (if needed)
async function insertSampleData() {
  try {
    const adminUsers = await getUserByUsername("admin");
    if (!adminUsers || adminUsers.length === 0) {
      throw new Error("Admin user not found; cannot insert sample data.");
    }
    const adminId = adminUsers[0].id;

    const sampleSubmissions = [
      {
        name: "Portal of Helios",
        description: "A mysterious portal that appeared at 1:32 AM UTC when Helios took control, trapping players in digital limbo.",
        url: "http://aurors.htb/portal-helios",
        category: "artifacts"
      },
      {
        name: "Sword of Malakar",
        description: "A blade rumored to have been forged in defiance of the dark ruler Malakar.",
        url: "http://aurors.htb/sword-malakar",
        category: "weapons"
      },
      {
        name: "Shield of Eternity",
        description: "A shield said to protect its bearer from the relentless march of time.",
        url: "http://aurors.htb/shield-eternity",
        category: "armory"
      },
      {
        name: "Crystal of Fate",
        description: "A shimmering crystal that foretells the destiny of those who dare to gaze upon it.",
        url: "http://aurors.htb/crystal-fate",
        category: "artifacts"
      },
      {
        name: "Orb of Destiny",
        description: "An orb pulsating with enigmatic energy, believed to alter the course of events.",
        url: "http://aurors.htb/orb-destiny",
        category: "artifacts"
      },
      {
        name: "Crown of Shadows",
        description: "A crown imbued with dark power, coveted by those who seek to control the night.",
        url: "http://aurors.htb/crown-shadows",
        category: "royalty"
      },
      {
        name: "Ring of Transcendence",
        description: "A ring that grants its wearer a glimpse beyond the ordinary realm.",
        url: "http://aurors.htb/ring-transcendence",
        category: "jewelry"
      },
      {
        name: "Amulet of Lost Souls",
        description: "An amulet that whispers the secrets of forgotten spirits.",
        url: "http://aurors.htb/amulet-lost-souls",
        category: "mystic"
      },
      {
        name: "Staff of the Ancients",
        description: "A powerful staff once wielded by the mages of old to channel arcane energies.",
        url: "http://aurors.htb/staff-ancients",
        category: "weapons"
      },
      {
        name: "Boots of the Wanderer",
        description: "Boots that grant the wearer unmatched speed across the lands of Eldoria.",
        url: "http://aurors.htb/boots-wanderer",
        category: "equipment"
      }
    ];

    for (let i = 0; i < sampleSubmissions.length; i++) {
      const sub = sampleSubmissions[i];
      const submissionResult = await db.query(
        "INSERT INTO submissions (name, description, url, category, userId, approved) VALUES ($1, $2, $3, $4, $5, true) RETURNING id",
        [sub.name, sub.description, sub.url, sub.category, adminId]
      );
      const submissionId = submissionResult.rows[0].id;

      const startingBid = 100 * (i + 1);
      const endTime = "2025-12-31 23:59:59";
      const auctionResult = await db.query(
        "INSERT INTO auctions (resourceId, startingBid, endTime) VALUES ($1, $2, $3) RETURNING id",
        [submissionId, startingBid, endTime]
      );
      const auctionId = auctionResult.rows[0].id;

      await db.query(
        "INSERT INTO bids (auctionId, userId, amount) VALUES ($1, $2, $3)",
        [auctionId, adminId, startingBid + 50]
      );
      await db.query(
        "INSERT INTO bids (auctionId, userId, amount) VALUES ($1, $2, $3)",
        [auctionId, adminId, startingBid + 100]
      );
    }
    console.log("Sample data inserted successfully.");
  } catch (error) {
    console.error("Error inserting sample data:", error);
  }
}

initializeDatabase();

module.exports = {
  db,
  getBidsForAuction,
  getBidsByUser,
  getUserByUsername,
  getUserById,
  createUser,
  verifyUserCredentials,
  createSubmission,  // <-- Exported createSubmission function
  getSubmissionsByUser,
  approveSubmission,
  createAuction,
  getAllAuctions,
  placeBid,
  getAuctionById,
  runReadOnlyQuery
};
