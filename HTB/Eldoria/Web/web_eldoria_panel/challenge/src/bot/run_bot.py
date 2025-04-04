import sys
import time
import sqlite3
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options

def main():
	if len(sys.argv) < 2:
		print("No quest URL provided.", file=sys.stderr)
		sys.exit(1)
	quest_url = sys.argv[1]

	DB_PATH = "/app/data/database.sqlite"

	conn = sqlite3.connect(DB_PATH)
	c = conn.cursor()

	c.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")
	if not c.fetchone():
		print("The 'users' table doesn't exist. Run seed script or create it here.")
		sys.exit(1)

	c.execute("SELECT username, password FROM users WHERE is_admin = 1 LIMIT 1")
	admin = c.fetchone()
	if not admin:
		print("Admin not found in the database.", file=sys.stderr)
		sys.exit(1)

	admin_username, admin_password = admin

	chrome_options = Options()

	chrome_options.add_argument("headless")
	chrome_options.add_argument("no-sandbox")
	chrome_options.add_argument("ignore-certificate-errors")
	chrome_options.add_argument("disable-dev-shm-usage")
	chrome_options.add_argument("disable-infobars")
	chrome_options.add_argument("disable-background-networking")
	chrome_options.add_argument("disable-default-apps")
	chrome_options.add_argument("disable-extensions")
	chrome_options.add_argument("disable-gpu")
	chrome_options.add_argument("disable-sync")
	chrome_options.add_argument("disable-translate")
	chrome_options.add_argument("hide-scrollbars")
	chrome_options.add_argument("metrics-recording-only")
	chrome_options.add_argument("no-first-run")
	chrome_options.add_argument("safebrowsing-disable-auto-update")
	chrome_options.add_argument("media-cache-size=1")
	chrome_options.add_argument("disk-cache-size=1")

	driver = webdriver.Chrome(options=chrome_options)

	try:
		driver.get("http://127.0.0.1:80")

		username_field = driver.find_element(By.ID, "username")
		password_field = driver.find_element(By.ID, "password")

		username_field.send_keys(admin_username)
		password_field.send_keys(admin_password)

		submit_button = driver.find_element(By.ID, "submitBtn")
		submit_button.click()

		driver.get(quest_url)

		time.sleep(5)

	except Exception as e:
		print(f"Error during automated login and navigation: {e}", file=sys.stderr)
		sys.exit(1)

	finally:
		driver.quit()

if __name__ == "__main__":
	main()
