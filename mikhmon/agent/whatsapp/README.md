# WhatsApp Gateway untuk Mikhmon

Sistem WhatsApp Gateway terintegrasi dengan Mikhmon untuk penjualan voucher hotspot melalui WhatsApp.

## Persyaratan Sistem

- Ubuntu/Debian Server (Direkomendasikan Ubuntu 20.04 LTS)
- PHP 7.4 atau lebih baru
- Node.js 16.x
- MySQL/MariaDB
- Nginx/Apache
- PM2 (Process Manager)
- WhatsApp yang aktif untuk bot

## Instalasi

### 1. Persiapan Server
bash
Clone repository Mikhmon (jika belum)
cd /var/www/html
git clone https://github.com/your/mikhmon.git
Masuk ke direktori whatsapp
cd mikhmon/whatsapp
Buat file menjadi executable
chmod +x install.sh backup.sh
bash
./install.sh
bash
cp .env.example .env
bash
nano .env
env
Database
DB_HOST=localhost
DB_USER=mikhmon
DB_PASS=your_password
DB_NAME=mikhmon
WhatsApp Gateway
WA_PORT=2000
WA_ADMIN=628123456789 # Nomor WA Admin
Mikhmon
MIKHMON_URL=http://localhost/mikhmon
bash
Start bot
pm2 start wa_bot.js
Lihat log dan QR Code
pm2 logs
Scan QR Code dengan WhatsApp yang akan dijadikan bot
bash
Edit crontab
sudo crontab -e
Tambahkan baris berikut untuk backup setiap hari jam 00:00
0 0 /var/www/html/mikhmon/whatsapp/backup.sh
!help atau !menu - Lihat menu utama
!admin - Lihat panduan lengkap admin
!cekagen <nomer> - Cek info agen
!deposit <nomer> <jumlah> - Deposit saldo
!block <nomer> - Blokir agen
!unblock <nomer> - Buka blokir
!broadcast <pesan> - Kirim ke semua agen
!report hari - Laporan hari ini
!report bulan - Laporan bulan ini
!help atau !menu - Lihat menu
!saldo - Cek saldo
!harga - Cek daftar harga
!voucher <profile> <jumlah> - Beli voucher
!riwayat - Cek riwayat transaksi
!help - Lihat menu
!daftar <nama> - Daftar jadi agen
bash
Cek status PM2
pm2 status
Cek status MySQL
systemctl status mysql
Cek status Nginx
systemctl status nginx
bash
Log WhatsApp bot
pm2 logs wa_bot
Log sistem
tail -f /var/log/syslog
Log WhatsApp Gateway
tail -f logs/wa_gateway.log
bash
Restart bot
pm2 restart wa_bot
Restart MySQL
sudo systemctl restart mysql
Restart Nginx
sudo systemctl restart nginx
Apakah ada bagian dari README yang perlu ditambahkan atau diperjelas?