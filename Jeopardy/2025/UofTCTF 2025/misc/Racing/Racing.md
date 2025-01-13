---
date: 2025-01-13
description: symlinks to bypass blacklist
platform: UofTCTF 2025
categories: Misc
tags:
  - privesc
  - linux
duration:
---
# Racing 
Cars 1 is my favorite movie, what's yours? `ssh user@34.148.242.227 -p 2222`. The password is racing-chals.

code provided - reads a file provided but `flag` is not allowed
# Solution
used symlink in `/home/user/permitted` -> `/flag.txt` so when file read blacklist is bypassed

![](_attachments/Pasted%20image%2020250113173438.png)

