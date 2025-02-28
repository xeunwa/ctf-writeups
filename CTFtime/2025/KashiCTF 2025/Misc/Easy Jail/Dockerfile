FROM python:3.12-slim

RUN apt-get update && apt-get install curl socat -y

WORKDIR /challenge

COPY entrypoint.sh /challenge/entrypoint.sh
COPY chall.py /challenge/chall.py
COPY flag.txt /challenge/flag.txt

RUN chmod +x /challenge/entrypoint.sh
RUN chmod +x /challenge/chall.py

EXPOSE 50002

ENTRYPOINT [ "/challenge/entrypoint.sh" ]
CMD ["socat", "-T60", "TCP-LISTEN:50002,reuseaddr,fork", "EXEC:/challenge/chall.py"]