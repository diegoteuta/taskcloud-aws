#!/bin/bash
exec > /var/log/user-data.log 2>&1

# ── 1. Instalar AWS CLI ───────────────────────────────────────
apt-get install -y awscli

# ── 2. Leer credenciales desde Secrets Manager ────────────────
SECRET=$(aws secretsmanager get-secret-value \
  --secret-id taskcloud/db \
  --region us-east-1 \
  --query SecretString \
  --output text)

DB_HOST=$(echo $SECRET | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['host'])")
DB_USER=$(echo $SECRET | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['username'])")
DB_PASS=$(echo $SECRET | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['password'])")
DB_NAME=$(echo $SECRET | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('dbname','tasksdb'))")

# ── 3. Escribir db-config.ini ─────────────────────────────────
cat > /etc/db-config.ini << INIEOF
host = "${DB_HOST}"
port = "3306"
dbname = "${DB_NAME}"
username = "${DB_USER}"
password = "${DB_PASS}"
INIEOF

chown root:www-data /etc/db-config.ini
chmod 640 /etc/db-config.ini

# ── 4. Symlink para la app ────────────────────────────────────
if [ ! -L /var/www/html/db-config.ini ]; then
  ln -s /etc/db-config.ini /var/www/html/db-config.ini
  chown -h www-data:www-data /var/www/html/db-config.ini
fi

# ── 5. Reiniciar Apache ───────────────────────────────────────
systemctl restart apache2

echo "USER-DATA FASE 4 OK"