# Using latest debian image as of 2024-12-25
FROM debian:latest

# Install the packages required for the application
RUN apt-get update && apt-get install -y \
    supervisor samba python3 python3-pip nodejs npm socat \
    && rm -rf /var/lib/apt/lists/*

# Copy config files
COPY config/supervisord.conf /etc/supervisord.conf

# Copy the application and its contents to the container
COPY challenge/app /www/application
COPY --chown=root:root challenge/flag.txt /flag.txt
WORKDIR /www/application

# Environment variables
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

RUN npm i
RUN npm i -g nodemon
RUN npx prisma migrate dev --name init

# Set up the permissions and the working directory
RUN chmod 400 /flag.txt
RUN chmod -R 777 /www/application

# RUN chmod 400 /tmp/validate.py && chmod 400 /tmp/semgrep_rules.yaml
RUN chown -R www-data:www-data /www/application
RUN chmod -R a=rX /www/application/public /www/application/views && chown -R root:root /www/application/public /www/application/views

# Configure Samba
RUN echo "[app]" >> /etc/samba/smb.conf && \
    echo "path = /www/application" >> /etc/samba/smb.conf && \
    echo "browsable = yes" >> /etc/samba/smb.conf && \
    echo "writable = yes" >> /etc/samba/smb.conf && \
    echo "guest ok = yes" >> /etc/samba/smb.conf && \
    echo "read only = no" >> /etc/samba/smb.conf


# Expose the ports
EXPOSE 3000 445 1337

# Start socat (as an additional service) and supervisord
CMD service smbd start && /usr/bin/supervisord -c /etc/supervisord.conf
