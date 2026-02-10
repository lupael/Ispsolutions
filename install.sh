#!/bin/bash

################################################################################
# ISP Solution - Optimized Installation Script (Enhanced)
# Target OS: Ubuntu 22.04 / 24.04 LTS
################################################################################

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration (Editable)
DOMAIN_NAME=${DOMAIN_NAME:-"radius.ispbills.com"}
DB_NAME=${DB_NAME:-"ispsolution"}
DB_USER=${DB_USER:-"ispsolution"}
DB_PASSWORD=${DB_PASSWORD:-"$(openssl rand -base64 12)"}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-"$(openssl rand -base64 12)"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD:-"$(openssl rand -base64 12)"}
INSTALL_DIR="/var/www/ispsolution"
INSTALL_OPENVPN="yes"
SETUP_SSL="no"
EMAIL=${EMAIL:-"admin@$DOMAIN_NAME"}

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

# --- Step 1: Core Dependencies ---
install_essentials() {
    print_status "Installing system essentials..."
    apt-get update -y
    apt-get install -y software-properties-common curl wget git unzip zip gnupg2 \
        ca-certificates lsb-release apt-transport-https build-essential openssl snmp \
        ufw bc fail2ban
    print_done "Essentials installed."
}

# --- Step 2: Swap Memory ---
setup_swap() {
    if swapon --show | grep -q "/swapfile"; then
        print_status "Swap exists, skipping."
    else
        print_status "Setting up 2GB swap..."
        fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
        chmod 600 /swapfile
        mkswap /swapfile
        swapon /swapfile
        echo '/swapfile none swap sw 0 0' >> /etc/fstab
        print_done "Swap configured."
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

# --- Step 4: Secure MySQL & Create Databases ---
configure_mysql() {
    print_status "Securing MySQL and creating databases..."

    # Secure Root
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASSWORD}';"

    SECURE_CONF=$(mktemp)
    cat > "$SECURE_CONF" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF

    # Create App Database & User
    mysql --defaults-extra-file="$SECURE_CONF" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
    mysql --defaults-extra-file="$SECURE_CONF" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql --defaults-extra-file="$SECURE_CONF" -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"

    # Create Radius Database & User
    mysql --defaults-extra-file="$SECURE_CONF" -e "CREATE DATABASE IF NOT EXISTS ${RADIUS_DB_NAME};"
    mysql --defaults-extra-file="$SECURE_CONF" -e "CREATE USER IF NOT EXISTS '${RADIUS_DB_USER}'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASSWORD}';"
    mysql --defaults-extra-file="$SECURE_CONF" -e "GRANT ALL PRIVILEGES ON ${RADIUS_DB_NAME}.* TO '${RADIUS_DB_USER}'@'localhost';"

    mysql --defaults-extra-file="$SECURE_CONF" -e "DELETE FROM mysql.user WHERE User='';"
    mysql --defaults-extra-file="$SECURE_CONF" -e "DROP DATABASE IF EXISTS test;"
    mysql --defaults-extra-file="$SECURE_CONF" -e "FLUSH PRIVILEGES;"
    rm "$SECURE_CONF"
}

# --- Step 5: FreeRADIUS ---
configure_freeradius() {
    print_status "Configuring FreeRADIUS..."
    apt-get install -y freeradius freeradius-mysql freeradius-utils

    ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
    ln -sf /etc/freeradius/3.0/mods-available/sqlcounter /etc/freeradius/3.0/mods-enabled/

    sed -i 's/driver = "rlm_sql_null"/driver = "rlm_sql_mysql"/' /etc/freeradius/3.0/mods-available/sql
    sed -i "s/login = \"radius\"/login = \"${RADIUS_DB_USER}\"/" /etc/freeradius/3.0/mods-available/sql
    sed -i "s/password = \"radpass\"/password = \"${RADIUS_DB_PASSWORD}\"/" /etc/freeradius/3.0/mods-available/sql

    # A more robust way using a heredoc to overwrite the sql config
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

    systemctl restart freeradius
}

# --- Step 6: OpenVPN ---
configure_openvpn() {
    print_status "Setting up OpenVPN..."
    apt-get install -y openvpn easy-rsa
    # Enable IP Forwarding
    echo 'net.ipv4.ip_forward=1' >> /etc/sysctl.conf
    sysctl -p

    mkdir -p ~/openvpn-ca
    cp -r /usr/share/easy-rsa/* ~/openvpn-ca/
    cd ~/openvpn-ca

    ./easyrsa init-pki
    ./easyrsa build-ca nopass batch
    ./easyrsa gen-dh
    ./easyrsa build-server-full server nopass batch
    openvpn --genkey --secret ta.key

    cp pki/ca.crt pki/issued/server.crt pki/private/server.key pki/dh.pem ta.key /etc/openvpn/

    cat > /etc/openvpn/server.conf <<EOF
port 1194
proto udp
dev tun
ca ca.crt
cert server.crt
key server.key
dh dh.pem
tls-auth ta.key 0
server 10.8.0.0 255.255.255.0
ifconfig-pool-persist ipp.txt
push "redirect-gateway def1 bypass-dhcp"
push "dhcp-option DNS 8.8.8.8"
push "dhcp-option DNS 8.8.4.4"
keepalive 10 120
cipher AES-256-GCM
persist-key
persist-tun
user nobody
group nogroup
status openvpn-status.log
verb 3
EOF
    systemctl enable --now openvpn@server
}

# --- Step 7: Laravel App Setup & Auto .env Edit ---
setup_laravel() {
    print_status "Cloning and configuring Laravel..."
    mkdir -p "$INSTALL_DIR"
    git clone https://github.com/i4edubd/ispsolution.git "$INSTALL_DIR"
    cd "$INSTALL_DIR"

    cp .env.example .env

    # Auto Edit .env
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN_NAME}|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

    # RADIUS secondary connection (common in ISP Laravel apps)
    if grep -q "RADIUS_DB_PASSWORD" .env; then
        sed -i "s|RADIUS_DB_DATABASE=.*|RADIUS_DB_DATABASE=${RADIUS_DB_NAME}|" .env
        sed -i "s|RADIUS_DB_USERNAME=.*|RADIUS_DB_USERNAME=${RADIUS_DB_USER}|" .env
        sed -i "s|RADIUS_DB_PASSWORD=.*|RADIUS_DB_PASSWORD=${RADIUS_DB_PASSWORD}|" .env
    fi

    composer install --no-dev --optimize-autoloader
    npm install
    npm run build

    php artisan key:generate
    php artisan migrate --seed --force
    php artisan storage:link

    chown -R www-data:www-data "$INSTALL_DIR"
    chmod -R 775 storage bootstrap/cache
}

# --- Step 8: Nginx Configuration ---
setup_nginx() {
    print_status "Configuring Nginx..."
    cat > /etc/nginx/sites-available/"$DOMAIN_NAME" <<EOF
server {
    listen 80;
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
    }
}
EOF
    ln -sf /etc/nginx/sites-available/"$DOMAIN_NAME" /etc/nginx/sites-enabled/
    unlink /etc/nginx/sites-enabled/default
    systemctl restart nginx
}

# --- Step 8: Automation Script ---
setup_automation() {
    cat <<EOF > /usr/local/bin/create-tenant
#!/bin/bash
SUBDOMAIN=\$1
BASE_DOMAIN="${DOMAIN_NAME}"
if [ -z "\$SUBDOMAIN" ]; then echo "Usage: create-tenant <name>"; exit 1; fi
FULL_DOMAIN="\$SUBDOMAIN.\$BASE_DOMAIN"
echo "Creating config for \$FULL_DOMAIN"
EOF
    chmod +x /usr/local/bin/create-tenant
}

# --- Step 9: Save Credentials ---
save_credentials() {
    print_status "Saving credentials to /root/ispsolution-credentials.txt"
    cat <<EOF > /root/ispsolution-credentials.txt
#################################################
ISP Solution Credentials - $(date)
#################################################
MySQL Root Password: ${DB_ROOT_PASSWORD}

App Database Name:   ${DB_NAME}
App DB User:         ${DB_USER}
App DB Password:     ${DB_PASSWORD}

Radius Database Name: ${RADIUS_DB_NAME}
Radius DB User:       ${RADIUS_DB_USER}
Radius DB Password:   ${RADIUS_DB_PASSWORD}

Domain:              ${DOMAIN_NAME}
Admin Email:         ${EMAIL}
#################################################
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
    configure_openvpn
    setup_nginx
    setup_laravel
    setup_automation
    save_credentials

    # Firewall
    ufw allow 22,23,80,161/udp,162/udp,163/udp,443,8000,2222,8728,8729,8787,1812,1813,1194/udp
    ufw --force enable

    print_done "Installation Complete!"
    echo "Check /root/ispsolution-credentials.txt for all passwords."
}

main "$@"
