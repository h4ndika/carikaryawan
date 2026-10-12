#!/usr/bin/env bash
# ==============================================================================
# CARIKARYAWAN.COM - Production VPS Setup Script (Ubuntu 24.04 LTS)
# Stack: Nginx + PHP 8.3 FPM + MariaDB/MySQL + Redis + Node.js 20 + Supervisor + Composer
# ==============================================================================

set -e

# Color helpers
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}   🚀 Starting CARIKARYAWAN.COM Production VPS Setup    ${NC}"
echo -e "${BLUE}======================================================${NC}"

# 1. Check Root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}❌ Please run this script as root or with sudo.${NC}"
  exit 1
fi

# 2. Require DB_PASS from the environment BEFORE any install/action (never hardcode it).
#    Usage: sudo DB_PASS='<strong-password>' bash deploy/setup-vps.sh
if [ -z "${DB_PASS:-}" ]; then
  echo -e "${RED}❌ DB_PASS belum di-set. Jalankan: sudo DB_PASS='<password-kuat>' bash $0${NC}"
  exit 1
fi
# Password disisipkan ke SQL dan sed; hanya izinkan karakter aman.
if ! [[ "$DB_PASS" =~ ^[A-Za-z0-9@%+=:,._!-]{12,}$ ]]; then
  echo -e "${RED}❌ DB_PASS harus >= 12 karakter dari set A-Z a-z 0-9 @ % + = : , . _ ! -${NC}"
  exit 1
fi

DOMAIN="carikaryawan.com"
APP_DIR="/var/www/carikaryawan"
DB_NAME="carikaryawan"
DB_USER="carikaryawan_user"

echo -e "\n${YELLOW}▶ Step 1: Updating system packages...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt update -y && apt upgrade -y
apt install -y software-properties-common curl wget git unzip zip ufw htop supervisor certbot python3-certbot-nginx

echo -e "\n${YELLOW}▶ Step 2: Installing Nginx Web Server...${NC}"
apt install -y nginx
systemctl enable nginx
systemctl start nginx

echo -e "\n${YELLOW}▶ Step 3: Installing PHP 8.3 & Required Extensions...${NC}"
add-apt-repository -y ppa:ondrej/php
apt update -y
apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip \
  php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-sqlite3 \
  php8.3-redis php8.3-intl php8.3-soap php8.3-readline

# Configure PHP settings
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 64M/" /etc/php/8.3/fpm/php.ini
sed -i "s/post_max_size = .*/post_max_size = 64M/" /etc/php/8.3/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.3/fpm/php.ini
sed -i "s/max_execution_time = .*/max_execution_time = 180/" /etc/php/8.3/fpm/php.ini

systemctl restart php8.3-fpm
systemctl enable php8.3-fpm

echo -e "\n${YELLOW}▶ Step 4: Installing Composer...${NC}"
if ! command -v composer &> /dev/null; then
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm /tmp/composer-setup.php
fi
composer --version

echo -e "\n${YELLOW}▶ Step 5: Installing Node.js 20 LTS & NPM...${NC}"
if ! command -v node &> /dev/null; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt install -y nodejs
fi
node -v
npm -v

echo -e "\n${YELLOW}▶ Step 6: Installing Redis Server...${NC}"
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

echo -e "\n${YELLOW}▶ Step 7: Installing MariaDB / MySQL Server...${NC}"
apt install -y mariadb-server
systemctl enable mariadb
systemctl start mariadb

# Create Database and User
mariadb -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mariadb -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"
echo -e "${GREEN}✓ Database '${DB_NAME}' created with user '${DB_USER}'${NC}"

echo -e "\n${YELLOW}▶ Step 8: Configuring Nginx VirtualHost...${NC}"
cat > /etc/nginx/sites-available/${DOMAIN} << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name carikaryawan.com www.carikaryawan.com;
    root /var/www/carikaryawan/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    index index.php index.html;

    charset utf-8;

    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo -e "\n${YELLOW}▶ Step 9: Cloning / Preparing Project Directory...${NC}"
mkdir -p /var/www
mkdir -p /root/.ssh
[ -f /root/.ssh/id_ed25519 ] || (cp -f /home/*/.ssh/id_ed25519* /root/.ssh/ 2>/dev/null || true)
chmod 700 /root/.ssh 2>/dev/null || true
chmod 600 /root/.ssh/id_ed25519 2>/dev/null || true
ssh-keyscan -t ed25519,rsa github.com >> /root/.ssh/known_hosts 2>/dev/null || true
if [ ! -d "${APP_DIR}" ]; then
  git clone git@github.com:h4ndika/carikaryawan.git ${APP_DIR}
else
  cd ${APP_DIR} && git pull origin main
fi

cd ${APP_DIR}
cp -n .env.example .env || true

# Set production env settings
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_URL=.*|APP_URL=https://carikaryawan.com|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|" .env
sed -i "s|CACHE_STORE=.*|CACHE_STORE=redis|" .env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=redis|" .env

echo -e "\n${YELLOW}▶ Step 10: Installing Composer Dependencies...${NC}"
composer install --no-dev --optimize-autoloader

echo -e "\n${YELLOW}▶ Step 11: Generating App Key & Caching Config...${NC}"
php artisan key:generate --force
php artisan storage:link || true

echo -e "\n${YELLOW}▶ Step 12: Installing Node Dependencies & Building Assets...${NC}"
npm ci || npm install
npm run build

echo -e "\n${YELLOW}▶ Step 13: Running Migrations...${NC}"
php artisan migrate --force

echo -e "\n${YELLOW}▶ Step 14: Optimizing Laravel (Cache routes, views, config)...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache || true

echo -e "\n${YELLOW}▶ Step 15: Configuring Supervisor for Queue Worker...${NC}"
cat > /etc/supervisor/conf.d/carikaryawan-worker.conf << 'EOF'
[program:carikaryawan-worker-fast]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/carikaryawan/artisan queue:work --queue=ai,default --sleep=2 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/carikaryawan/storage/logs/worker-fast.log
stopwaitsecs=3600

[program:carikaryawan-worker-jobs]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/carikaryawan/artisan queue:work --queue=jobs --sleep=3 --tries=1 --timeout=900 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/carikaryawan/storage/logs/worker-jobs.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update
supervisorctl start all

echo -e "\n${YELLOW}▶ Step 16: Setting Up Laravel Cron Schedule...${NC}"
(crontab -l 2>/dev/null | grep -v 'artisan schedule:run'; echo "* * * * * cd /var/www/carikaryawan && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo -e "\n${YELLOW}▶ Step 17: Fixing Permissions...${NC}"
chown -R www-data:www-data ${APP_DIR}
chmod -R 775 ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache

echo -e "\n${YELLOW}▶ Step 18: Configuring Firewall (UFW)...${NC}"
ufw allow OpenSSH
ufw allow 'Nginx Full'
echo "y" | ufw enable || true

echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}   🎉 CARIKARYAWAN.COM Setup Selesai dengan Sukses!      ${NC}"
echo -e "${GREEN}======================================================${NC}"
echo -e "Informasi Database:"
echo -e "  - DB Name : ${DB_NAME}"
echo -e "  - DB User : ${DB_USER}"
echo -e "\nLangkah Terakhir untuk SSL Let's Encrypt (setelah domain diarahkan ke IP VPS):"
echo -e "  ${YELLOW}sudo certbot --nginx -d carikaryawan.com -d www.carikaryawan.com${NC}"
echo -e "======================================================"
