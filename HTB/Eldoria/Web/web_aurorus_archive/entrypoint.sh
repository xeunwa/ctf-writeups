#!/bin/bash
set -e

echo "[+] Initializing container entrypoint..."

# Generate random passwords and secrets
APPUSER_PASSWORD=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -hex 32)
OAUTH_CLIENT_ID=$(openssl rand -base64 32 | tr -d '/+=')
OAUTH_CLIENT_SECRET=$(openssl rand -base64 32 | tr -d '/+=')
ADMIN_PASSWORD=$(openssl rand -base64 32)
JWT_SECRET_OAUTH=$(openssl rand -base64 32)

# Save environment variables to .env file
cat <<EOF > /app/.env
JWT_SECRET=$JWT_SECRET
NODE_ENV=development
OAUTH_CLIENT_ID=$OAUTH_CLIENT_ID
OAUTH_CLIENT_SECRET=$OAUTH_CLIENT_SECRET
OAUTH_REDIRECT_URI=/callback
OAUTH_TOKEN_URL=http://127.0.0.1:8080/oauth/token
OAUTH_USERINFO_URL=http://127.0.0.1:8080/oauth/user-info/json
ADMIN_PASSWORD=$ADMIN_PASSWORD
DB_USER=appuser
DB_PASSWORD=$APPUSER_PASSWORD
DB_NAME=appdb
DB_HOST=localhost
DB_PORT=5432
EOF

cat <<EOF > /oauthServer/.env
CLIENT_ID=$OAUTH_CLIENT_ID
CLIENT_SECRET=$OAUTH_CLIENT_SECRET
SESSION_SECRET=$JWT_SECRET_OAUTH
ADMIN_PASSWORD=$ADMIN_PASSWORD
EOF

echo "[+] Environment variables saved to /app/.env"

# Ensure PostgreSQL directories exist with correct permissions
echo "[+] Ensuring PostgreSQL directories exist..."
mkdir -p /var/lib/postgresql/data /run/postgresql
chown -R postgres:postgres /var/lib/postgresql /run/postgresql
chmod 775 /run/postgresql

# Initialize PostgreSQL only if not already initialized
if [ ! -f "/var/lib/postgresql/data/PG_VERSION" ]; then
    echo "[+] Initializing PostgreSQL database..."
    su - postgres -c "/usr/bin/initdb -D /var/lib/postgresql/data"
fi

# Ensure PostgreSQL config allows connections
echo "listen_addresses = '*'" >> /var/lib/postgresql/data/postgresql.conf
echo "host all all 0.0.0.0/0 md5" >> /var/lib/postgresql/data/pg_hba.conf

# Start PostgreSQL in background
echo "[+] Starting PostgreSQL..."
su - postgres -c "/usr/bin/pg_ctl -D /var/lib/postgresql/data -l /var/lib/postgresql/data/logfile start"

# Wait for PostgreSQL to be ready
until su - postgres -c "pg_isready -q"; do
    sleep 1
    echo "[+] Waiting for PostgreSQL..."
done

echo "[+] PostgreSQL is ready!"

# Set up database and create a new user (appuser) with complete access to appdb and the selected LO functions
echo "[+] Setting up database and user..."
su - postgres -c "psql -v ON_ERROR_STOP=1 <<EOF
DROP USER IF EXISTS appuser;
CREATE USER appuser WITH PASSWORD '$APPUSER_PASSWORD' SUPERUSER;
DROP DATABASE IF EXISTS appdb;
CREATE DATABASE appdb OWNER appuser;
\c appdb
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO appuser;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO appuser;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO appuser;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON SEQUENCES TO appuser;
EOF"

echo "[+] Database and user created successfully."

# Stop PostgreSQL after setup
echo "[+] Stopping temporary PostgreSQL instance..."
su - postgres -c "/usr/bin/pg_ctl -D /var/lib/postgresql/data stop"

# Start services via Supervisord
echo "[+] Starting services with Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor.d/supervisord.ini
