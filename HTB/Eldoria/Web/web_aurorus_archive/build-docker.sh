docker rm -f web_aurorus_archive
docker build -t web_aurorus_archive .
docker run --name=web_aurorus_archive --rm -p1337:1337 -it web_aurorus_archive
