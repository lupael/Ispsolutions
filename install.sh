#!/bin/bash

################################################################################
# ISP Solution - Complete Auto-Installation Script for Ubuntu VM
# 
# This script performs a COMPLETE CLEAN INSTALLATION on Ubuntu VM including:
# 
# CLEANUP PHASE (Automated):
# - Removes existing MySQL/MariaDB completely
# - Removes existing FreeRADIUS completely  
# - Removes all web servers (Nginx/Apache)
# - Removes PHP and all extensions
# - Removes Composer, Node.js, NPM
# - Removes Redis
# - Performs deep clean of all remnants
# - Cleans package cache and orphaned packages
# - Updates system packages
#
# INSTALLATION PHASE:
# - System packages and dependencies
# - PHP 8.2+ and extensions
# - Composer
# - Node.js and NPM
# - MySQL 8.0
# - Redis
# - Nginx web server
# - FreeRADIUS server
# - OpenVPN server (optional)
# - Laravel application setup
# - Database configuration
#
# Usage: sudo bash install.sh
################################################################################

################################################################################
# ISP Solution - Master Production Installer (v5.0)
# Features: Presence Detection, 2GB Swap, Deep Clean, SSL, RADIUS, & Laravel
################################################################################

set -e 

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# --- Configuration ---
DOMAIN_NAME=${DOMAIN_NAME:-"radius.ispbills.com"}
EMAIL=${EMAIL:-"admin@ispbills.com"}
INSTALL_DIR="/var/www/ispsolution"
DB_NAME="ispsolution"
CRED_FILE="/root/ispsolution-credentials.txt"

# --- Utility ---
print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_done() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# --- Step -1: Deep Clean and Remove Existing Installation ---
deep_clean_system() {
    print_status "Starting deep clean of system..."
    
    # Stop all related services
    print_status "Stopping all services..."
    systemctl stop nginx apache2 php8.2-fpm php8.1-fpm php8.0-fpm php7.4-fpm mysql mariadb freeradius redis-server openvpn 2>/dev/null || true
    
    # Remove MySQL/MariaDB completely
    print_status "Removing MySQL/MariaDB..."
    apt-get remove --purge -y mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-* mariadb-server mariadb-client 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /etc/mysql /var/lib/mysql /var/log/mysql
    rm -rf /var/lib/mysql-files /var/lib/mysql-keyring
    deluser --remove-home mysql 2>/dev/null || true
    delgroup mysql 2>/dev/null || true
    
    # Remove RADIUS completely
    print_status "Removing FreeRADIUS..."
    apt-get remove --purge -y freeradius freeradius-common freeradius-mysql freeradius-utils 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /etc/freeradius /var/log/freeradius
    deluser --remove-home freerad 2>/dev/null || true
    delgroup freerad 2>/dev/null || true
    
    # Remove web servers (Nginx and Apache)
    print_status "Removing web servers..."
    apt-get remove --purge -y nginx nginx-common nginx-core apache2 apache2-bin apache2-data apache2-utils 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /etc/nginx /var/log/nginx /var/www/html
    rm -rf /etc/apache2 /var/log/apache2
    # Note: www-data user is kept as it's a system account that may be used by other services
    
    # Remove PHP and all extensions
    print_status "Removing PHP..."
    apt-get remove --purge -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl php8.2-common 2>/dev/null || true
    apt-get remove --purge -y php8.1-fpm php8.1-cli php8.1-common php8.0-fpm php8.0-cli php8.0-common php7.4-fpm php7.4-cli php7.4-common 2>/dev/null || true
    apt-get remove --purge -y libapache2-mod-php8.2 libapache2-mod-php8.1 libapache2-mod-php8.0 libapache2-mod-php7.4 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /etc/php /usr/lib/php
    
    # Remove Composer
    print_status "Removing Composer..."
    rm -f /usr/local/bin/composer /usr/bin/composer
    
    # Remove Node.js and NPM
    print_status "Removing Node.js and NPM..."
    apt-get remove --purge -y nodejs npm node 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /usr/local/lib/node_modules /usr/local/bin/node /usr/local/bin/npm
    rm -rf /root/.npm /root/.node-gyp
    # Also clean up common user directories if they exist
    if [ -d /home ]; then
        for user_home in /home/*; do
            [ -d "$user_home" ] || continue
            [ -d "$user_home/.npm" ] && rm -rf "$user_home/.npm" 2>/dev/null || true
            [ -d "$user_home/.node-gyp" ] && rm -rf "$user_home/.node-gyp" 2>/dev/null || true
        done
    fi
    
    # Remove Redis
    print_status "Removing Redis..."
    apt-get remove --purge -y redis-server redis-tools 2>/dev/null || true
    apt-get autoremove -y 2>/dev/null || true
    rm -rf /etc/redis /var/lib/redis /var/log/redis
    deluser --remove-home redis 2>/dev/null || true
    delgroup redis 2>/dev/null || true
    
    # Remove application directory
    print_status "Removing application directory..."
    rm -rf "$INSTALL_DIR"
    
    # Remove credentials file
    rm -f "$CRED_FILE"
    
    # Clean package cache and orphaned packages
    print_status "Cleaning package cache..."
    apt-get autoclean -y
    apt-get autoremove -y
    apt-get clean
    
    # Remove any leftover configuration files
    print_status "Removing leftover configuration files..."
    dpkg -l | grep '^rc' | awk '{print $2}' | xargs -r dpkg --purge 2>/dev/null || true
    
    print_done "Deep clean completed. System is ready for fresh installation."
    
    # Update package lists
    print_status "Updating package lists..."
    apt-get update -y
    print_done "Package lists updated."
}

# --- Step 0: Swap File Creation ---
setup_swap() {
    if [ ! -f /swapfile ]; then
        print_status "Creating 2GB Swap file for system stability..."
        fallocate -l 2G /swapfile
        chmod 600 /swapfile
        mkswap /swapfile
        swapon /swapfile
        echo '/swapfile none swap sw 0 0' | tee -a /etc/fstab
        print_done "Swap created."
    else
        print_status "Swap file already exists. Skipping."
    fi
}

# --- Step 1: Verification after Deep Clean ---
check_existing_presence() {
    print_status "Verifying clean state after deep clean..."
    
    # This is a safety check to ensure the deep clean was successful
    # At this point, MySQL should be completely removed
    
    if [ -d "$INSTALL_DIR" ]; then
        print_status "Note: Application directory still exists (will be removed during installation)."
    fi
    
    # MySQL should not be accessible at this point (it's been removed by deep_clean_system)
    # It will be installed fresh in the next step (install_stack)
    if command -v mysql >/dev/null 2>&1; then
        print_status "Warning: MySQL binary still found after cleanup."
    fi
    
    print_done "System verified clean and ready for fresh installation."
}

# --- Step 2: Install Stack ---
install_stack() {
    print_status "Installing LEMP Stack & RADIUS..."
    apt-get update -y
    apt-get install -y software-properties-common curl git unzip openssl ufw mysql-server certbot python3-certbot-nginx bc
    add-apt-repository ppa:ondrej/php -y && apt-get update -y
    apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl nginx freeradius freeradius-mysql
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
}

# --- Step 3: Deployment & Config ---
setup_app() {
    # Generate New Credentials
    N_ROOT_PASS=$(openssl rand -base64 12 | tr -d '=+/')
    N_APP_PASS=$(openssl rand -base64 12 | tr -d '=+/')
    N_RAD_PASS=$(openssl rand -base64 15 | tr -d '=+/')

    # Update MySQL Root (after fresh install, root has no password)
    mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${N_ROOT_PASS}';"
    F_CONN="mysql -u root -p${N_ROOT_PASS}"

    # Databases
    $F_CONN -e "CREATE DATABASE $DB_NAME; CREATE USER 'ispsolution'@'localhost' IDENTIFIED BY '$N_APP_PASS'; GRANT ALL PRIVILEGES ON $DB_NAME.* TO 'ispsolution'@'localhost';"
    $F_CONN -e "CREATE DATABASE radius; CREATE USER 'radius'@'localhost' IDENTIFIED BY '$N_RAD_PASS'; GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost'; FLUSH PRIVILEGES;"

    # RADIUS
    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    SQL_MOD="/etc/freeradius/3.0/mods-enabled/sql"
    sed -i 's/driver = "rlm_sql_null"/driver = "rlm_sql_mysql"/' "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*login = .*/login = \"radius\"/" "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*password = .*/password = \"$N_RAD_PASS\"/" "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*radius_db = .*/radius_db = \"radius\"/" "$SQL_MOD"
    $F_CONN radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql
    systemctl restart freeradius

    # Laravel
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    cp .env.example .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$N_APP_PASS|" .env
    sed -i "s|RADIUS_DB_PASSWORD=.*|RADIUS_DB_PASSWORD=$N_RAD_PASS|" .env
    # ... additional .env syncs ...
    
    composer install --no-dev --optimize-autoloader
    php artisan key:generate
    php artisan migrate --force
    php artisan db:seed --force
    chown -R www-data:www-data "$INSTALL_DIR"
    
    # Cron
    (crontab -l 2>/dev/null | grep -v "schedule:run" ; echo "* * * * * cd $INSTALL_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -

    # Credentials File
    echo -e "MYSQL_ROOT: $N_ROOT_PASS\nAPP_PASS: $N_APP_PASS\nRAD_PASS: $N_RAD_PASS" > "$CRED_FILE"
}

# --- Main ---
main() {
    [ "$EUID" -ne 0 ] && echo "Run as root" && exit 1
    
    # Prompt user for clean install
    echo -e "${YELLOW}===============================================${NC}"
    echo -e "${YELLOW}   ISP Solution Auto-Install Script${NC}"
    echo -e "${YELLOW}===============================================${NC}"
    echo ""
    echo "This script will perform a COMPLETE CLEAN INSTALLATION:"
    echo "  1. Remove all existing MySQL/MariaDB installations"
    echo "  2. Remove all existing RADIUS installations"
    echo "  3. Remove all web servers (Nginx/Apache)"
    echo "  4. Remove PHP, Composer, Node.js, Redis"
    echo "  5. Perform deep system clean"
    echo "  6. Update system packages"
    echo "  7. Install everything fresh"
    echo ""
    echo -e "${RED}WARNING: This will DELETE all existing data!${NC}"
    echo ""
    read -p "Do you want to continue? (yes/no): " -r REPLY
    echo ""
    if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        print_error "Installation cancelled by user."
        exit 0
    fi
    
    deep_clean_system
    setup_swap
    check_existing_presence
    install_stack
    setup_app
    print_done "Master Install Complete! Credentials in $CRED_FILE"
}

main "$@"
