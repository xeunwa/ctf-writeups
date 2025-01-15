def rev002(combined_str):
    result = []
    for char in combined_str:
        combined_value = ord(char)
        high_byte = combined_value >> 8  # Get the high byte
        # print(high_byte)
        low_byte = combined_value & 0xFF  # Get the low byte (masking the lower 8 bits)
        # print(combined_value)
        result.append(chr(high_byte))  # Convert high byte back to char
        result.append(chr(low_byte))   # Convert low byte back to char
    return ''.join(result)


flag = ""
for i in range(0, len(flag), 2):
    chr((ord(flag[i]) << 8) + ord(flag[i + 1])) 
    
print(rev002("杲潤湯筩湳瑥慤彯晟㡟扩瑳彭慫敟ㄶ形楴獽"))