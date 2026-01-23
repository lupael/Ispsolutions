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

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration variables
DOMAIN_NAME=${DOMAIN_NAME:-"localhost"}
DB_NAME=${DB_NAME:-"ispsolution"}
DB_USER=${DB_USER:-"ispsolution"}
DB_PASSWORD=${DB_PASSWORD:-"$(openssl rand -base64 12)"}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-"$(openssl rand -base64 12)"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD:-"$(openssl rand -base64 12)"}
INSTALL_DIR="/var/www/ispsolution"
INSTALL_OPENVPN=${INSTALL_OPENVPN:-"no"}

# Functions
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run this script as root or with sudo"
        exit 1
    fi
}

print_banner() {
    echo -e "${GREEN}"
    cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║           ISP SOLUTION INSTALLATION SCRIPT                ║
║                                                           ║
║     Complete setup for Ubuntu (Fresh Installation)       ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
}

# Step 1: Update system packages
update_system() {
    print_info "Updating system packages..."
    apt-get update -y
    apt-get upgrade -y
    print_success "System packages updated"
}

# Step 2: Install basic dependencies
install_basic_dependencies() {
    print_info "Installing basic dependencies..."
    apt-get install -y \
        software-properties-common \
        curl \
        wget \
        git \
        unzip \
        zip \
        gnupg2 \
        ca-certificates \
        lsb-release \
        apt-transport-https \
        build-essential \
        openssl \
        ufw
    print_success "Basic dependencies installed"
}

# Step 3: Install PHP 8.2+
install_php() {
    print_info "Installing PHP 8.2 and extensions..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update -y
    apt-get install -y \
        php8.2 \
        php8.2-fpm \
        php8.2-cli \
        php8.2-common \
        php8.2-mysql \
        php8.2-zip \
        php8.2-gd \
        php8.2-mbstring \
        php8.2-curl \
        php8.2-xml \
        php8.2-bcmath \
        php8.2-redis \
        php8.2-intl \
        php8.2-soap \
        php8.2-imagick
    
    # Configure PHP
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/post_max_size = .*/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
    
    systemctl enable php8.2-fpm
    systemctl start php8.2-fpm
    
    print_success "PHP 8.2 installed and configured"
}

# Step 4: Install Composer
install_composer() {
    print_info "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    chmod +x /usr/local/bin/composer
    print_success "Composer installed"
}

# Step 5: Install Node.js and NPM
install_nodejs() {
    print_info "Installing Node.js LTS and NPM..."
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
    apt-get install -y nodejs
    npm install -g npm@latest
    print_success "Node.js and NPM installed"
}

# Step 6: Install MySQL 8.0
install_mysql() {
    print_info "Installing MySQL 8.0..."
    
    # Set root password before installation
    debconf-set-selections <<< "mysql-server mysql-server/root_password password ${DB_ROOT_PASSWORD}"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${DB_ROOT_PASSWORD}"
    
    apt-get install -y mysql-server mysql-client
    
    systemctl enable mysql
    systemctl start mysql
    
    # Secure MySQL installation
    mysql -uroot -p"${DB_ROOT_PASSWORD}" <<MYSQL_SCRIPT
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    print_success "MySQL 8.0 installed and secured"
}

# Step 7: Install Redis
install_redis() {
    print_info "Installing Redis..."
    apt-get install -y redis-server
    
    # Configure Redis
    sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
    
    systemctl enable redis-server
    systemctl restart redis-server
    
    print_success "Redis installed and configured"
}

# Step 8: Install Nginx
install_nginx() {
    print_info "Installing Nginx..."
    apt-get install -y nginx
    
    systemctl enable nginx
    systemctl start nginx
    
    print_success "Nginx installed"
}

# Step 9: Install FreeRADIUS
install_freeradius() {
    print_info "Installing FreeRADIUS server..."
    apt-get install -y \
        freeradius \
        freeradius-mysql \
        freeradius-utils
    
    systemctl enable freeradius
    
    print_success "FreeRADIUS installed"
}

# Step 10: Install OpenVPN (optional)
install_openvpn() {
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        print_info "Installing OpenVPN server..."
        apt-get install -y openvpn easy-rsa
        print_success "OpenVPN installed"
    else
        print_info "Skipping OpenVPN installation (set INSTALL_OPENVPN=yes to install)"
    fi
}

# Step 11: Clone repository
clone_repository() {
    print_info "Cloning ISP Solution repository..."
    
    if [ -d "$INSTALL_DIR" ]; then
        print_warning "Directory $INSTALL_DIR already exists. Backing up..."
        mv "$INSTALL_DIR" "${INSTALL_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    
    mkdir -p "$(dirname $INSTALL_DIR)"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    
    print_success "Repository cloned"
}

# Step 12: Setup databases
setup_databases() {
    print_info "Setting up databases..."
    
    # Create application database
    mysql -uroot -p"${DB_ROOT_PASSWORD}" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    # Create RADIUS database
    mysql -uroot -p"${DB_ROOT_PASSWORD}" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    print_success "Databases created"
}

# Step 13: Configure Laravel application
configure_laravel() {
    print_info "Configuring Laravel application..."
    
    cd "$INSTALL_DIR"
    
    # Copy environment file
    if [ ! -f .env ]; then
        cp .env.example .env
    fi
    
    # Update .env file
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN_NAME}|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    sed -i "s|RADIUS_DB_DATABASE=.*|RADIUS_DB_DATABASE=${RADIUS_DB_NAME}|" .env
    sed -i "s|RADIUS_DB_USERNAME=.*|RADIUS_DB_USERNAME=${RADIUS_DB_USER}|" .env
    sed -i "s|RADIUS_DB_PASSWORD=.*|RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD}|" .env
    
    # Install dependencies
    print_info "Installing PHP dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev
    
    # Generate application key
    php artisan key:generate --force
    
    # Set permissions
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage"
    chmod -R 775 "$INSTALL_DIR/bootstrap/cache"
    
    print_success "Laravel application configured"
}

# Step 14: Install Node.js dependencies
install_node_dependencies() {
    print_info "Installing Node.js dependencies and building assets..."
    cd "$INSTALL_DIR"
    npm install
    npm run build
    print_success "Node.js dependencies installed and assets built"
}

# Step 15: Run migrations
run_migrations() {
    print_info "Running database migrations..."
    cd "$INSTALL_DIR"
    php artisan migrate --force
    print_success "Database migrations completed"
}

# Step 16: Seed database (optional)
seed_database() {
    print_info "Seeding database with demo data..."
    cd "$INSTALL_DIR"
    php artisan db:seed --class=RoleSeeder --force
    php artisan db:seed --class=DemoSeeder --force
    print_success "Database seeded"
}

# Step 17: Configure Nginx
configure_nginx() {
    print_info "Configuring Nginx virtual host..."
    
    cat > /etc/nginx/sites-available/ispsolution <<NGINX_CONFIG
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME};
    root ${INSTALL_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX_CONFIG
    
    # Enable site
    ln -sf /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test configuration
    nginx -t
    
    # Reload Nginx
    systemctl reload nginx
    
    print_success "Nginx configured"
}

# Step 18: Configure firewall
configure_firewall() {
    print_info "Configuring firewall..."
    
    ufw --force enable
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow 22/tcp    # SSH
    ufw allow 80/tcp    # HTTP
    ufw allow 443/tcp   # HTTPS
    ufw allow 3306/tcp  # MySQL (optional, for remote access)
    ufw allow 1812/udp  # RADIUS auth
    ufw allow 1813/udp  # RADIUS accounting
    
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        ufw allow 1194/udp  # OpenVPN
    fi
    
    print_success "Firewall configured"
}

# Step 19: Configure FreeRADIUS
configure_freeradius() {
    print_info "Configuring FreeRADIUS to use MySQL..."
    
    # Enable SQL module
    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    
    # Configure SQL connection
    sed -i "s/driver = \"rlm_sql_null\"/driver = \"rlm_sql_mysql\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/dialect = \"sqlite\"/dialect = \"mysql\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/server = \"localhost\"/server = \"localhost\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/port = 3306/port = 3306/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/login = \"radius\"/login = \"${RADIUS_DB_USER}\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/password = \"radpass\"/password = \"${RADIUS_DB_PASSWORD}\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/radius_db = \"radius\"/radius_db = \"${RADIUS_DB_NAME}\"/" /etc/freeradius/3.0/mods-available/sql
    
    # Import RADIUS schema
    mysql -u"${RADIUS_DB_USER}" -p"${RADIUS_DB_PASSWORD}" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql 2>/dev/null || true
    
    # Restart FreeRADIUS
    systemctl restart freeradius
    
    print_success "FreeRADIUS configured"
}

# Step 20: Setup Laravel scheduler
setup_scheduler() {
    print_info "Setting up Laravel task scheduler..."
    
    # Add cron job for Laravel scheduler
    (crontab -l 2>/dev/null; echo "* * * * * cd $INSTALL_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    print_success "Laravel scheduler configured"
}

# Step 21: Optimize Laravel
optimize_laravel() {
    print_info "Optimizing Laravel application..."
    cd "$INSTALL_DIR"
    
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    print_success "Laravel optimized"
}

# Step 22: Display installation summary
display_summary() {
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                                                           ║${NC}"
    echo -e "${GREEN}║         INSTALLATION COMPLETED SUCCESSFULLY!              ║${NC}"
    echo -e "${GREEN}║                                                           ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Installation Details:${NC}"
    echo -e "  Application URL:     http://${DOMAIN_NAME}"
    echo -e "  Installation Dir:    ${INSTALL_DIR}"
    echo ""
    echo -e "${BLUE}Database Credentials:${NC}"
    echo -e "  MySQL Root Password: ${DB_ROOT_PASSWORD}"
    echo -e "  App DB Name:         ${DB_NAME}"
    echo -e "  App DB User:         ${DB_USER}"
    echo -e "  App DB Password:     ${DB_PASSWORD}"
    echo -e "  RADIUS DB Name:      ${RADIUS_DB_NAME}"
    echo -e "  RADIUS DB User:      ${RADIUS_DB_USER}"
    echo -e "  RADIUS DB Password:  ${RADIUS_DB_PASSWORD}"
    echo ""
    echo -e "${BLUE}Demo Accounts (password: password):${NC}"
    echo -e "  Developer:           developer@ispbills.com"
    echo -e "  Super Admin:         superadmin@ispbills.com"
    echo -e "  Admin:               admin@ispbills.com"
    echo -e "  Operator:            operator@ispbills.com"
    echo -e "  Sub-Operator:        suboperator@ispbills.com"
    echo -e "  Customer:            customer@ispbills.com"
    echo ""
    echo -e "${YELLOW}Important:${NC}"
    echo -e "  1. Save the database credentials shown above in a secure location"
    echo -e "  2. Configure your DNS to point to this server"
    echo -e "  3. Consider installing SSL certificate (Let's Encrypt)"
    echo -e "  4. Review security settings in production"
    echo -e "  5. Configure MikroTik routers in the admin panel"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    echo -e "  1. Visit http://${DOMAIN_NAME} to access the application"
    echo -e "  2. Login with one of the demo accounts"
    echo -e "  3. Configure your network services (RADIUS, MikroTik)"
    echo -e "  4. Read the documentation in ${INSTALL_DIR}/docs/"
    echo ""
    echo -e "${GREEN}For documentation, visit: ${INSTALL_DIR}/docs/${NC}"
    echo ""
    
    # Save credentials to file
    cat > /root/ispsolution-credentials.txt <<CREDENTIALS
ISP Solution Installation Credentials
======================================

Installation Date: $(date)
Server: $(hostname)

Application URL: http://${DOMAIN_NAME}
Installation Directory: ${INSTALL_DIR}

Database Credentials:
---------------------
MySQL Root Password: ${DB_ROOT_PASSWORD}
App DB Name: ${DB_NAME}
App DB User: ${DB_USER}
App DB Password: ${DB_PASSWORD}
RADIUS DB Name: ${RADIUS_DB_NAME}
RADIUS DB User: ${RADIUS_DB_USER}
RADIUS DB Password: ${RADIUS_DB_PASSWORD}

Demo Accounts (password: password):
------------------------------------
Developer: developer@ispbills.com
Super Admin: superadmin@ispbills.com
Admin: admin@ispbills.com
Operator: operator@ispbills.com
Sub-Operator: suboperator@ispbills.com
Customer: customer@ispbills.com

IMPORTANT: Keep this file secure and delete after saving credentials elsewhere!
CREDENTIALS
    
    print_success "Credentials saved to /root/ispsolution-credentials.txt"
}

# Main execution
main() {
    check_root
    print_banner
    
    print_info "Starting ISP Solution installation..."
    print_warning "This will take several minutes. Please be patient..."
    echo ""
    
    update_system
    install_basic_dependencies
    install_php
    install_composer
    install_nodejs
    install_mysql
    install_redis
    install_nginx
    install_freeradius
    install_openvpn
    clone_repository
    setup_databases
    configure_laravel
    install_node_dependencies
    run_migrations
    seed_database
    configure_nginx
    configure_firewall
    configure_freeradius
    setup_scheduler
    optimize_laravel
    display_summary
}

# Run main function
main "$@"
