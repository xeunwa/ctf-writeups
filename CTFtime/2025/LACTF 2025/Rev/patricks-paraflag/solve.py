enc = "l_alcotsft{_tihne__ifnlfaign_igtoyt}"
flag = ['x'] * 36

for i in range(len(enc)//2):
    flag[i] = enc[i * 2]
    flag[i+(len(enc)//2)] = enc[i * 2 + 1]

print(''.join(flag))  
     
