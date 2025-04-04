#!/bin/sh

# Change flag name
mv /flag.txt /flag$(cat /dev/urandom | tr -cd "a-f0-9" | head -c 10).txt

# Secure entrypoint
chmod 600 /entrypoint.sh

# Launch supervisord
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf