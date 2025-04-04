# Using latest image as of 2025-03-09
FROM debian:latest@sha256:35286826a88dc879b4f438b645ba574a55a14187b483d09213a024dc0c0a64ed

# Install the pre-requisites
RUN apt-get update && apt-get install -y python3 python3-pip nginx supervisor samba gnupg2 curl lsb-release socat
RUN pip3 install semgrep gunicorn --break-system-packages --no-cache-dir

# Add PostgreSQL's repository
RUN sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
RUN curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /etc/apt/trusted.gpg.d/postgresql.gpg
RUN apt-get update && apt-get install -y postgresql-17 postgresql-contrib-17

# Create the application directory
RUN mkdir -p /www/application/config

# Copy the application files
COPY --chown=www-data:www-data --chmod=777 challenge/application /www/application

# Copy the configuration files into our new config directory
COPY --chmod=646 config/nginx.conf /www/application/config/nginx.conf
COPY config/supervisord.conf /www/application/config/supervisord.conf
COPY config/postgresql.conf /www/application/config/postgresql.conf


# Install Python deps
RUN pip3 install -r /www/application/requirements.txt --no-cache-dir --break-system-packages

# Setting up the configuration files
RUN rm -f /etc/nginx/nginx.conf \
    && rm -f /etc/supervisord.conf \
    && rm -f /etc/postgresql/17/main/postgresql.conf

RUN ln -s /www/application/config/nginx.conf /etc/nginx/nginx.conf \
    && ln -s /www/application/config/supervisord.conf /etc/supervisord.conf \
    && ln -s /www/application/config/postgresql.conf /etc/postgresql/17/main/postgresql.conf


# Copy the challenge files
COPY --chown=root:root --chmod=600 challenge/flag.txt /flag.txt
COPY --chown=root:root --chmod=700 challenge/restart_nginx.sh /tmp/restart_nginx.sh
#COPY --chown=root:root --chmod=700 challenge/validate.py /tmp/validate.py
#COPY --chown=root:root --chmod=400 challenge/semgrep_rules.yaml /tmp/semgrep_rules.yaml

# Set up PostgreSQL cluster explicitly
COPY challenge/application/schema/db.sql /tmp/db.sql
RUN pg_dropcluster --stop 17 main || true \
    && rm -rf /var/lib/postgresql/17/main \
    && rm -rf /etc/postgresql/17/main \
    && pg_createcluster 17 main

RUN su postgres -c "pg_ctlcluster 17 main start" \
    && su postgres -c "psql -c \"CREATE USER garrick WITH PASSWORD 'st0nes_4nd_5t!ck5';\"" \
    && su postgres -c "psql -c \"CREATE DATABASE forgemaster_db OWNER garrick;\"" \
    && su postgres -c "psql -d forgemaster_db -f /tmp/db.sql"

# Configure Samba (append lines to default smb.conf)
RUN echo "[app]" >> /etc/samba/smb.conf \
    && echo "path = /www/application" >> /etc/samba/smb.conf \
    && echo "browsable = yes" >> /etc/samba/smb.conf \
    && echo "writable = yes" >> /etc/samba/smb.conf \
    && echo "guest ok = yes" >> /etc/samba/smb.conf \
    && echo "read only = no" >> /etc/samba/smb.conf

# Set the working directory
WORKDIR /www/application

# Environment variables
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Expose the ports
EXPOSE 3000 445

# Start the services
CMD service smbd start \
    && su postgres -c "pg_ctlcluster 17 main start" \
    && /usr/bin/supervisord -c /etc/supervisord.conf

