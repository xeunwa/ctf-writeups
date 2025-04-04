#!/bin/bash
docker rm -f demo_rb
docker build --tag=demo_rb .
docker run -p 4567:4567 -it --rm --name=demo_rb demo_rb