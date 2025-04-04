docker build -t web_arcane_auctions .
docker run  --name=cursed_scrolls --rm -p 5000:3000 -p 445:445 -p 1337:1337 -it web_arcane_auctions
