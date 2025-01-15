from secret import flag, base
 
def to_nega_notation(number, base):
    # make the number negative 
    base = base if base < 0 else -base
    
    if number == 0:
        return "0"

    digits = []

    # % get a remainder 
    while number != 0:
        remainder = number % base
        number //= base
        
        if remainder < 0:
            remainder += abs(base)
            number += 1
        
        digits.append(str(remainder) if remainder < 10 else chr(55 + remainder))
    
    return ''.join(reversed(digits))

number = - int.from_bytes(flag.encode(), "big")
result = to_nega_notation(number, base)
print (f"result: {result}")

=================================
result = "1LK1O45FNCOA1489GCN6HNDP1HP5QDEI18ONPLK1PLC8GOJKP2GPNEMMO6M7F2JH3GM6QDF9EPN57JMPAD20PDNDGGN2"