# Red Flags Frontend

## Installation

**Step 1**: Clone the repository
```
git clone https://github.com/SecureEU/red_flags_frontend
cd red_flags_frontend
```

**Step 2**: Configure the environment
Need to know the values of environment variables from the Red Flags backend, namely: API_URL, API_PORT
```
cat > .env << 'EOF'
APP_NAME=RedFlags
APP_ENV=local
APP_KEY=base64:xmp/LZdGzfReQJW7ggbK6hlgQ6iTdzzO+jUidBzCWps=
APP_DEBUG=true
APP_URL=http://<server_ip>                         # ⚠ Set to your server's IP
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
MEMCACHED_HOST=127.0.0.1
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
API_BASE_URL=http://db-api:8000                    #  http://<API_URL>:<API_PORT>
VITE_APP_NAME="${APP_NAME}"
PORT=7274
EOF
```

**Step 3**: Build and start
```
docker compose up -d --build
```

## Troubleshooting
Verify the frontend is running
```
docker ps | gre[ redflagui
```
Check UI at http://<server_ip>:7274
