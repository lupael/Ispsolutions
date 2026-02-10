#!/bin/bash

################################################################################
# ISP Solution - Master Installation Script (Ubuntu 24.04 Verified)
################################################################################

set -e

# Configuration
DOMAIN_NAME=${DOMAIN_NAME:-"radius.ispbills.com"}
DB_NAME=${DB_NAME:-"ispsolution"}
DB_USER=${DB_USER:-"ispsolution"}
DB_PASSWORD=${DB_PASSWORD:-"$(openssl rand -base64 12)"}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-"$(openssl rand -base64 12)"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD:-"$(openssl rand -base64 12)"}
INSTALL_DIR="/var/www/ispsolution"

print_status() { echo -e "\033[0;34m[INFO]\033[0m $1"; }
print_done() { echo -e "\033[0;32m[SUCCESS]\033[0m $1"; }

# 1. System Prep
print_status "Preparing System..."
apt-get update -y
apt-get install -y software-properties-common curl wget git unzip zip gnupg2 build-essential openssl ufw certbot python3-certbot-nginx logrotate

# 2. Silence services to prevent FreeRADIUS crash loop
echo -e '#!/bin/sh\nexit 101' | sudo tee /usr/sbin/policy-rc.d
sudo chmod +x /usr/sbin/policy-rc.d

# 3. Install MySQL & PHP Stack
print_status "Installing MySQL and PHP Stack..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis php8.2-intl php8.2-snmp \
    nginx redis-server mysql-server freeradius freeradius-mysql freeradius-utils

# Node.js (LTS)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# 4. Ensure MySQL is running
print_status "Verifying MySQL Service..."
sudo systemctl unmask mysql
sudo systemctl enable mysql
sudo systemctl start mysql

for i in {1..30}; do
    [ -S /var/run/mysqld/mysqld.sock ] && break
    print_status "Waiting for MySQL socket... $i"
    sleep 1
done

# 5. Database Setup
print_status "Configuring Databases..."
sudo mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

MYSQL_CONF=$(mktemp)
cat > "$MYSQL_CONF" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF

# 6. Credentials Summary
cat <<EOF > /root/ispsolution-credentials.txt
MySQL Root Password: ${DB_ROOT_PASSWORD}
App Database: ${DB_NAME} (User: ${DB_USER} / Pass: ${DB_PASSWORD})
Radius Database: ${RADIUS_DB_NAME} (User: ${RADIUS_DB_USER} / Pass: ${RADIUS_DB_PASSWORD})
EOF

# 7. FreeRADIUS Setup
print_status "Configuring FreeRADIUS..."
mysql --defaults-extra-file="$MYSQL_CONF" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql
[ -f /etc/freeradius/3.0/mods-config/sql/ippool/mysql/schema.sql ] && mysql --defaults-extra-file="$MYSQL_CONF" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/ippool/mysql/schema.sql
[ -f /etc/freeradius/3.0/mods-config/sql/counter/mysql/schema.sql ] && mysql --defaults-extra-file="$MYSQL_CONF" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/counter/mysql/schema.sql

cp /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-available/sql.bak
cat > /etc/freeradius/3.0/mods-available/sql <<EOF
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"
    server = "localhost"
    port = 3306
    login = "${RADIUS_DB_USER}"
    password = "${RADIUS_DB_PASSWORD}"
    radius_db = "${RADIUS_DB_NAME}"
    read_clients = yes
    client_query = "SELECT ip_address, secret, name FROM nas"
}
EOF
ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
chgrp freerad /etc/freeradius/3.0/mods-available/sql

sudo rm /usr/sbin/policy-rc.d
systemctl restart freeradius || true

# 8. Firewall Rules (fixed syntax)
print_status "Configuring Firewall..."
ufw allow 22/tcp
ufw allow 23/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 1812/udp
ufw allow 1813/udp
ufw allow 3306/tcp
ufw allow 6379/tcp
ufw allow 53
ufw allow 8080/tcp
ufw allow 8728/tcp
ufw allow 8729/tcp
ufw allow 8787/tcp
ufw allow 161/udp
ufw allow 162/udp
ufw allow 2222/tcp
ufw allow 2323/tcp
ufw allow 1700/udp
ufw --force enable

# 9. Web App Installation
print_status "Cloning ISP Solution..."
mkdir -p "$INSTALL_DIR"
git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
cd "$INSTALL_DIR"
cp .env.example .env

sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed --force
chown -R www-data:www-data "$INSTALL_DIR"

# 10. Frontend Build
print_status "Building frontend assets..."
npm install --legacy-peer-deps
npm run build

# 11. Laravel Scheduler (cron)
print_status "Configuring Laravel scheduler..."
( crontab -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1" ) | crontab -

# 12. Nginx Configuration
print_status "Configuring Nginx..."
cat > /etc/nginx/sites-available/ispsolution <<EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME};

    root ${INSTALL_DIR}/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# 13. SSL with Certbot
print_status "Obtaining SSL Certificate..."
certbot --nginx -d ${DOMAIN_NAME} --non-interactive --agree-tos -m admin@${DOMAIN_NAME}

# 14. Laravel Queue Worker Service
print_status "Configuring Laravel Queue Worker..."
cat > /etc/systemd/system/ispsolution-queue.service <<EOF
[Unit]
Description=Laravel Queue Worker for ISP Solution
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php ${INSTALL_DIR}/artisan queue:work --sleep=3 --tries=3 --timeout=90
WorkingDirectory=${INSTALL_DIR}
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=ispsolution-queue

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reexec
systemctl enable ispsolution-queue
systemctl start ispsolution-queue

# 15. Log Rotation
print_status "Configuring log rotation..."
cat > /etc/logrotate.d/ispsolution <<EOF
${INSTALL_DIR}/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
}
EOF

cat > /etc/logrotate.d/freeradius <<EOF
/var/log/freeradius/*.log {
    weekly
    missingok
    rotate 8
    compress
    delaycompress
    notifempty
    create 640 freerad freerad
}
EOF

[ -f "$MYSQL_CONF" ] && rm "$MYSQL_CONF"
print_done "Installation Finished! Check /root/ispsolution-credentials.txt"
