const puppeteer = require("puppeteer");
const fs = require("fs");

const USER_DATA_DIR = "/tmp/session";

if (!fs.existsSync(USER_DATA_DIR)) {
  fs.mkdirSync(USER_DATA_DIR, { recursive: true });
  console.log(`Created user data directory at ${USER_DATA_DIR}`);
}

async function processURLWithBot(url) {
  const browser = await puppeteer.launch({
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-popup-blocking',
      '--disable-background-networking',
      '--disable-default-apps',
      '--disable-extensions',
      '--disable-gpu',
      '--disable-sync',
      '--disable-translate',
      '--hide-scrollbars',
      '--metrics-recording-only',
      '--mute-audio',
      '--no-first-run',
      '--safebrowsing-disable-auto-update',
      '--js-flags=--noexpose_wasm,--jitless'
    ],
    userDataDir: USER_DATA_DIR,
  });
  const page = await browser.newPage();

  try {
    const adminPassword = process.env.ADMIN_PASSWORD;
    if (!adminPassword) {
      throw new Error("Admin password not set in environment variables.");
    }

    await page.goto("http://127.0.0.1:1337/");
    console.log(await browser.cookies());
    if (page.url() != "http://127.0.0.1:1337/") {
      console.log("loggingin IN");
      await page.type('input[name="username"]', "admin");
      await page.type('input[name="password"]', adminPassword);

      await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: "networkidle0" }),
      ]);
      console.log(await browser.cookies());

    } else {
      console.log("already logged in")
      console.log(await page.url());
    }

    await page.goto(url, { waitUntil: "networkidle0" });

    await new Promise(resolve => setTimeout(resolve, 5000));
  } catch (err) {
    console.error(`Bot encountered an error while processing URL ${url}:`, err);
  } finally {
    await browser.close();
  }
}

module.exports = { processURLWithBot };
