#!/bin/bash

################################################################################
# ISP Solution - Uninstallation Script
# This script will remove the application and all its components.
# WARNING: This is a destructive operation. Use with caution.
################################################################################

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# --- Utility Functions ---
print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARN]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run as root (sudo bash uninstall.sh)"
        exit 1
    fi
}

# --- Configuration (Should match install script) ---
DOMAIN_NAME=${DOMAIN_NAME:-"radius.ispbills.com"}
DB_NAME=${DB_NAME:-"ispsolution"}
DB_USER=${DB_USER:-"ispsolution"}
RADIUS_DB_NAME=${RADIUS_DB_NAME:-"radius"}
RADIUS_DB_USER=${RADIUS_DB_USER:-"radius"}
INSTALL_DIR="/var/www/ispsolution"

# --- Uninstallation Functions ---

stop_services() {
    print_status "Stopping services..."
    systemctl stop nginx || true
    systemctl stop freeradius || true
    systemctl stop openvpn@server || true
    systemctl disable openvpn@server || true
    systemctl stop php8.2-fpm || true
    systemctl stop redis-server || true
}

remove_nginx_config() {
    print_status "Removing Nginx configuration..."
    rm -f "/etc/nginx/sites-available/${DOMAIN_NAME}"
    rm -f "/etc/nginx/sites-enabled/${DOMAIN_NAME}"
    # Re-enable default site if it exists
    if [ -f "/etc/nginx/sites-available/default" ]; then
        ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
    fi
    systemctl restart nginx || true
}

remove_laravel_app() {
    print_status "Removing Laravel application files..."
    if [ -d "$INSTALL_DIR" ]; then
        rm -rf "$INSTALL_DIR"
    else
        print_warning "Install directory ${INSTALL_DIR} not found."
    fi
}

remove_mysql_data() {
    print_status "Removing MySQL databases and users..."
    print_warning "You will be prompted for the MySQL root password if it's set."

    # Check if mysql client is available
    if ! command -v mysql &> /dev/null; then
        print_warning "MySQL client not found. Skipping database removal."
        return
    fi

    mysql -u root -p <<EOF
DROP DATABASE IF EXISTS ${DB_NAME};
DROP DATABASE IF EXISTS ${RADIUS_DB_NAME};
DROP USER IF EXISTS '${DB_USER}'@'localhost';
DROP USER IF EXISTS '${RADIUS_DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    echo "MySQL cleanup attempted."
}

remove_packages() {
    print_status "Uninstalling packages (FreeRADIUS, OpenVPN, PHP, etc.)..."

    # Purge to remove config files
    apt-get purge -y freeradius freeradius-mysql freeradius-utils
    apt-get purge -y openvpn easy-rsa
    apt-get purge -y 'php8.2*'
    apt-get purge -y nginx redis-server

    # Remove PPA
    add-apt-repository --remove ppa:ondrej/php -y

    # Clean up
    apt-get autoremove -y
    apt-get autoclean -y
}

remove_swap() {
    print_status "Removing swap file..."
    if [ -f /swapfile ]; then
        swapoff /swapfile
        rm /swapfile
        sed -i '\|/swapfile|d' /etc/fstab
    else
        print_warning "Swap file not found."
    fi
}

remove_firewall_rules() {
    print_status "Removing firewall rules..."
    ufw delete allow 22,23,80,161/udp,162/udp,163/udp,443,8000,2222,8728,8729,8787,1812,1813,1194/udp || print_warning "UFW rule might not exist."
}

cleanup_files() {
    print_status "Cleaning up remaining files..."
    rm -f /root/ispsolution-credentials.txt
    rm -f /usr/local/bin/create-tenant
    rm -rf ~/openvpn-ca
    rm -f /etc/openvpn/server.conf
    rm -f /etc/openvpn/*.crt /etc/openvpn/*.key /etc/openvpn/*.pem
}

# --- Main Execution ---
main() {
    check_root

    echo -e "${RED}!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo -e "!!! WARNING: This will permanently delete the ISP Solution   !!!"
    echo -e "!!! application, its configuration, and its databases.       !!!"
    echo -e "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!${NC}"
    read -p "Are you sure you want to continue? [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Uninstallation cancelled."
        exit 1
    fi

    stop_services
    remove_firewall_rules
    remove_nginx_config
    remove_laravel_app

    # Ask before destructive package/db removal
    read -p "Do you want to remove MySQL databases and users? [y/N] " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        remove_mysql_data
    fi

    read -p "Do you want to uninstall all related packages (PHP, Nginx, Redis, etc.)? [y/N] " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        remove_packages
        remove_swap
    fi

    cleanup_files

    echo -e "${GREEN}Uninstallation complete.${NC}"
    echo "Some packages like 'git', 'curl', 'ufw' were not removed as they are common system utilities."
    echo "A reboot is recommended to ensure all services are stopped."
}

main "$@"
