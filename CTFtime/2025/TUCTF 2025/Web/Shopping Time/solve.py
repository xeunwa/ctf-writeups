import requests
import hashlib
import itertools 
import string

url = 'https://shopping-time.tuctf.com/review?item='
payload = "Flag"

hash = hashlib.md5(payload.encode()).hexdigest()
# target hash: c5836008c1649301e29351a55db8f65c
print(hash)

chars = string.printable
n = 4
print(chars)

# generate permutations with repeataing
for l in range(1, n+1):	
	permutations = itertools.product(chars, repeat=l)
	for t in permutations:
		r = ''.join(list(t))
		hash_r = hashlib.md5(r.encode()).hexdigest()
		print(f"hash:{hash_r}\tr: {r}")
		# check if matches target hash but not Flag
		if hash_r[0:6] == hash[0:6] and r != "Flag":
			print("valid hash found, sending request")
			resp = requests.get(url+r)
			print(resp.text)
			break
