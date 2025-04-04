#!/bin/bash
docker rm -f web_eldoria_realms
docker build --tag=web_eldoria_realms .
docker run -p 1337:1337 -p 50051:50051 -it --rm --name=web_eldoria_realms web_eldoria_realms