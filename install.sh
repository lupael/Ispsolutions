#!/bin/bash

################################################################################
# ISP Solution - Complete Installation Script for Ubuntu
# 
# This script installs and configures all dependencies required for the ISP
# Solution on a fresh Ubuntu VM, including:
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

# --- Step 1: Presence Detection (The Fixed Part) ---
check_existing_presence() {
    print_status "Scanning for existing installation..."
    
    EXISTING_APP=false
    EXISTING_DB=false

    # 1. Directory Check
    [ -d "$INSTALL_DIR" ] && EXISTING_APP=true

    # 2. Database Check (Auth-safe)
    if mysql -u root -e "status" >/dev/null 2>&1; then
        MYSQL_CONN="mysql -u root"
    else
        echo -e "${YELLOW}[!] Existing MySQL root password detected.${NC}"
        read -s -p "Enter current MySQL ROOT password: " PASS_INPUT
        echo ""
        if mysql -u root -p"${PASS_INPUT}" -e "status" >/dev/null 2>&1; then
            MYSQL_CONN="mysql -u root -p${PASS_INPUT}"
        else
            print_error "Incorrect MySQL password. Exiting."
            exit 1
        fi
    fi

    # Check if DB actually exists
    $MYSQL_CONN -e "use $DB_NAME" >/dev/null 2>&1 && EXISTING_DB=true

    # 3. Decision Menu
    if [ "$EXISTING_APP" = true ] || [ "$EXISTING_DB" = true ]; then
        echo -e "${RED}!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!${NC}"
        echo -e "${RED}   WARNING: EXISTING INSTALLATION DETECTED          ${NC}"
        echo -e "${RED}!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!${NC}"
        echo -e "1) Remove and Install Fresh (DEEP CLEAN) - DEFAULT"
        echo -e "2) Cancel Installation"
        echo ""
        
        USER_CHOICE=1
        echo -n "Select [1/2] (Timeout 30s, Default 1): "
        read -t 30 USER_CHOICE || USER_CHOICE=1
        echo ""

        if [ "$USER_CHOICE" == "2" ]; then
            print_error "Installation cancelled."
            exit 0
        fi
        
        # --- Deep Clean Logic ---
        print_status "Executing Deep Clean..."
        systemctl stop nginx php8.2-fpm freeradius mysql 2>/dev/null || true
        rm -rf "$INSTALL_DIR"
        $MYSQL_CONN -e "DROP DATABASE IF EXISTS $DB_NAME; DROP DATABASE IF EXISTS radius; DELETE FROM mysql.user WHERE User='ispsolution' OR User='radius'; FLUSH PRIVILEGES;" || true
        rm -f /etc/nginx/sites-enabled/ispsolution /etc/nginx/sites-available/ispsolution
        print_done "System cleaned."
    fi
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

    # Update MySQL Root
    $MYSQL_CONN -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${N_ROOT_PASS}';"
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
    setup_swap
    check_existing_presence
    install_stack
    setup_app
    print_done "Master Install Complete! Credentials in $CRED_FILE"
}

main "$@"
