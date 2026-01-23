#!/bin/bash

################################################################################
# ISP Solution - Complete Installation Script for Ubuntu 22.04+
################################################################################

# <<<----------------- BEGIN: DEFAULT ENVIRONMENT VARIABLES ------------------->
DOMAIN_NAME="radius.ispbills.com"
SETUP_SSL="yes"
EMAIL="admin@radius.ispbills.com"
# <<<------------------ END: DEFAULT ENVIRONMENT VARIABLES -------------------->>

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

DB_NAME=${DB_NAME:-"ispsolution"}
DB_USER=${DB_USER:-"ispsolution"}
DB_PASSWORD=${DB_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD:-"$(openssl rand -base64 18 | tr -d '=+/' | cut -c1-16)"}
INSTALL_DIR="/var/www/ispsolution"
INSTALL_OPENVPN=${INSTALL_OPENVPN:-"yes"}
SWAP_SIZE=${SWAP_SIZE:-"2G"}

print_info()      { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success()   { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning()   { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error()     { echo -e "${RED}[ERROR]${NC} $1"; }

check_root()      { if [ "$EUID" -ne 0 ]; then print_error "Run as root or with sudo"; exit 1; fi; }

print_banner() {
cat << EOF
${GREEN}
╔═══════════════════════════════════════════════════════════╗
║           ISP SOLUTION INSTALLATION SCRIPT               ║
║     Complete setup for Ubuntu 22.04+ (Fresh Install)     ║
╚═══════════════════════════════════════════════════════════╝
${NC}
EOF
}

setup_swap() {
    print_info "Setting up swap memory (${SWAP_SIZE})..."
    if swapon --show | grep -q "/swapfile"; then
        print_info "Swap file exists, skipping"
        return
    fi
    fallocate -l "$SWAP_SIZE" /swapfile 2>/dev/null || dd if=/dev/zero of=/swapfile bs=1M count=$(echo "$SWAP_SIZE"|sed 's/G/*1024/;s/M//;s/K/*1/;s/B//g'|bc)
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo "/swapfile none swap sw 0 0" >> /etc/fstab
    sysctl vm.swappiness=10
    sysctl vm.vfs_cache_pressure=50
    print_success "Swap configured: $(free -h | awk '/Swap:/ {print $2}')"
}

update_system() {
    print_info "Updating system packages..."
    DEBIAN_FRONTEND=noninteractive apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get upgrade -y
    print_success "System updated"
}

install_basic_dependencies() {
    print_info "Installing basic dependencies..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y software-properties-common curl wget git unzip zip gnupg2 ca-certificates lsb-release apt-transport-https build-essential openssl ufw dialog
    print_success "Basic dependencies installed"
}

install_php() {
    print_info "Installing PHP 8.2..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis php8.2-intl php8.2-soap php8.2-imagick
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/post_max_size = .*/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
    sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
    systemctl enable php8.2-fpm
    systemctl start php8.2-fpm
    print_success "PHP installed and configured"
}

install_composer() {
    print_info "Installing Composer..."
    if ! command -v composer >/dev/null 2>&1; then
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        chmod +x /usr/local/bin/composer
        print_success "Composer installed"
    else
        print_info "Composer already installed"
    fi
}

install_nodejs() {
    print_info "Installing Node.js LTS..."
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
    DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs
    npm install -g npm@latest
    print_success "Node.js and npm ready"
}

install_mysql() {
    print_info "Installing MySQL 8.0..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server mysql-client
    systemctl enable mysql
    systemctl start mysql
    # Set password and auth plugin for root
    if ! mysql -u root -e "SELECT user,plugin FROM mysql.user;" | grep 'mysql_native_password' | grep root > /dev/null; then
        sudo mysql <<MYSQL_SCRIPT
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
        print_success "MySQL root password and plugin set."
    fi
    print_success "MySQL 8.0 installed and configured"
}

install_redis() {
    print_info "Installing Redis..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y redis-server
    sed -i 's/^supervised .*/supervised systemd/' /etc/redis/redis.conf
    systemctl enable redis-server
    systemctl restart redis-server
    print_success "Redis installed"
}

install_nginx() {
    print_info "Installing Nginx..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y nginx
    systemctl enable nginx
    systemctl start nginx
    print_success "Nginx up"
}

install_freeradius() {
    print_info "Installing FreeRADIUS..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y freeradius freeradius-mysql freeradius-utils
    systemctl enable freeradius
    print_success "FreeRADIUS installed"
}

install_openvpn() {
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        print_info "Installing OpenVPN and Easy-RSA 3.x..."
        DEBIAN_FRONTEND=noninteractive apt-get install -y openvpn easy-rsa
        make-cadir ~/openvpn-ca
        cd ~/openvpn-ca

        ./easyrsa init-pki
        yes "" | ./easyrsa --batch build-ca nopass
        ./easyrsa --batch gen-req server nopass
        echo "yes" | ./easyrsa --batch sign-req server server
        ./easyrsa gen-dh
        openvpn --genkey secret ta.key

        cp pki/ca.crt pki/issued/server.crt pki/private/server.key pki/dh.pem ta.key /etc/openvpn/

        cat > /etc/openvpn/server.conf <<EOF
port 1194
proto udp
dev tun
ca ca.crt
cert server.crt
key server.key
dh dh.pem
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

        echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
        sysctl -p
        ufw allow 1194/udp
        systemctl start openvpn@server
        systemctl enable openvpn@server
        cd "$INSTALL_DIR"
        print_success "OpenVPN server installed and configured"
        print_info "Client certs in ~/openvpn-ca/pki/"
    else
        print_info "Skipping OpenVPN (INSTALL_OPENVPN!=yes)"
    fi
}

clone_repository() {
    print_info "Cloning ISP Solution repository..."
    if [ -d "$INSTALL_DIR" ]; then
        print_warning "Dir $INSTALL_DIR exists. Backing up..."
        mv "$INSTALL_DIR" "${INSTALL_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    mkdir -p "$(dirname "$INSTALL_DIR")"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"
    print_success "Repository cloned"
}

setup_databases() {
    print_info "Setting up databases..."
    if ! command -v mysql >/dev/null 2>&1; then
        print_error "mysql client not found"
        exit 1
    fi
    MYSQL_CREDS=$(mktemp)
    cat > "$MYSQL_CREDS" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF
    chmod 600 "$MYSQL_CREDS"
    mysql --defaults-extra-file="$MYSQL_CREDS" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

    mysql --defaults-extra-file="$MYSQL_CREDS" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

    rm -f "$MYSQL_CREDS"
    print_success "Databases created"
}

configure_laravel() {
    print_info "Configuring Laravel..."
    cd "$INSTALL_DIR"
    [ -f .env ] || cp .env.example .env
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN_NAME}|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    sed -i "s|RADIUS_DB_DATABASE=.*|RADIUS_DB_DATABASE=${RADIUS_DB_NAME}|" .env
    sed -i "s|RADIUS_DB_USERNAME=.*|RADIUS_DB_USERNAME=${RADIUS_DB_USER}|" .env
    sed -i "s|RADIUS_DB_PASSWORD=.*|RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD}|" .env
    if ! command -v composer > /dev/null 2>&1; then print_error "composer not available"; exit 1; fi
    composer install --no-interaction --optimize-autoloader --no-dev
    php artisan key:generate --force
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
    print_success "Laravel configured"
}

install_node_dependencies() {
    print_info "Building front-end assets..."
    cd "$INSTALL_DIR"
    if ! command -v npm >/dev/null 2>&1; then print_error "npm not available"; exit 1; fi
    npm install
    npm run build
    print_success "Node assets built"
}

run_migrations() {
    print_info "Migrating database..."
    cd "$INSTALL_DIR"
    php artisan migrate --force
    print_success "Migrations complete"
}

seed_database() {
    print_info "Seeding DB..."
    cd "$INSTALL_DIR"
    php artisan db:seed --class=RoleSeeder --force
    php artisan db:seed --class=DemoSeeder --force
    print_success "Seeded"
}

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
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
NGINX_CONFIG
    ln -sf /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    nginx -t
    systemctl reload nginx
    print_success "Nginx configured"
}

configure_firewall() {
    print_info "Configuring firewall..."
    ufw --force enable
    ufw default deny incoming; ufw default allow outgoing
    ufw allow 22/tcp; ufw allow 80/tcp; ufw allow 443/tcp; ufw allow 3306/tcp; ufw allow 1812/udp; ufw allow 1813/udp
    [ "$INSTALL_OPENVPN" = "yes" ] && ufw allow 1194/udp
    print_success "Firewall ready"
}

configure_freeradius() {
    print_info "Configuring FreeRADIUS..."
    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    sed -i "s/driver = .*/driver = \"rlm_sql_mysql\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/dialect = .*/dialect = \"mysql\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/login = .*/login = \"${RADIUS_DB_USER}\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/password = .*/password = \"${RADIUS_DB_PASSWORD}\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/radius_db = .*/radius_db = \"${RADIUS_DB_NAME}\"/" /etc/freeradius/3.0/mods-available/sql

    RADIUS_CREDS=$(mktemp)
    cat > "$RADIUS_CREDS" <<EOF
[client]
user=${RADIUS_DB_USER}
password=${RADIUS_DB_PASSWORD}
EOF
    chmod 600 "$RADIUS_CREDS"
    mysql --defaults-extra-file="$RADIUS_CREDS" "${RADIUS_DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql 2>/dev/null || true
    rm -f "$RADIUS_CREDS"
    systemctl restart freeradius
    print_success "FreeRADIUS configured"
}

setup_ssl() {
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        print_info "Setting up Let's Encrypt SSL..."
        [ -z "$EMAIL" ] && { print_error "EMAIL required for SSL."; return; }
        DEBIAN_FRONTEND=noninteractive apt-get install -y certbot python3-certbot-nginx
        certbot --nginx -d "${DOMAIN_NAME}" --non-interactive --agree-tos --email "${EMAIL}" --redirect
        systemctl enable certbot.timer || true
        systemctl start certbot.timer || true
        print_success "SSL certified for ${DOMAIN_NAME}"
    else
        print_info "Skipping SSL (domain is localhost or SETUP_SSL!=yes)"
    fi
}

setup_subdomain_automation() {
    print_info "Configuring tenant subdomain automation..."
    cat > /usr/local/bin/create-tenant-subdomain.sh <<'SUBDOMAIN_SCRIPT'
#!/bin/bash
SUBDOMAIN=$1
TENANT_ID=$2
BASE_DOMAIN="${DOMAIN_NAME}"
INSTALL_DIR="${INSTALL_DIR}"

[ -z "$SUBDOMAIN" ] || [ -z "$TENANT_ID" ] && { echo "Usage: $0 <subdomain> <tenant_id>"; exit 1; }
FULL_DOMAIN="${SUBDOMAIN}.${BASE_DOMAIN}"

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
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
NGINX_SUB

ln -sf /etc/nginx/sites-available/${SUBDOMAIN} /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
echo "Subdomain ${FULL_DOMAIN} created."

if [ -f /usr/bin/certbot ] && [ "${BASE_DOMAIN}" != "localhost" ]; then
    certbot --nginx -d "${FULL_DOMAIN}" --non-interactive --agree-tos --email "${EMAIL:-admin@${BASE_DOMAIN}}" --redirect 2>/dev/null || \
      echo "Warn: SSL not obtained for ${FULL_DOMAIN}. Try manually."
fi
SUBDOMAIN_SCRIPT
    chmod +x /usr/local/bin/create-tenant-subdomain.sh
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN_NAME}|" "$INSTALL_DIR/.env"
    print_success "Tenant subdomain automation ready. Usage: sudo create-tenant-subdomain.sh <subdomain> <tenant_id>"
}

setup_scheduler() {
    print_info "Setting up scheduler..."
    (crontab -l 2>/dev/null; echo "* * * * * cd $INSTALL_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -
    print_success "Scheduler installed"
}

optimize_laravel() {
    print_info "Optimizing Laravel..."
    cd "$INSTALL_DIR"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    print_success "Laravel optimized"
}

display_summary() {
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║         INSTALLATION COMPLETED SUCCESSFULLY!              ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Installation Details:${NC}"
    echo -e "  Application URL:     http://${DOMAIN_NAME}"
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        echo -e "  SSL Enabled:         ✓ https://${DOMAIN_NAME}"
    fi
    echo -e "  Installation Dir:    ${INSTALL_DIR}"
    echo -e "  Swap Memory:         $(free -h | awk '/Swap:/ {print $2}')"
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
    cat > /root/ispsolution-credentials.txt <<CREDENTIALS
ISP Solution Installation Credentials
======================================
Date: $(date)
Server: $(hostname)
URL: http://${DOMAIN_NAME}
Install dir: ${INSTALL_DIR}
MySQL Root: ${DB_ROOT_PASSWORD}
App DB Name: ${DB_NAME}
App DB User: ${DB_USER}
App DB Pass: ${DB_PASSWORD}
RADIUS DB Name: ${RADIUS_DB_NAME}
RADIUS DB User: ${RADIUS_DB_USER}
RADIUS DB Pass: ${RADIUS_DB_PASSWORD}
IMPORTANT: Save credentials and delete this file when done!
CREDENTIALS
    chmod 600 /root/ispsolution-credentials.txt
    print_success "Credentials saved to /root/ispsolution-credentials.txt"
}

main() {
    check_root
    print_banner
    print_info "Starting ISP Solution install..."
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

main "$@"
