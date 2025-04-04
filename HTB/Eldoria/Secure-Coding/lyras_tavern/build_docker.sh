#!/bin/bash
docker build -t lyras_tavern .
docker run  --name=lyras_tavern --rm -p 8081:3000 -p 444:445 -it lyras_tavern