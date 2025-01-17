from pwn import *

p = remote('ctf.mf.grsu.by', 9037)

# This exploit used a vulnerability in the SMB (Server Message Block) protocol of Windows. It was used to organize the largest ransomware attacks.
# And now - a few questions. Keep the writing of the answers as on Wikipedia (https://en.wikipedia.org/). Try to be brief. If there are several words in the answer - separate them with a space.

#     Exploit name
#     Exploit ID in the CVE database
#     Technique ID (according to MITRE ATT&CK classification)
#     Tactic (according to MITRE ATT&CK classification)
#     Used in 2017 for rapid spread in an attack
#     What year did the exploit leak
#     Cyber ​​group involved in the leak
#     Alleged developer of the exploit
#     Microsoft patch ID for the exploit

answers = [
    "Eternal Blue",
    "CVE-2017-0144",
    "T1210",
    "Lateral Movement",
    "WannaCry",
    "2017",
    "Shadow Brokers",
    "NSA",
    "MS17-010",
]

for ans in answers:
    print(p.recvuntil(b">").decode())
    print("Answer:", ans)
    p.sendline(bytes(ans.encode()))
    print(p.recvline().decode())

p.interactive()

# grodno{bb92f01a1d3d373b95f08538bfece4}

