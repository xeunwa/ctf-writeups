from Crypto.Util.number import isPrime, getPrime

while True:
    # Find two primes where gcd(65537, (p-1)*(q-1)) != 1
    p = getPrime(64)
    q = getPrime(64)
    
    phi = (p - 1) * (q - 1)
    
    if phi % 65537 == 0:  # This ensures e has no modular inverse
        print(f"Using p={p}, q={q} to break RSAaaS!")
        break
print(p, q)
