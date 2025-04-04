









# Add readflag binary
COPY config/readflag.c /
RUN gcc -o /readflag /readflag.c -Wimplicit-function-declaration && chmod 4755 /readflag && rm /readflag.c

# Copy flag
COPY flag.txt /root/flag
