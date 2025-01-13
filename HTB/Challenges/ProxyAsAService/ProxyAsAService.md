---
date: 2025-01-08
description: SSRF blacklist filter bypass
platform: HackTheBox
categories: Web
tags:
  - SSRF
  - url-bypass
  - code-review
  - python
duration: 10 minutes
---
# Application
>Experience the freedom of the web with ProxyAsAService. Because online privacy and access should be for everyone, everywhere.
author: makelaris

the application is built using flask and there are two main endpoints 
- `/proxy` - proxies the request to a website
- `/debug/environment` - shows environment variables 
additionally flag is set in a `FLAG` environment variable 
## proxy 
`/?url=` - gets redirected to a reddit url by default 
```python
SITE_NAME = 'reddit.com'
# ... 
@proxy_api.route('/', methods=['GET', 'POST'])
def proxy():
    url = request.args.get('url')

    if not url:
        cat_meme_subreddits = [
            '/r/cats/',
            '/r/catpictures',
            '/r/catvideos/'
        ]

        random_subreddit = random.choice(cat_meme_subreddits)
		# redirection to the same route choosing a random subreddit
        return redirect(url_for('.proxy', url=random_subreddit))
	# visit the target 
    target_url = f'http://{SITE_NAME}{url}'
    # essentially navigating to http://reddit.com{url}
    response, headers = proxy_req(target_url)

    return Response(response.content, response.status_code, headers.items())
```

## environment
gets an environment variable / target function
`/debug/environment`
```python
@debug.route('/environment', methods=['GET'])
@is_from_localhost
def debug_environment():
    environment_info = {
        'Environment variables': dict(os.environ),
        'Request headers': dict(request.headers)
    }

    return jsonify(environment_info)
```

even though this environment available via localhost. It is protected using blacklist 
```python
RESTRICTED_URLS = ['localhost', '127.', '192.168.', '10.', '172.']

def is_safe_url(url):
    for restricted_url in RESTRICTED_URLS:
        if restricted_url in url:
            return False
    return True
```
# Vulnerability 
As the application visits the URL we passed it and returns it in the response, can we use this to access other application paths, performing SSRF? 
- It visit`reddit.com` by default but a `@` character can be inputted from the url input to change the domain  allowing attacker to visit`localhost/debug/environment` instead
- SSRF protection was not enough and can be bypass using a number of payloads https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/Server%20Side%20Request%20Forgery/README.md#bypass-using-an-encoded-ip-address
# Exploit 
- For the `reddit.com` hardcoded hostname can be bypassed by passing in the url parameter prefixing with `@` character `?url=@xnw.xyz` it becomes `http://reddit.com@xnw.xyz`
- Bypass the blacklist using encoded ip address to bypass blacklist `?url=@0.0.0.0:1337`
- Access the `/debug/environment` path  http://94.237.54.42:47180/?url=@0.0.0.0:1337/debug/environment
![](_attachments/Pasted%20image%2020250108111029.png)
> http://94.237.54.42:47180/?url=@0.1337/debug/environment might be the shortest payload