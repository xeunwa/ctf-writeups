```dataview
LIST join(file.tags, ", ")
FROM "ctf/portswigger" 
WHERE contains(platform,"PortSwiggerLabs") and !contains(file.path, "__") 
SORT file.cday desc
```
