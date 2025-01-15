---
date: 2025-01-15
description: JWT signature not verified
platform: New Year CTF 2025
categories: Web
tags:
  - jwt-attacks
duration:
---
In this task, you have the chance to become an admin without going through all that boring verification and sneak into the system like a true hacker. Find a way to bypass the protection and gain access to the admin information. Good luck on your journey to the peaks of cybersecurity! https://ctf-spcs.mf.grsu.by/task/web_wtf cred: guest:password123

# Solution
jwt signature not verified, allow forging tokens directly through body
1. changing the role user>admin does not verify signature

![](_attachments/Pasted%20image%2020250115190844.png)
