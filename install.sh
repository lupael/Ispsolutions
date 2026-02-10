#!/bin/bash

################################################################################
# ISP Solution - Optimized Installation Script (Enhanced for Ubuntu 24.04)
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

# --- Step 1 & 2: Essentials & Swap ---
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

# --- Step 3: PHP & Web Stack ---
install_stack() {
    print_status "Installing PHP 8.2, Nginx, Redis, and MySQL..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update -y
    apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip php8.2-gd \
        php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis \
        php8.2-intl php8.2-imagick php8.2-snmp nginx redis-server mysql-server

    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
}

# --- Step 4: Secure MySQL (CORRECTED FOR 24.04) ---
configure_mysql() {
    print_status "Securing MySQL and creating databases..."

    # 1. Reset root to use a password and native plugin for script compatibility
    sudo mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
FLUSH PRIVILEGES;
EOF

    # 2. Create a temporary .my.cnf so the script can run mysql commands without "Access Denied"
    export MYSQL_CONF=$(mktemp)
    cat > "$MYSQL_CONF" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF

    # 3. Create Databases and Users
    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"

    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME};"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';"
    mysql --defaults-extra-file="$MYSQL_CONF" -e "GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';"
    
    print_done "MySQL configured."
}

# --- Step 5: FreeRADIUS (CORRECTED) ---
configure_freeradius() {
    print_status "Configuring FreeRADIUS..."
    apt-get install -y freeradius freeradius-mysql freeradius-utils

    # CRITICAL: Import the schema first so the service doesn't fail on start
    print_status "Importing FreeRADIUS MySQL Schema..."
    mysql --defaults-extra-file="$MYSQL_CONF" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    
    # Configure SQL Module
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
    systemctl restart freeradius
}

# --- Steps 6-9: OpenVPN, Laravel, Nginx (Standard) ---
configure_openvpn() {
    print_status "Setting up OpenVPN..."
    apt-get install -y openvpn easy-rsa
    echo 'net.ipv4.ip_forward=1' >> /etc/sysctl.conf && sysctl -p
    # (Remaining OpenVPN logic same as yours...)
}

setup_laravel() {
    print_status "Cloning and configuring Laravel..."
    mkdir -p "$INSTALL_DIR"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    cp .env.example .env

    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

    composer install --no-dev --optimize-autoloader
    php artisan key:generate
    # Use the temp config for migration
    php artisan migrate --seed --force
    chown -R www-data:www-data "$INSTALL_DIR"
}

setup_nginx() {
    print_status "Configuring Nginx..."
    # (Nginx config logic same as yours...)
}

save_credentials() {
    cat <<EOF > /root/ispsolution-credentials.txt
MySQL Root Password: ${DB_ROOT_PASSWORD}
App DB User: ${DB_USER} / Pass: ${DB_PASSWORD}
Radius DB User: ${RADIUS_DB_USER} / Pass: ${RADIUS_DB_PASSWORD}
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
    # configure_openvpn (optional)
    setup_laravel
    setup_nginx
    save_credentials
    
    # Cleanup the temp MySQL config
    [ -f "$MYSQL_CONF" ] && rm "$MYSQL_CONF"
    
    print_done "Installation Complete! Credentials in /root/ispsolution-credentials.txt"
}

main "$@"
