---
date: 2025-01-05
description: Bypassing chrome policy denylist
platform: irisCTF2025
categories: Web
tags:
  - url-bypass
  - code-review
  - python
duration:
---

# Political

## Main application
- /token - returns valid tokens
- /redeem endpoint to get flag but token must be visited bythe admin
if token not in valid
- /giveflag endpoint requires an admin to visit the site

```python
 14 @app.route("/giveflag")
 15 def hello_world():
 16     if "token" not in request.args or "admin" not in request.cookies:
 17         return "Who are you?"
 18 
 19     token = request.args["token"]
 20     admin = request.cookies["admin"]
 21     if token not in valid_tokens or admin != ADMIN:
 22         return "Why are you?"
 23 
 24     valid_tokens[token] = True
 25     return "GG"
```
admin request should looklike `/giveflag?token={valid_token}`

# bot 
visiting the `/redeem` endpoint after admin visit does not set the valid_tokens to true and flag not returned

policy.json
```json
  1 {
  2     "URLBlocklist": ["*/giveflag", "*?token=*"]
  3 }
```
[https://chromeenterprise.google/policies/?policy=URLBlocklist](https://chromeenterprise.google/policies/?policy=URLBlocklist "https://chromeenterprise.google/policies/?policy=URLBlocklist")


- tried to bypass using `/giveflag?a&token={token}` but chrome policy is smarter than that. Apparently adding arguments does not invalidate the `*/giveflag` blocklist and putting the `token`as second argument does not work as well
- tried using URL encoding 

Gave up, other solutions show encoding i.e. `/%2fgiveflag?%74oken` > `//giveflag?token.  try to encode characters next time 