---
date: 2024-12-09
description: OSINT in general, some web
platform: TCONCTF 7
categories: Web, OSINT, Misc
tags:
  - steganography
  - php/8-1-0-dev
  - AES
duration:
---

# HacktheNorth/TCONCTF7 Writeups 

Here are my writeups for solved challenges.

| Category | Challenge                    | Solution                                                                                                                                                               a                                                                                          |
| -------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Web      | Computer Page (50)           | https://github.com/0000rei/TCON7-CTF-Writeups/blob/main/Web/03%20-%20Computer%20Page.md                                                                                                                                                                                                                                                                       |
| Web      | Hack me Bhe (200)            | https://github.com/0000rei/TCON7-CTF-Writeups/blob/main/Web/06%20-%20Hack%20Me%20Bhe.md                                                                                                                                                                                                                                                                       |
| OSINT    | Digital Enigma (50)          | Search provided string in virustotal. Flag found here https://www.virustotal.com/gui/file/66c9bf00640ca37abb8335101f38f383e58c17c41c0475f9c459d99753bccbb0                                                                                                             |
| OSINT    | Hidden Verse (150)           | Search `tcon{` in pastebin https://pastebin.com/                                                                                                                                                                                                                       |
| OSINT    | Hidden in Plain Site (300)   | Search `laet4x` twitter account and find similar image to the challenge https://x.com/laet4x/status/1862206770690605422. Use `steghide extract -sf file` to extract data and get flag                                                                                  |
| OSINT    | The Event Code (350)         | Search `techkey2021@gmail.com` in Epieos https://epieos.com/?q=techkey2021%40gmail.com&t=email. The output will include a public calendar https://calendar.google.com/calendar/u/0/embed?src=techkey2021@gmail.com and the flag is found in event in **Jan 1, 2025**   |
| OSINT    | Im here! Im here! (400)      | Search `Don Domingo Public Market` in Google Maps. We are unable to find the flag in the reviews/photos in the place but if you look around, a similar image is found at TGP https://maps.app.goo.gl/AnPVc3E3oaCZTuJQ6                                                   |
| OSINT    | The Hidden Archive (450)     | Search `tcon` in Wikipedia and find theres a dedicated page for tcon. We can see Revision history here https://en.wikipedia.org/w/index.php?title=Tcon&action=history. Flag can be found at https://en.wikipedia.org/w/index.php?title=Tcon&diff=prev&oldid=1248813049 |
| OSINT    | Battle of Youtubers (450)    | Search `tcon{` in the youtube video/channel is enough to get the flag. But we can use online tools to extract youtube metadata e.g https://mattw.io/youtube-metadata/                                                                                                  |
| MISC     | Hexed Reality (100)          | Download image and extract data using steghide `steghide extract -sf file ` Observe the output is in hex. We can use this one liner to get the flag `cat steganopayload23203.txt \| xxd -r -p \| grep tcon{`                                                           |
| MISC     | Dante's (150)                | Searching online about the provided string leads us that its a Malbodge esteric language. String can be decoded here https://malbolge.doleczek.pl/. There is an error for 1 character(258) so I had to brute-force it                                                  |
| MISC     | Decryption - Symmetric (200) | Crypto chall, can be solved using Cyberchef AES Decrypt just pad the key with spaces. Recipe - https://tinyurl.com/2ef5f7xz                                                                                                                                            |
| MISC     | Paws of Hidden Byte (350)    | Forensics/LSB Steganography problem. I used zsteg to solve and bruteforce LSB params out of the box `zsteg -a tconXlsb.png -l 500 \| grep tcon`                                                                                                                        |
|          |                              |                                                                                                                                                                                                                                                                        |

Apologies for the short writeups, i didn't have time to collect the artifacts, message me if u want extra details on how i solve a particular challenge.
