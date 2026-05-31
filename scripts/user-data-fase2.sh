#!/bin/bash
set -e
exec > /var/log/user-data.log 2>&1

# ── 1. Paquetes base ──────────────────────────────────────────
apt-get update -y
apt-get install -y apache2 php php-mysql php-mbstring mariadb-server

# ── 2. Apache ─────────────────────────────────────────────────
systemctl enable apache2
systemctl start apache2
rm -f /var/www/html/index.html

# ── 3. Base de datos local (POC) ──────────────────────────────
systemctl enable mariadb
systemctl start mariadb

mysql -u root << 'SQLEOF'
CREATE DATABASE IF NOT EXISTS tasksdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'taskuser'@'localhost' IDENTIFIED BY 'TaskCloud2026!';
GRANT ALL PRIVILEGES ON tasksdb.* TO 'taskuser'@'localhost';
FLUSH PRIVILEGES;
SQLEOF

# ── 4. Configuracion de DB ────────────────────────────────────
cat > /etc/db-config.ini << 'INIEOF'
host = "localhost"
port = "3306"
dbname = "tasksdb"
username = "taskuser"
password = "TaskCloud2026!"
INIEOF

chmod 640 /etc/db-config.ini
chown root:www-data /etc/db-config.ini
ln -s /etc/db-config.ini /var/www/html/db-config.ini

# ── 5. Permisos ───────────────────────────────────────────────
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "USER-DATA FASE 2 OK"