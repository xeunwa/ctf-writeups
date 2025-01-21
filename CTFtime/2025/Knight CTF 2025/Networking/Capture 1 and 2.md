---
date: 2025-01-22
description: Web attack packet analysis
platform: Knight CTF 2025
categories: Network, Forensics, Web
tags:
  - packet-analysis
  - websocket
  - http
duration:
---
# capture1 filters
```
http
(ip.src == 192.168.1.9) && (ip.dst == 192.168.1.10)
http.request.method == "POST"
http.response.code == 302 
http contains "password"
```
- Nikto Scanning
- Account bruteforce
- RCE via php file upload
- Directory listing
- Stored XSS to cookie steal via file upload

# capture2 filters
```
websocket
!(_ws.col.protocol == "TCP")
frame contains "root"
```
- basic auth 
- websocket running rce
- `/etc/passwd` read
- 