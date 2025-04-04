# Base image: Alpine with Node.js
FROM node:alpine

# Set environment variables
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    PGDATA=/var/lib/postgresql/data

# Install dependencies: Chromium, PostgreSQL, Supervisord
RUN apk add --no-cache \
    chromium \
    chromium-chromedriver \
    postgresql \
    postgresql-contrib \
    supervisor \
    nginx \
    openssl \
    build-base

# Create required directories
RUN mkdir -p /var/lib/postgresql/data /var/log/supervisor

# Change ownership of PostgreSQL data directory
RUN chown -R postgres:postgres /var/lib/postgresql

# Initialize PostgreSQL as the 'postgres' user
USER postgres
RUN initdb -D /var/lib/postgresql/data

# Switch back to root for further setup
USER root

# Supervisord configuration
RUN mkdir -p /etc/supervisor.d
COPY config/supervisord.conf /etc/supervisor.d/supervisord.ini

# Set up working directory & install dependencies
COPY challenge /app
COPY oauthServer /oauthServer
COPY config/nginx.conf /etc/nginx/nginx.conf

WORKDIR /app
RUN npm install

WORKDIR /oauthServer
RUN npm install

# Copy entrypoint script and set correct permissions
COPY entrypoint.sh /
RUN chmod 700 /entrypoint.sh && chown root:root /entrypoint.sh

# Expose ports
EXPOSE 1337

# Add readflag binary
COPY config/readflag.c /
RUN gcc -o /readflag /readflag.c -Wimplicit-function-declaration && chmod 4755 /readflag && rm /readflag.c

# Copy flag
COPY flag.txt /root/flag

# Start everything with Supervisord
ENTRYPOINT ["/bin/ash", "/entrypoint.sh"]


