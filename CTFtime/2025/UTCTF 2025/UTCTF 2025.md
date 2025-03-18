---
date: March 17, 2025
description: Gamey, doxxing, Bruteforcing,, binaryQR, discord
platform: UTCTF 2025
categories: Web, Misc, Cryptography, Forensics
tags:
  - brute-force
  - broken-logic
  - autokey-cipher
  - privesc
---
# Web 
## Number Champion

1. Steal players uuid in `/match` higher elo is better, matching with higher elo and steal their uuid until we get uuid with 2900+ elo. apparently we can `play with ourselves` to increase our own score.
2. Use 2900+ elo uuid to match with geopy and get the distance between them
```
{"distance":8370.564914215602,"elo":3000,"user":"geopy","uuid":"093202cc-cb1f-441c-9ccc-c0e2129c3677"}
```

> Each degree of **latitude** is approximately **111 km** apart. lat is (-90,90) lon is (-180, 180)

3. Did binary search bruteforce for lat and lon but theres a math solution provided in writeups 

| Lat      | Lon       | Output                 |
| -------- | --------- | ---------------------- |
| 39.9403  | -82.995   | 0.09040148382475867    |
|          | -82.998   | 0.06977037559040941    |
|          | -82.999   | 0.12267024381518209    |
|          | -82.997   | 0.018127808302310637   |
| 39.9402  | -82.997   | 0.022111785807836484   |
| 39.9404  |           | 0.01623282171224992    |
| 39.9405  |           | 0.017134534636618824   |
| 39.9404  | -82.9973  | 0.03214041400778609    |
|          | -82.9971  | 0.021531307395465514   |
| 39.9404  | -82.9969  | 0.010946217453454317   |
| 39.94043 |           | 0.01090183459323888    |
| 39.94042 |           | 0.010872958049317106 x |
| 39.94041 |           | 0.010887811675886382   |
| 39.94041 | -82.9968  | 0.005591735402942646 x |
|          | -82.99683 |                        |

https://maps.app.goo.gl/Up6toEEt8oQHgebC8
1. flag `utflag{1059-s-high-st-columbus-43206}`

References:
triangulating distance- https://gis.stackexchange.com/questions/66/trilateration-using-3-latitude-longitude-points-and-3-distances

## OTP 
1. First we need to find out how the "OTP"/value output for secrets is calculated.  by self registering different values of secret, registering same secret `ab` for same users outputs a 0.

- slight differences in password show closer key (otp) password of `ab` and `ba` outputs to 6
- we can observe theres a 97+ character difference if the password is of different length
- correct characters in position same will always output 0 in difference`ba` `bz` = 27 only difference of a and z are observed. 

observation

| secret | secret2 | pairing |
| ------ | ------- | ------- |
| a      | a       | 0       |
|        | aa      | 97      |
|        | aaa     | 194     |
|        | aaa     | 291     |
| a      | b       | 3       |
|        | ab      | 98      |
|        | ac      | 99      |
|        | az      | 122     |
| b      | ab      | 101     |


2. this means we can practically bruteforce the secret of the flag user by checking one character at a time, registering single character up to 32 max

constraints
- Usernames must be 1-16 characters long and secrets must be 1-32 characters long.
- Usernames and secrets can only contain alphanumeric characters, underscore, and curly braces.

Created `solve.py` to automate registering and bruteforcing, each character. Server resets every 5 minutes i think. 

![](_attachments/Pasted%20image%2020250317093521.png)


# Misc
## Trapped in plain sight 1 / 2
privesc challenge try to read `/home/trapped/flag.txt`

part 1 
some SUID bit 
```bash
find / -perm -4000 -type f 2>/dev/null xxd /home/trapped/flag.txt | xxd -r
```
part 2 
> read `/etc/passwd` and change to the secretuser user with password exposed. then read flag

## [Down the Rabbit Hole](Down%20the%20Rabbit%20Hole.md)

# Crypto 
## Autokey Cipher
I know people say you can do a frequency analysis on autokey ciphers over long text, but the flag is short so it'll be fine. `lpqwma{rws_ywpqaauad_rrqfcfkq_wuey_ifwo_xlkvxawjh_pkbgrzf}`

1. Trying out tools and gpt online comes up with initial key `RWLLMU` since we know `utflag{` is the start
2. Trying it manually i observed that if i entered key of more than 8 the rest of the output is getting destroyed 

`rwllmuhs` key length of 8 looks like its showing the plaintext `utflag{key_frequqqcy_analmpis_when_wqow_begibking_letfhrs}` key length of 9 fcks up the rest of the cipher, so the key should be 8 `rwllmuhsu` > `utflag{key_edkfaukwf_nogaclau_rhqs_idlo_dudffstyt_mqybmhm}`

3. Means that the last 2 characters are the one to bruteforce

Generated bruteforce autokey_decrypt from deepsee k

> takeaway: guessing first few characters, usually solves the rest by itself when key is short?


References:
- https://youtu.be/ywRRfc2t6w8
- https://crypto.interactive-maths.com/autokey-cipher.html
- https://www.dcode.fr/autoclave-cipher

# Forensics
## Streamified
Apparently I'm supposed to scan this or something... but I don't really get it. `1111111000011110101111111100000100110101100100000110111010110110111010111011011101010101001101011101101110101001010010101110110000010100101111010000011111111010101010101111111000000001011110100000000010111110001110110011111000111010101100000010100000100011110111100101110111100000100001010100010000011000001000000001011011111100010001010111011100011010100010101001111100110111011100001001100110000011100001100110101011111111100000000110000001000110101111111001111001101010011100000101101001010001000010111010111100011111111011011101011001110011010011101110101010011110010010110000010011011001011100011111111010101010000010111`

Solution 
tried ascii decryption and byte splitting nothing good came from dat, den thought about the clue

'scanning' from the challenge description  maybe this is qr code with 1 as black 0 as white, ask chat gpt to create script that converts this to qr. it turns out it would generate a valid QR code 

Or we can just use this site. https://bahamas10.github.io/binary-to-qrcode/

Generated QR code
![](_attachments/Pasted%20image%2020250317104349.png)

learned a bunch of tools
- https://cryptii.com/pipes/binary-decoder
- https://bahamas10.github.io/binary-to-qrcode/