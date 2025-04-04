docker rm -f web_eldoria_panel
docker build --platform linux/amd64 -t web_eldoria_panel .
docker run --platform linux/amd64 --name=web_eldoria_panel --rm -p1337:80 -it web_eldoria_panel
