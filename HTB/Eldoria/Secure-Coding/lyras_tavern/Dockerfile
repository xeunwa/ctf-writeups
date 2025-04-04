# Using latest debian image as of 2025-03-05
FROM debian:latest@sha256:35286826a88dc879b4f438b645ba574a55a14187b483d09213a024dc0c0a64ed

# Update repositories and install prerequisites
RUN apt-get update && apt-get install -y gnupg apt-transport-https ca-certificates lsb-release wget samba supervisor socat python3 python3-pip php-fpm php-sqlite3 php-curl

# Add the Sury repository for modern PHP packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add -

# Update and install Apache, PHP 8.2 CGI, and the fcgid module and pip
RUN apt-get update && apt-get install -y apache2 libapache2-mod-fcgid php8.2-cgi vim libapache2-mod-php8.2 && rm -rf /var/lib/apt/lists/*
#RUN pip3 install --break-system-packages --no-cache-dir semgrep

# Copy config files
COPY config/supervisord.conf /etc/supervisord.conf
COPY config/apache2.conf /etc/apache2/apache2.conf
COPY config/cgi-bin.conf /etc/apache2/conf-available/cgi-bin.conf


# Copying the application and its contents
COPY --chown=www-data:www-data --chmod=777 challenge/application /www/application
#COPY --chown=root:root --chmod=700 challenge/validate.py /tmp/validate.py
#COPY --chown=root:root --chmod=400 challenge/semgrep_rules.yaml /tmp/semgrep_rules.yaml
COPY --chown=root:root --chmod=400 challenge/flag.txt /flag.txt

RUN mkdir /www/application/instance/
RUN chown www-data:www-data /www/application/instance/
RUN chmod 777 /www/application/instance/

# Restrict some files (js, css files) to be read only
RUN chmod -R 555 /www/application/static
RUN chmod 500 /www/application/config.php

# Set up a custom Apache CGI configuration for /cgi-bin/
RUN mkdir -p /usr/lib/cgi-bin /etc/apache2/logs && chown www-data:www-data /usr/lib/cgi-bin
RUN ln -s /usr/lib/cgi-bin/app.cgi /www/application/app.cgi
COPY --chown=www-data:www-data --chmod=777 config/app.cgi /usr/lib/cgi-bin/app.cgi
RUN chmod +x /usr/lib/cgi-bin/app.cgi

# Enable CGI and fcgid, disable default CGI conf, and adjust MPM
RUN a2enmod cgi &&\
    a2enmod fcgid && \
    a2disconf serve-cgi-bin && \
    a2enmod php8.2 && \
    a2enconf cgi-bin

# Increase CGI timeout to prevent premature script termination
RUN sed -i 's/FcgidIOTimeout.*/FcgidIOTimeout 120/g' /etc/apache2/mods-available/fcgid.conf

# Forget what this is for, but I know it's important
RUN usermod -s /bin/false www-data
RUN mkdir -p /tmp/php_config && chown root:root /tmp/php_config && chmod 555 /tmp/php_config
COPY --chown=root:root --chmod=555 config/proper_config.ini /tmp/php_config/proper_config.ini

# Configure Samba
RUN echo "[app]" >> /etc/samba/smb.conf && \
    echo "path = /www/application" >> /etc/samba/smb.conf && \
    echo "browsable = yes" >> /etc/samba/smb.conf && \
    echo "writable = yes" >> /etc/samba/smb.conf && \
    echo "guest ok = yes" >> /etc/samba/smb.conf && \
    echo "read only = no" >> /etc/samba/smb.conf

WORKDIR /www/application

# Expose port 3000 and start samba and supervisord in the foreground
EXPOSE 3000 445
CMD service smbd start && /usr/bin/supervisord -c /etc/supervisord.conf
