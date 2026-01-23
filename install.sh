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
# ISP Solution - Full Production Installer v3.0
# Features: Deep Clean, Presence Detection, SSL, RADIUS, Cron, & Sanity Check
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

# --- Step 0: Deep Clean (Remove Leftovers) ---
deep_clean() {
    echo -e "${YELLOW}Proceeding with Deep Clean...${NC}"

    # 1. Stop services to unlock files
    print_status "Stopping active services..."
    systemctl stop nginx php8.2-fpm freeradius mysql redis-server 2>/dev/null || true

    # 2. Wipe Application Files
    if [ -d "$INSTALL_DIR" ]; then
        print_status "Removing leftover application files at $INSTALL_DIR..."
        rm -rf "$INSTALL_DIR"
    fi

    # 3. Wipe Databases and Users
    print_status "Dropping old databases and users..."
    mysql -u root -e "DROP DATABASE IF EXISTS $DB_NAME; DROP DATABASE IF EXISTS radius; DELETE FROM mysql.user WHERE User='ispsolution' OR User='radius'; FLUSH PRIVILEGES;" 2>/dev/null || true

    # 4. Cleanup Configs
    print_status "Removing leftover configurations..."
    rm -f /etc/nginx/sites-enabled/ispsolution /etc/nginx/sites-available/ispsolution
    rm -f /etc/freeradius/3.0/mods-enabled/sql /etc/freeradius/3.0/mods-enabled/sqlcounter
    
    print_done "Deep Clean complete. System is ready for fresh install."
}

# --- Step 1: Presence Detection ---
check_existing() {
    if [ -d "$INSTALL_DIR" ] || mysql -u root -e "use $DB_NAME" 2>/dev/null; then
        echo -e "${RED}!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!${NC}"
        echo -e "${RED}   WARNING: EXISTING INSTALLATION DETECTED          ${NC}"
        echo -e "${RED}!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!${NC}"
        echo -e "1) Remove and Install Fresh (DEEP CLEAN) - DEFAULT"
        echo -e "2) Cancel Installation"
        echo ""
        
        CHOICE=1
        echo -n "Select [1/2] (Timeout 30s, Default 1): "
        read -t 30 CHOICE || CHOICE=1
        echo ""

        if [ "$CHOICE" == "2" ]; then
            print_error "Installation cancelled."
            exit 0
        fi
        deep_clean
    fi
}

# --- Step 2: Credential Generation ---
generate_creds() {
    print_status "Generating secure credentials..."
    DB_ROOT_PASSWORD=$(openssl rand -base64 12 | tr -d '=+/')
    DB_PASSWORD=$(openssl rand -base64 12 | tr -d '=+/')
    RADIUS_DB_PASSWORD=$(openssl rand -base64 15 | tr -d '=+/')
    
    {
        echo "MYSQL_ROOT_PASS: $DB_ROOT_PASSWORD"
        echo "APP_DB_PASS:     $DB_PASSWORD"
        echo "RADIUS_DB_PASS:  $RADIUS_DB_PASSWORD"
    } > "$CRED_FILE"
    chmod 600 "$CRED_FILE"
}

# --- Step 3: Install Stack ---
install_stack() {
    print_status "Installing PHP, Nginx, and MySQL..."
    apt-get update -y
    apt-get install -y software-properties-common curl git unzip openssl ufw mysql-server certbot python3-certbot-nginx
    add-apt-repository ppa:ondrej/php -y && apt-get update -y
    apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl nginx
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
}

# --- Step 4: Configure DB & RADIUS ---
setup_db_radius() {
    print_status "Configuring MySQL and FreeRADIUS..."
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';"
    
    # DB Setup
    mysql -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE DATABASE $DB_NAME; CREATE USER 'ispsolution'@'localhost' IDENTIFIED BY '$DB_PASSWORD'; GRANT ALL PRIVILEGES ON $DB_NAME.* TO 'ispsolution'@'localhost';"
    mysql -u root -p"${DB_ROOT_PASSWORD}" -e "CREATE DATABASE radius; CREATE USER 'radius'@'localhost' IDENTIFIED BY '$RADIUS_DB_PASSWORD'; GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost'; FLUSH PRIVILEGES;"
    
    # RADIUS Setup
    apt-get install -y freeradius freeradius-mysql
    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    SQL_MOD="/etc/freeradius/3.0/mods-enabled/sql"
    sed -i 's/driver = "rlm_sql_null"/driver = "rlm_sql_mysql"/' "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*login = .*/login = \"radius\"/" "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*password = .*/password = \"$RADIUS_DB_PASSWORD\"/" "$SQL_MOD"
    sed -i "s/^[[:space:]]*#[[:space:]]*radius_db = .*/radius_db = \"radius\"/" "$SQL_MOD"
    
    # Import Schema
    mysql -u root -p"${DB_ROOT_PASSWORD}" radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql
    systemctl restart freeradius
}

# --- Step 5: Laravel Setup ---
setup_laravel() {
    print_status "Cloning Laravel and running seeders..."
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    cp .env.example .env
    
    # Sync .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=ispsolution|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
    
    for K in RADIUS_DB_DATABASE RADIUS_DB_USERNAME RADIUS_DB_PASSWORD RADIUS_DB_HOST; do
        grep -q "$K" .env || echo "$K=" >> .env
    done
    sed -i "s|RADIUS_DB_HOST=.*|RADIUS_DB_HOST=127.0.0.1|" .env
    sed -i "s|RADIUS_DB_DATABASE=.*|RADIUS_DB_DATABASE=radius|" .env
    sed -i "s|RADIUS_DB_USERNAME=.*|RADIUS_DB_USERNAME=radius|" .env
    sed -i "s|RADIUS_DB_PASSWORD=.*|RADIUS_DB_PASSWORD=$RADIUS_DB_PASSWORD|" .env

    composer install --no-dev --optimize-autoloader
    php artisan key:generate
    php artisan migrate --force
    php artisan db:seed --force
    
    # Laravel Cron
    (crontab -l 2>/dev/null | grep -v "schedule:run" ; echo "* * * * * cd $INSTALL_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 storage bootstrap/cache
}

# --- Step 6: Nginx & SSL ---
setup_web() {
    print_status "Configuring Nginx and SSL..."
    cat > /etc/nginx/sites-available/ispsolution <<EOF
server {
    listen 80;
    server_name $DOMAIN_NAME;
    root $INSTALL_DIR/public;
    index index.php;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ { include snippets/fastcgi-php.conf; fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; }
}
EOF
    ln -sf /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    systemctl restart nginx
    
    # Certbot (Make sure DNS points to this IP)
    certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos -m "$EMAIL" --redirect || print_error "SSL Failed. Check DNS."
}

# --- Step 7: Sanity Check ---
run_sanity_check() {
    echo -e "\n${YELLOW}=== FINAL SANITY CHECK ===${NC}"
    cd "$INSTALL_DIR"
    php artisan tinker --execute="DB::connection()->getPdo(); print('Main DB: OK\n');" 2>/dev/null || print_error "Main DB Connection: FAIL"
    php artisan tinker --execute="DB::connection('radius')->getPdo(); print('Radius DB: OK\n');" 2>/dev/null || print_error "Radius DB Connection: FAIL"
    systemctl is-active --quiet freeradius && print_done "RADIUS Service: OK"
    crontab -l | grep -q "schedule:run" && print_done "Cron Job: OK"
    echo -e "${YELLOW}==========================${NC}\n"
}

# --- Execution ---
main() {
    check_root
    check_existing
    generate_creds
    install_stack
    setup_db_radius
    setup_laravel
    setup_web
    run_sanity_check
    print_done "Production installation complete! Credentials: $CRED_FILE"
}

main "$@"
