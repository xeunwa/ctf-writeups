from pwn import *

p = remote('ctf.mf.grsu.by', 9034)

# The fact of using this "malware" is considered the beginning of the era of modern cyber wars. It all started in the Middle East in the field of nuclear technology.
# Did you know about this? Try to answer a few questions.
# Each answer is one word in English. Keep the answer spelling as on Wikipedia (https://en.wikipedia.org/)

#     The type of this "malware"
#     The name of this computer worm
#     In what year was it first used
#     Which country was attacked
#     Which country is believed to have carried out the attack
#     Which company's industrial installations were most affected
#     The name and surname of the programmer who discovered the worm code (two words separated by a space)
#     Which company did he work for
#     Which country does this company work in

answers = [
    "Worm",
    "Stuxnet",
    "2010",
    "Iran",
    "Israel",
    "Siemens",
    "Sergey Ulasen",
    "VirusBlokAda",
    "Belarus"
]


for ans in answers:
    print(p.recvuntil(b">").decode())
    print("Answer:", ans)
    p.sendline(bytes(ans.encode()))
    print(p.recvline().decode())

p.interactive()

#  grodno{ebfff041087ac2ef1d420af2604fbce5}