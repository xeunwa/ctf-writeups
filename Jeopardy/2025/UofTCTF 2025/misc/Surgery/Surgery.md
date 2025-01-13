---
date: 2025-01-13
description: GeoOSINT with website recon
platform: UofTCTF 2025
categories: OSINT
tags:
  - geosint
duration:
---
# Surgery
I was thinking of getting some facial contouring plastic surgery done, but didn't know who to go to. My friend said they had a recommendation for a doctor specializing in that, but only sent me this photo of some building. Who's the doctor? Before submitting, wrap the doctor's name in uoftctf{}. Special characters allowed, [First] [Last] format. For example, if the name was Jean-Pierre Brehier, the flag would be uoftctf{Jean-Pierre Brehier}

chall.png
![](_attachments/chall.png)
# Solution
reverse search google images + add surgery/ clinic as text input to find `JK Plastic Surgery Clinic` in Korea

![](_attachments/Pasted%20image%2020250113172956.png)
visit website nothing found in google maps

![](_attachments/Pasted%20image%2020250113173007.png)

website [https://jkplastic.com/en/about-us/our-value/doctors.asp?scr_target=김성식](https://jkplastic.com/en/about-us/our-value/doctors.asp?scr_target=김성식 "https://jkplastic.com/en/about-us/our-value/doctors.asp?scr_target=김성식") 

Look under Facial Countouring since were lookign for Surgeon from this department. tried the doctors found