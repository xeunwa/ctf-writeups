from pwn import *

p = remote('34.66.235.106', 5000)

# instruction
lines = p.recvlines(3)
for line in lines:
    print(line.decode())

for i in range(1000):
    # question
    question = p.recvline().decode().strip()
    print(question)
    # answer
    answer = str(eval(question.split(": ")[1]))
    print(f"Answer: {answer}")
    p.sendlineafter(b'Answer:', bytes(answer.encode()))
    # status 
    status = p.recvline().decode().strip()
    print(status)
    print("---"*5)

p.interactive()
