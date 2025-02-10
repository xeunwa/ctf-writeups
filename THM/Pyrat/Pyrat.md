---
date: 2025-02-02
description: Python RCE with scripting tcp connection
platform: TryHackMe
categories: Network, PrivEsc
tags:
  - brute-force/authentication
  - scripting
  - python
  - code-injection/python
  - file-enumeration
  - TCP 
duration:
---
# recon
port 8000 q
python 3.11.2

# Attack Chain 
```bash
# 1. connecting to the via nc port allows python code execution 
#rce from there
print(1) > rce payload

# 2. read linux files find important stuff audit
find /opt -iname "*conf*"
cat /opt/dev/.git/config

git status
# 3. git restore python file to get username hint? 
# 4. bruteforce tcp port for root


