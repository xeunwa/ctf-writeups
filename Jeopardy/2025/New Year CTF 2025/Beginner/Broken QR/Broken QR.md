---
date: 2025-01-15
description: QR Code recovery
platform: New Year CTF 2025
categories: Forensics
tags:
  - image-analysis/qr
duration:
---
Broken QR was provided, fix watermark and there's missing pixels in QR
# Online tools
https://merri.cx/qrazybox/
	- Error Correction level H
	- Mask Pattern 4
	- 41x41 qr code 
# Script
1. Pixels extraction threshhold
2. Use`pyzbar decode`

![](_attachments/Pasted%20image%2020250114014701.png)

`grodno{It's_h4rd_t0_l1ve_without_R33d-S0l0m0n_c0d3s!}`
