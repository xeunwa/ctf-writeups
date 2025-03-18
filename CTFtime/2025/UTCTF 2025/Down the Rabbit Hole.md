---
date: March 17, 2025
description: getting hidden information in discord
platform: UTCTF 2025
categories: Misc
tags:
  - discord
  - steganography
---
## Challenge
Join our Discord and find the flag. https://discord.gg/RDDNTV7F62

Note: The initial scope for this challenge is just the Discord server itself, and not any persons or individuals. Unofficial content is not in scope.

By Sasha (@kyrili on discord)

## Solution
1. We can get some additional info from the discord using the API. this gets the secret info. Apparently there are multiple ways to access this, inspecting through JS, react devtools, observing the link and discord forks apps. what i did is did accessed the API
```bash
curl https://discord.com/api/v10/guilds/<guild_id>/channels
```

![](_attachments/Pasted%20image%2020250316150806.png)

2. We see document file. Planning doc: [https://docs.google.com/document/d/1cgFhoHKLEbbJlu1SX4gCfFI4CGEEoEisiFq1CW-TKUo/](https://docs.google.com/document/d/1cgFhoHKLEbbJlu1SX4gCfFI4CGEEoEisiFq1CW-TKUo/ "https://docs.google.com/document/d/1cgFhoHKLEbbJlu1SX4gCfFI4CGEEoEisiFq1CW-TKUo/") Admin password: `Bdm@9D/]J^7@9[D(` note of this)
![](_attachments/Pasted%20image%2020250317104817.png)

3. Extracting doc file to txt reveals some hidden information
    vertical text `Coq\IP1o7hr#yyW7`
    horizontal test  `WPPVY-9YgdHlRZjIWlYWnyST4lqZiILaA_tpGt3bqVU`

4. hovering link goes to private gist of chall author [https://gist.github.com/umbresp/5275f23f615c9bdcb21c463ac4b87c3c](https://gist.github.com/umbresp/5275f23f615c9bdcb21c463ac4b87c3c "https://gist.github.com/umbresp/5275f23f615c9bdcb21c463ac4b87c3c")
![](_attachments/Pasted%20image%2020250317104920.png)

5. show revisions we get `aHR0cHM6Ly9tZWdhLm56L2ZpbGUvSEhnUjFSUkw=` > [https://mega.nz/file/HHgR1RRL](https://mega.nz/file/HHgR1RRL "https://mega.nz/file/HHgR1RRL")
6. password for megafile is the admin password found form discord api `Bdm@9D/]J^7@9[D(` . this downloads a zip that contains a .git folder
7. 7. extract the previously deleted image from git
![](_attachments/Pasted%20image%2020250317105149.png)

8 . Run `steghide extract -sf image.jpg` to the white rabbit with the last string of chars found from the doc file `Coq\IP1o7hr#yyW7`

![](_attachments/Pasted%20image%2020250317105230.png)

flag `utflag{f0ll0w1ng_th3_wh1t3_r4bb1t_:3}`

