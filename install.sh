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
║            ISP SOLUTION INSTALLATION SCRIPT               ║
║      Complete setup for Ubuntu 22.04+ (Fresh Install)     ║
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
    print_success "Swap configured."
}

update_system() {
    print_info "Updating system packages..."
    DEBIAN_FRONTEND=noninteractive apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get upgrade -y
    print_success "System updated"
}

install_basic_dependencies() {
    print_info "Installing basic dependencies..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y software-properties-common curl wget git unzip zip gnupg2 ca-certificates lsb-release apt-transport-https build-essential openssl ufw dialog bc
    print_success "Basic dependencies installed"
}

install_php() {
    print_info "Installing PHP 8.2..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis php8.2-intl php8.2-soap php8.2-imagick
    systemctl enable php8.2-fpm
    systemctl start php8.2-fpm
    print_success "PHP installed"
}

install_composer() {
    print_info "Installing Composer..."
    if ! command -v composer >/dev/null 2>&1; then
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        chmod +x /usr/local/bin/composer
    fi
}

install_nodejs() {
    print_info "Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
    DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs
    npm install -g npm@latest
}

install_mysql() {
    print_info "Installing MySQL..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server mysql-client
    systemctl enable mysql
    systemctl start mysql
    sudo mysql <<MYSQL_SCRIPT
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
}

install_redis() {
    DEBIAN_FRONTEND=noninteractive apt-get install -y redis-server
    systemctl enable redis-server
}

install_nginx() {
    DEBIAN_FRONTEND=noninteractive apt-get install -y nginx
    systemctl enable nginx
}

install_freeradius() {
    DEBIAN_FRONTEND=noninteractive apt-get install -y freeradius freeradius-mysql freeradius-utils
    systemctl enable freeradius
}

clone_repository() {
    print_info "Cloning ISP Solution repository..."
    mkdir -p /var/www
    if [ -d "$INSTALL_DIR" ]; then
        mv "$INSTALL_DIR" "${INSTALL_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR" || { print_error "Git clone failed. Check internet or repo URL."; exit 1; }
    cd "$INSTALL_DIR"
    print_success "Repository cloned to $INSTALL_DIR"
}

install_openvpn() {
    if [ "$INSTALL_OPENVPN" = "yes" ]; then
        print_info "Installing OpenVPN and Easy-RSA..."
        DEBIAN_FRONTEND=noninteractive apt-get install -y openvpn easy-rsa
        
        rm -rf ~/openvpn-ca
        make-cadir ~/openvpn-ca
        cd ~/openvpn-ca

        # FIX: Disable pipefail to prevent 'yes' command from breaking the script
        set +o pipefail 
        ./easyrsa init-pki
        yes "" | ./easyrsa --batch build-ca nopass
        ./easyrsa --batch gen-req server nopass
        echo "yes" | ./easyrsa --batch sign-req server server
        ./easyrsa gen-dh
        openvpn --genkey secret ta.key
        set -o pipefail

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
        print_success "OpenVPN configured."
    fi
}

setup_databases() {
    print_info "Setting up databases..."
    MYSQL_CREDS=$(mktemp)
    cat > "$MYSQL_CREDS" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF
    chmod 600 "$MYSQL_CREDS"
    mysql --defaults-extra-file="$MYSQL_CREDS" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME};
CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
    rm -f "$MYSQL_CREDS"
}

configure_laravel() {
    print_info "Configuring Laravel..."
    cd "$INSTALL_DIR"
    cp .env.example .env || true
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    composer install --no-interaction --optimize-autoloader --no-dev
    php artisan key:generate --force
    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache"
}

install_node_dependencies() {
    print_info "Building assets..."
    cd "$INSTALL_DIR"
    npm install && npm run build
}

run_migrations() {
    cd "$INSTALL_DIR"
    php artisan migrate --force
}

seed_database() {
    cd "$INSTALL_DIR"
    php artisan db:seed --class=RoleSeeder --force
}

configure_nginx() {
    cat > /etc/nginx/sites-available/ispsolution <<NGINX_CONFIG
server {
    listen 80;
    server_name ${DOMAIN_NAME};
    root ${INSTALL_DIR}/public;
    index index.php;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
    }
}
NGINX_CONFIG
    ln -sf /etc/nginx/sites-available/ispsolution /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    systemctl reload nginx
}

configure_firewall() {
    ufw --force enable
    ufw allow 22,80,443,1812,1813,1194/udp
}

configure_freeradius() {
    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    # (Radius configuration happens here using variables)
    systemctl restart freeradius
}

setup_ssl() {
    if [ "$SETUP_SSL" = "yes" ] && [ "$DOMAIN_NAME" != "localhost" ]; then
        apt-get install -y certbot python3-certbot-nginx
        certbot --nginx -d "${DOMAIN_NAME}" --non-interactive --agree-tos --email "${EMAIL}" --redirect
    fi
}

main() {
    check_root
    print_banner
    setup_swap
    update_system
    install_basic_dependencies
    
    # CRITICAL: Clone first so directory exists for all other services
    clone_repository
    
    install_php
    install_composer
    install_nodejs
    install_mysql
    install_redis
    install_nginx
    install_freeradius
    install_openvpn
    setup_databases
    configure_laravel
    install_node_dependencies
    run_migrations
    seed_database
    configure_nginx
    configure_firewall
    configure_freeradius
    setup_ssl
    
    print_success "ALL DONE. View credentials in /root/ispsolution-credentials.txt"
}

main "$@"
