#!/bin/bash

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Memulai instalasi WhatsApp Gateway untuk Mikhmon...${NC}"

# Update sistem
echo -e "\n${GREEN}[1/7] Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# Install Node.js
echo -e "\n${GREEN}[2/7] Installing Node.js...${NC}"
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
echo -e "\n${GREEN}[3/7] Installing MySQL...${NC}"
sudo apt install -y mysql-server

# Install required packages
echo -e "\n${GREEN}[4/7] Installing required packages...${NC}"
sudo apt install -y git pm2 nginx

# Setup project
echo -e "\n${GREEN}[5/7] Setting up project...${NC}"
cd /var/www/html/mikhmon/whatsapp
npm init -y
npm install @whiskeysockets/baileys@latest qrcode-terminal express node-fetch

# Tambahkan pembuatan direktori
mkdir -p auth_info_baileys uploads
chmod 775 auth_info_baileys uploads

# Tambahkan instalasi dotenv
npm install dotenv

# Tambahkan konfigurasi MySQL user
sudo mysql << EOF
CREATE USER 'mikhmon'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON mikhmon.* TO 'mikhmon'@'localhost';
FLUSH PRIVILEGES;
EOF

# Salin .env.example ke .env
cp .env.example .env

# Setup database
echo -e "\n${GREEN}[6/7] Setting up database...${NC}"
echo "Creating database and tables..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS mikhmon;"
sudo mysql mikhmon < database.sql

# Setup PM2
echo -e "\n${GREEN}[7/7] Setting up PM2...${NC}"
sudo pm2 start wa_bot.js
sudo pm2 save
sudo pm2 startup

# Create Nginx config
echo -e "\n${GREEN}Creating Nginx configuration...${NC}"
sudo tee /etc/nginx/sites-available/wa-gateway << EOF
server {
    listen 80;
    server_name your_domain.com;

    location / {
        proxy_pass http://localhost:2000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
    }
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/wa-gateway /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx

# Update port in wa_bot.js
sed -i 's/port = 3000/port = 2000/' wa_bot.js

echo -e "\n${GREEN}Instalasi selesai!${NC}"
echo -e "\nLangkah selanjutnya:"
echo "1. Edit file config.php dan sesuaikan kredensial database"
echo "2. Jalankan 'pm2 logs' untuk melihat QR code WhatsApp"
echo "3. Scan QR code dengan WhatsApp" 