# Generate ips based from octal range
n = 10000

def octal_to_ipv4(octal):
    decimal = int(octal, 8)
    return '.'.join(str((decimal >> (8 * i)) & 255) for i in reversed(range(4)))

f = open("ipv4s.txt", "w")
for i in range(n):
    octal = f"{2571:04o}{i:04o}"
    ipv4 = octal_to_ipv4(octal)
    print(f"Generated: {octal} {ipv4}")
    f.write(f"{ipv4}\n")

f.close()
