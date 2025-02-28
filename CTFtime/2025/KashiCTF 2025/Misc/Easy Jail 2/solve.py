
while True:
    arg = input(">>")
    g = f"global f; f = lambda: __import__('os').system('{arg}')"

    hexx = g.encode().hex()
    execc = f"exec(bytes.fromhex('{hexx}').decode())"
    evall = f'eval("{exec}")'

    print(hexx)
    print(execc)
    print(evall) 
    eval(execc)
    f()
