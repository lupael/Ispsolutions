#!/bin/bash

################################################################################
# ISP Solution - Master Installation Script (Ubuntu 24.04 Fixed)
################################################################################

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# --- Utility Functions ---
print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_done() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run as root (sudo bash install.sh)"
        exit 1
    fi
}

# --- Step 1: Essentials & Swap ---
install_essentials() {
    print_status "Installing system essentials..."
    apt-get update -y
    apt-get install -y software-properties-common curl wget git unzip zip gnupg2 \
        ca-certificates lsb-release apt-transport-https build-essential openssl snmp \
        ufw bc fail2ban
}

setup_swap() {
    if ! swapon --show | grep -q "/swapfile"; then
        print_status "Setting up 2GB swap..."
        fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
        chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
        echo '/swapfile none swap sw 0 0' >> /etc/fstab
    fi
}

# --- Step 2: Stack Installation ---
install_stack() {
    print_status "Installing PHP 8.2, Nginx, Redis, and MySQL..."
    
    # Disable service auto-start to prevent FreeRADIUS crash loop
    echo -e '#!/bin/sh\nexit 101' | sudo tee /usr/sbin/policy-rc.d
    sudo chmod +x /usr/sbin/policy-rc.d

    add-apt-repository ppa:ondrej/php -y
    apt-get update -y
    apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip php8.2-gd \
        php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis \
        php8.2-intl php8.2-imagick php8.2-snmp nginx redis-server mysql-server \
        freeradius freeradius-mysql freeradius-utils

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
}

# --- Step 3: Secure MySQL ---
configure_mysql() {
    print_status "Securing MySQL and creating databases..."

    sudo mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
FLUSH PRIVILEGES;
EOF

    export MYSQL_CONF=$(mktemp)
    cat > "$MYSQL_CONF" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF

    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"

    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME};"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';"
}

# --- Step 4: FreeRADIUS Configuration ---
configure_freeradius() {
    print_status "Configuring FreeRADIUS..."

    # Import schema while services are still "silenced"
    mysql --defaults-extra-file="$MYSQL_CONF" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    ln -sf /etc/freeradius/3.0/mods-available/sqlcounter /etc/freeradius/3.0/mods-enabled/

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
    chgrp freerad /etc/freeradius/3.0/mods-available/sql
    
    # Re-enable services and start
    sudo rm /usr/sbin/policy-rc.d
    systemctl restart freeradius
}

# --- Step 5: Laravel & Nginx ---
setup_laravel() {
    print_status "Setting up Laravel application..."
    mkdir -p "$INSTALL_DIR"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    cp .env.example .env

    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

    composer install --no-dev --optimize-autoloader
    php artisan key:generate
    php artisan migrate --seed --force
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 storage bootstrap/cache
}

setup_nginx() {
    print_status "Configuring Nginx..."
    cat > /etc/nginx/sites-available/"$DOMAIN_NAME" <<EOF
server {
    listen 80;
    server_name ${DOMAIN_NAME};
    root ${INSTALL_DIR}/public;
    index index.php;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
EOF
    ln -sf /etc/nginx/sites-available/"$DOMAIN_NAME" /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    systemctl restart nginx
}

save_credentials() {
    cat <<EOF > /root/ispsolution-credentials.txt
MySQL Root Password: ${DB_ROOT_PASSWORD}
App Database: ${DB_NAME} (User: ${DB_USER} / Pass: ${DB_PASSWORD})
Radius Database: ${RADIUS_DB_NAME} (User: ${RADIUS_DB_USER} / Pass: ${RADIUS_DB_PASSWORD})
EOF
    chmod 600 /root/ispsolution-credentials.txt
}

# --- Execution ---
main() {
    check_root
    install_essentials
    setup_swap
    install_stack
    configure_mysql
    configure_freeradius
    setup_laravel
    setup_nginx
    save_credentials
    [ -f "$MYSQL_CONF" ] && rm "$MYSQL_CONF"
    print_done "Installation Complete! Access via http://${DOMAIN_NAME}"
}

main "$@"
