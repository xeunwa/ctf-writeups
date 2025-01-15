from pwn import *

p = remote('ctf.mf.grsu.by', 9035)

# https://attack.mitre.org/groups/G0032/
# 1. The name of this cyber group: Lazarus Group
# 2. Since what year has the group been active
# 3. The name of the group's most destructive attack in 2014
# 4. The company attacked
# 5. The name of the worm used by the group in 2017
# 6. The exploit used to escalate privileges and launch the worm (two words separated by a space)
# 7. Who this exploit was allegedly stolen from

answers = [
    "Lazarus Group",
    "2009",
    "Wiper",
    "Sony Pictures Entertainment",
    "WannaCry",
    "Eternal Blue",
    "NSA"
]

for ans in answers:
    print(p.recvuntil(b">").decode())
    print("Answer:", ans)
    p.sendline(bytes(ans.encode()))
    print(p.recvline().decode())

p.interactive()

# grodno{6bdca092cbb0546cb07395536c4e61dc7af1cca}