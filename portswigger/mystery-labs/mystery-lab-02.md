---
platform: PortSwiggerLabs
categories: Web
---
#jwt-attacks 

# Functionalities
- Search bar 
- Login Page 

## Login page 
`wiener:peter` login work
## /admin
- unauthorized 
- `kid header` jwt. 

# Vulnerability
Vulnerable to JWT kid header path traversal [[202411120243 - JWT Attacks#JWT authentication bypass via kid header path traversal]]

# Attack 
0. Generate empty sign key 
1. Change the `kid` header value pointing to `../../../../../../dev/null`
2. Change the `sub` value to `administrator`
3. Sign and select symmetric key generated + Don't Modify header