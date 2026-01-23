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
DB_PASSWORD=${DB_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
INSTALL_DIR="/var/www/ispsolution"
INSTALL_OPENVPN=${INSTALL_OPENVPN:-"yes"}  # Changed default to yes
SETUP_SSL=${SETUP_SSL:-"no"}  # Set to "yes" to install SSL with Let's Encrypt
SWAP_SIZE=${SWAP_SIZE:-"2G"}  # Default 2GB swap
EMAIL=${EMAIL:-""}  # Email for SSL certificate notifications

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

# Step 1: Setup swap memory
setup_swap() {
    print_info "Setting up swap memory (${SWAP_SIZE})..."
    
    # Check if swap already exists
    if swapon --show | grep -q "/swapfile"; then
        print_info "Swap file already exists, skipping creation"
        return
    fi
    
    # Check available disk space
    AVAILABLE_SPACE=$(df -BG / | awk 'NR==2 {print $4}' | sed 's/G//')
    REQUIRED_SPACE=$(echo "$SWAP_SIZE" | sed 's/G//')
    
    if [ "$AVAILABLE_SPACE" -lt "$REQUIRED_SPACE" ]; then
        print_warning "Not enough disk space for ${SWAP_SIZE} swap. Available: ${AVAILABLE_SPACE}G"
        print_info "Reducing swap size to 1G"
        SWAP_SIZE="1G"
    fi
    
    # Create swap file
    if ! fallocate -l "$SWAP_SIZE" /swapfile 2>/dev/null; then
        print_info "fallocate failed, using dd instead..."
        SWAP_MB=$(echo "$SWAP_SIZE" | sed 's/G/*1024/g; s/M//g' | bc)
        dd if=/dev/zero of=/swapfile bs=1M count="$SWAP_MB" status=progress
    fi
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    
    # Make swap permanent
    if ! grep -q "/swapfile" /etc/fstab; then
        echo "/swapfile none swap sw 0 0" >> /etc/fstab
    fi
    
    # Optimize swap settings
    if ! grep -q "vm.swappiness" /etc/sysctl.conf; then
        echo "vm.swappiness=10" >> /etc/sysctl.conf
        echo "vm.vfs_cache_pressure=50" >> /etc/sysctl.conf
        sysctl -p
    fi
    
    print_success "Swap memory configured: $(free -h | grep Swap | awk '{print $2}')"
}

# Step 2: Update system packages
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
    # Note: This method exposes password briefly in process list
    # For production, consider using mysql_config_editor or environment variables
    debconf-set-selections <<< "mysql-server mysql-server/root_password password ${DB_ROOT_PASSWORD}"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${DB_ROOT_PASSWORD}"
    
    apt-get install -y mysql-server mysql-client
    
    systemctl enable mysql
    systemctl start mysql
    
    # Secure MySQL installation
    # Note: Using heredoc to avoid password in command line. Still visible in process briefly.
    mysql --defaults-extra-file=<(cat <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF
) <<MYSQL_SCRIPT
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

# Step 10: Install and configure OpenVPN
install_openvpn() {
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        print_info "Installing and configuring OpenVPN server..."
        apt-get install -y openvpn easy-rsa
        
        # Setup Easy-RSA
        make-cadir ~/openvpn-ca
        cd ~/openvpn-ca
        
        # Configure vars file
        cat > vars <<EOF
export KEY_COUNTRY="US"
export KEY_PROVINCE="CA"
export KEY_CITY="SanFrancisco"
export KEY_ORG="ISP Solution"
export KEY_EMAIL="${EMAIL:-admin@example.com}"
export KEY_OU="ISP"
export KEY_NAME="server"
EOF
        
        # Build CA and server certificates
        source vars
        ./clean-all 2>/dev/null || true
        
        print_info "Generating CA certificate..."
        ./build-ca --batch 2>&1 | grep -v "^Generating" || {
            print_error "Failed to generate CA certificate"
            return 1
        }
        
        print_info "Generating server certificate..."
        ./build-key-server --batch server 2>&1 | grep -v "^Generating" || {
            print_error "Failed to generate server certificate"
            return 1
        }
        
        print_info "Generating Diffie-Hellman parameters (this may take a while)..."
        ./build-dh 2>&1 | tail -1 || {
            print_error "Failed to generate DH parameters"
            return 1
        }
        
        print_info "Generating TLS auth key..."
        openvpn --genkey --secret keys/ta.key || {
            print_error "Failed to generate TLS auth key"
            return 1
        }
        
        # Copy certificates to OpenVPN directory
        cd ~/openvpn-ca/keys
        if [ -f ca.crt ] && [ -f server.crt ] && [ -f server.key ] && [ -f ta.key ]; then
            cp ca.crt server.crt server.key ta.key dh2048.pem /etc/openvpn/
            print_success "OpenVPN certificates generated and installed"
        else
            print_error "OpenVPN certificate generation incomplete"
            return 1
        fi
        
        # Create server configuration
        cat > /etc/openvpn/server.conf <<EOF
port 1194
proto udp
dev tun
ca ca.crt
cert server.crt
key server.key
dh dh2048.pem
server 10.8.0.0 255.255.255.0
ifconfig-pool-persist ipp.txt
push "redirect-gateway def1 bypass-dhcp"
push "dhcp-option DNS 8.8.8.8"
push "dhcp-option DNS 8.8.4.4"
keepalive 10 120
tls-auth ta.key 0
cipher AES-256-CBC
user nobody
group nogroup
persist-key
persist-tun
status openvpn-status.log
verb 3
EOF
        
        # Enable IP forwarding
        echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
        sysctl -p
        
        # Configure firewall for OpenVPN
        ufw allow 1194/udp
        
        # Start and enable OpenVPN
        systemctl start openvpn@server
        systemctl enable openvpn@server
        
        cd "$INSTALL_DIR"
        
        print_success "OpenVPN server installed and configured"
        print_info "OpenVPN configuration: /etc/openvpn/server.conf"
        print_info "Client certificates: ~/openvpn-ca/keys/"
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
    
    mkdir -p "$(dirname "$INSTALL_DIR")"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    
    print_success "Repository cloned"
}

# Step 12: Setup databases
setup_databases() {
    print_info "Setting up databases..."
    
    # Create MySQL credentials file for secure access
    MYSQL_CREDS=$(mktemp)
    cat > "$MYSQL_CREDS" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF
    chmod 600 "$MYSQL_CREDS"
    
    # Create application database
    mysql --defaults-extra-file="$MYSQL_CREDS" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    # Create RADIUS database
    mysql --defaults-extra-file="$MYSQL_CREDS" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    
    # Clean up credentials file
    rm -f "$MYSQL_CREDS"
    
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
    
    # Import RADIUS schema using secure credentials
    RADIUS_CREDS=$(mktemp)
    cat > "$RADIUS_CREDS" <<EOF
[client]
user=${RADIUS_DB_USER}
password=${RADIUS_DB_PASSWORD}
EOF
    chmod 600 "$RADIUS_CREDS"
    
    mysql --defaults-extra-file="$RADIUS_CREDS" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql 2>/dev/null || true
    
    # Clean up credentials file
    rm -f "$RADIUS_CREDS"
    
    # Restart FreeRADIUS
    systemctl restart freeradius
    
    print_success "FreeRADIUS configured"
}

# Step 20: Setup SSL with Let's Encrypt
setup_ssl() {
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        print_info "Setting up SSL certificate with Let's Encrypt..."
        
        # Validate email is provided
        if [ -z "$EMAIL" ]; then
            print_error "EMAIL environment variable required for SSL setup"
            print_info "Skipping SSL setup. Run manually: certbot --nginx -d ${DOMAIN_NAME}"
            return
        fi
        
        # Install certbot
        apt-get install -y certbot python3-certbot-nginx
        
        # Obtain and install certificate
        certbot --nginx -d "${DOMAIN_NAME}" --non-interactive --agree-tos --email "${EMAIL}" --redirect
        
        # Setup auto-renewal
        systemctl enable certbot.timer
        systemctl start certbot.timer
        
        print_success "SSL certificate installed for ${DOMAIN_NAME}"
        print_info "Certificate will auto-renew via systemd timer"
    else
        if [ "$DOMAIN_NAME" = "localhost" ]; then
            print_info "Skipping SSL setup (domain is localhost)"
        else
            print_info "Skipping SSL setup (set SETUP_SSL=yes and EMAIL=your@email.com to enable)"
        fi
    fi
}

# Step 21: Setup tenant subdomain automation
setup_subdomain_automation() {
    print_info "Setting up tenant subdomain automation..."
    
    # Create script for subdomain creation
    cat > /usr/local/bin/create-tenant-subdomain.sh <<'SUBDOMAIN_SCRIPT'
#!/bin/bash
# Script to create subdomain for new tenant
# Usage: create-tenant-subdomain.sh <subdomain> <tenant_id>

SUBDOMAIN=$1
TENANT_ID=$2
BASE_DOMAIN="${DOMAIN_NAME}"
INSTALL_DIR="${INSTALL_DIR}"

if [ -z "$SUBDOMAIN" ] || [ -z "$TENANT_ID" ]; then
    echo "Usage: $0 <subdomain> <tenant_id>"
    exit 1
fi

FULL_DOMAIN="${SUBDOMAIN}.${BASE_DOMAIN}"

# Create Nginx configuration for subdomain
cat > /etc/nginx/sites-available/${SUBDOMAIN} <<NGINX_SUB
server {
    listen 80;
    listen [::]:80;
    server_name ${FULL_DOMAIN};
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
NGINX_SUB

# Enable site
ln -sf /etc/nginx/sites-available/${SUBDOMAIN} /etc/nginx/sites-enabled/

# Test and reload Nginx
nginx -t && systemctl reload nginx

echo "Subdomain ${FULL_DOMAIN} created successfully"

# If SSL is enabled, obtain certificate for subdomain
if [ -f /usr/bin/certbot ] && [ "${BASE_DOMAIN}" != "localhost" ]; then
    echo "Obtaining SSL certificate for ${FULL_DOMAIN}..."
    if certbot --nginx -d "${FULL_DOMAIN}" --non-interactive --agree-tos --email "${EMAIL:-admin@${BASE_DOMAIN}}" --redirect 2>/dev/null; then
        echo "SSL certificate obtained successfully"
    else
        echo "Warning: Failed to obtain SSL certificate for ${FULL_DOMAIN}"
        echo "You can manually obtain it later with: certbot --nginx -d ${FULL_DOMAIN}"
    fi
fi
SUBDOMAIN_SCRIPT

    # Make script executable
    chmod +x /usr/local/bin/create-tenant-subdomain.sh
    
    # Update .env with domain placeholders
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN_NAME}|" "$INSTALL_DIR/.env"
    
    print_success "Tenant subdomain automation configured"
    print_info "Use: create-tenant-subdomain.sh <subdomain> <tenant_id>"
}

# Step 22: Setup Laravel scheduler
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
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        echo -e "  SSL Enabled:         ✓ https://${DOMAIN_NAME}"
    fi
    echo -e "  Installation Dir:    ${INSTALL_DIR}"
    echo -e "  Swap Memory:         $(free -h | grep Swap | awk '{print $2}')"
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        echo -e "  OpenVPN:             ✓ Installed and configured"
    fi
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
    if [ "$SETUP_SSL" != "yes" ]; then
        echo -e "  3. Consider installing SSL certificate (Let's Encrypt)"
    fi
    echo -e "  4. Review security settings in production"
    echo -e "  5. Configure MikroTik routers in the admin panel"
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        echo -e "  6. Generate OpenVPN client configs from ~/openvpn-ca/keys/"
    fi
    echo ""
    echo -e "${BLUE}Tenant Subdomain:${NC}"
    echo -e "  To create tenant subdomains automatically:"
    echo -e "  sudo create-tenant-subdomain.sh <subdomain> <tenant_id>"
    echo -e "  Example: sudo create-tenant-subdomain.sh tenant1 123"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        echo -e "  1. Visit https://${DOMAIN_NAME} to access the application"
    else
        echo -e "  1. Visit http://${DOMAIN_NAME} to access the application"
    fi
    echo -e "  2. Login with one of the demo accounts"
    echo -e "  3. Configure your network services (RADIUS, MikroTik)"
    echo -e "  4. Read the documentation in ${INSTALL_DIR}/docs/"
    echo ""
    echo -e "${GREEN}For documentation, visit: ${INSTALL_DIR}/docs/${NC}"
    echo ""
    
    # Save credentials to file with secure permissions
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
    
    # Set secure permissions on credentials file
    chmod 600 /root/ispsolution-credentials.txt
    
    print_success "Credentials saved to /root/ispsolution-credentials.txt (secure permissions applied)"
    print_warning "IMPORTANT: Copy these credentials to a secure location and delete this file!"
}

# Main execution
main() {
    check_root
    print_banner
    
    print_info "Starting ISP Solution installation..."
    print_warning "This will take several minutes. Please be patient..."
    echo ""
    
    setup_swap
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
    setup_ssl
    setup_subdomain_automation
    setup_scheduler
    optimize_laravel
    display_summary
}

# Run main function
main "$@"
