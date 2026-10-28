#!/usr/bin/env bash
set -e

echo "🚀 Iniciando MySQL / MariaDB en GitHub Codespaces..."
sudo service mariadb start || sudo service mysql start

echo "🔧 Configurando acceso root y base de datos impulso_academico..."
sudo mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD(''); ALTER USER 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD(''); GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;" || true

echo "🌐 Iniciando servidor web PHP en el puerto 8000..."
nohup php -S 0.0.0.0:8000 > /tmp/php-server.log 2>&1 &
echo "✅ Impulso Académico corriendo en la nube en el puerto 8000."
