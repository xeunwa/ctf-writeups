with open("chall.txt", "rb") as f:
    enc = f.read().decode("iso8859-1")

print(enc)

# for c in flag:
#     # print(bin(ord(c))) #0b1101100
#     o = bin(ord(c))[2:] # unpadded 
#     o = bin(ord(c))[2:].zfill(8) # fills 8 binary
#     # print(o)

# replace first element with 0 
flag = ""
for c in enc:
    o = bin(ord(c))[2:].zfill(8)
    o = "0"+o[1:]
    flag += chr(int(o,2))

print(flag)

     
