// routes/admin.js
const express = require("express");
const router = express.Router();
const { runReadOnlyQuery } = require("../db");

// Middleware: Check if user is admin
async function isAdmin(req, res, next) {
  // This middleware expects that a previous middleware (in views.js) has set req.session.username
  if (!req.session.userId || req.session.username !== "admin") {
    return res
      .status(403)
      .json({ success: false, message: "Forbidden: Admins only" });
  }
  next();
}

// Serve Admin Panel UI
router.get("/admin", isAdmin, (req, res) => {
  res.render("admin.html", { title: "Admin Panel" });
});

// Endpoint: Get list of tables (PostgreSQL version)
router.get("/tables", isAdmin, async (req, res) => {
  try {
    // PostgreSQL query to list tables in the 'public' schema
    const tables = await runReadOnlyQuery(`
      SELECT table_name
      FROM information_schema.tables
      WHERE table_schema = 'public'
        AND table_type = 'BASE TABLE'
      ORDER BY table_name;
    `);
    res.json({ success: true, tables });
  } catch (error) {
    console.error("Fetching Tables Error:", error);
    res
      .status(500)
      .json({ success: false, message: "Error fetching tables" });
  }
});

// New Endpoint: Get all records from a specified table (POST version)
router.post("/table", isAdmin, async (req, res) => {
  const { tableName } = req.body;
  try {
    const query = `SELECT * FROM "${tableName}"`;

    if (query.includes(';')) {
      return res
        .status(400)
        .json({ success: false, message: "Multiple queries not allowed!" });
    }

    const results = await runReadOnlyQuery(query);
    res.json({ success: true, results });
  } catch (error) {
    console.error("Table Query Error:", error);
    res.status(500).json({
      success: false,
      message: "Error fetching table data.",
    });
  }
});

module.exports = router;
