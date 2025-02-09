<?php
require_once('../include/routeros_api.class.php');

class WhatsAppGateway {
    private $router;
    private $db;
    private $allowed_numbers = [
        '628123456789@s.whatsapp.net'
    ];
    
    public function __construct($router) {
        $this->router = $router;
        $this->db = new mysqli("localhost", "user", "password", "mikhmon");
    }
    
    public function handleMessage($message, $sender) {
        // Cek perintah bantuan
        if (strpos($message, '!help') === 0 || $message == '!menu') {
            return $this->showHelp($sender);
        }
        
        // Cek apakah admin
        if ($sender == $this->admin_number) {
            if (strpos($message, '!admin') === 0) {
                return $this->showAdminHelp();
            }
            return $this->handleAdminCommands($message);
        }
        
        // Cek apakah pengirim adalah agen
        $agent = $this->getAgent($sender);
        
        if (strpos($message, '!daftar') === 0) {
            return $this->registerAgent($message, $sender);
        }
        
        if (!$agent) {
            return [
                'success' => false,
                'message' => "Maaf, Anda belum terdaftar sebagai agen.\nKetik !daftar <nama> untuk mendaftar."
            ];
        }
        
        // Handle perintah-perintah agen
        if (strpos($message, '!voucher') === 0) {
            return $this->handleVoucherPurchase($message, $agent);
        } else if (strpos($message, '!saldo') === 0) {
            return $this->checkBalance($agent);
        }
    }
    
    private function registerAgent($message, $phone) {
        $params = explode(' ', $message, 2);
        $name = $params[1] ?? '';
        
        if (empty($name)) {
            return [
                'success' => false,
                'message' => "Format salah.\nKetik: !daftar <nama>"
            ];
        }
        
        $stmt = $this->db->prepare("INSERT INTO agents (phone, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $phone, $name);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => "Pendaftaran berhasil!\nSelamat datang $name\nSaldo: Rp 0\n\nSilahkan hubungi admin untuk deposit."
            ];
        }
        
        return [
            'success' => false,
            'message' => "Gagal mendaftar. Silahkan coba lagi."
        ];
    }
    
    private function handleVoucherPurchase($message, $agent) {
        // Parse pesan
        $params = explode(' ', $message);
        $profile = $params[1] ?? '';
        $qty = intval($params[2] ?? 1);
        
        // Cek harga profil
        $prices = [
            '2jam' => 3000,
            '5jam' => 5000,
            '1hari' => 10000
        ];
        
        if (!isset($prices[$profile])) {
            return [
                'success' => false,
                'message' => "Profil tidak valid.\nProfil yang tersedia:\n2jam: Rp3.000\n5jam: Rp5.000\n1hari: Rp10.000"
            ];
        }
        
        $total = $prices[$profile] * $qty;
        
        // Cek saldo
        if ($agent['balance'] < $total) {
            return [
                'success' => false,
                'message' => "Saldo tidak cukup.\nSaldo Anda: Rp" . number_format($agent['balance'])
            ];
        }
        
        // Generate voucher
        $voucher = $this->generateVoucher($profile);
        
        // Update saldo
        $this->db->begin_transaction();
        try {
            // Kurangi saldo
            $stmt = $this->db->prepare("UPDATE agents SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $total, $agent['id']);
            $stmt->execute();
            
            // Catat transaksi
            $stmt = $this->db->prepare("INSERT INTO transactions (agent_id, type, amount, voucher_code, profile) VALUES (?, 'purchase', ?, ?, ?)");
            $stmt->bind_param("idss", $agent['id'], $total, $voucher['username'], $profile);
            $stmt->execute();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Pembelian berhasil!\n\nVoucher: " . $voucher['username'] . "\nPassword: " . $voucher['password'] . "\nProfile: $profile\n\nSaldo: Rp" . number_format($agent['balance'] - $total)
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => "Gagal membuat voucher. Silahkan coba lagi."
            ];
        }
    }
    
    private function checkBalance($agent) {
        return [
            'success' => true,
            'message' => "Saldo Anda: Rp" . number_format($agent['balance'])
        ];
    }
    
    private function getAgent($phone) {
        $stmt = $this->db->prepare("SELECT * FROM agents WHERE phone = ? AND status = 'active'");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    private function generateVoucher($profile) {
        // Generate username dan password
        $username = 'user' . rand(1000,9999);
        $password = rand(1000,9999);
        
        // Add user di Mikrotik
        $this->router->comm("/ip/hotspot/user/add", array(
            "name" => $username,
            "password" => $password,
            "profile" => $profile,
            "comment" => "WA-AGENT"
        ));
        
        return [
            'username' => $username,
            'password' => $password
        ];
    }
    
    private function sendWhatsAppMessage($to, $message) {
        // Implementasi pengiriman pesan WA
        // Menggunakan WhatsApp API yang dipilih
    }
    
    private function log($message, $type = 'info') {
        $log_file = __DIR__ . '/logs/wa_gateway.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$date][$type] $message\n", FILE_APPEND);
    }
    
    private function showHelp($sender) {
        if ($sender == $this->admin_number) {
            return [
                'success' => true,
                'message' => "🔰 *MENU ADMIN*\n\n" .
                    "!admin - Menu admin lengkap\n" .
                    "!cekagen <nomer> - Cek info agen\n" .
                    "!deposit <nomer> <jumlah> - Deposit saldo agen\n" .
                    "!block <nomer> - Blokir agen\n" .
                    "!unblock <nomer> - Buka blokir agen\n" .
                    "!broadcast <pesan> - Kirim pesan ke semua agen\n\n" .
                    "Contoh:\n" .
                    "!deposit 628123456789 100000\n" .
                    "!cekagen 628123456789"
            ];
        }
        
        // Cek apakah pengirim adalah agen
        $agent = $this->getAgent($sender);
        if ($agent) {
            return [
                'success' => true,
                'message' => "🔰 *MENU AGEN*\n\n" .
                    "!saldo - Cek saldo\n" .
                    "!harga - Cek daftar harga\n" .
                    "!voucher <profile> <jumlah> - Beli voucher\n" .
                    "!riwayat - Cek riwayat transaksi\n\n" .
                    "Contoh:\n" .
                    "!voucher 2jam 1\n" .
                    "!voucher 5jam 2\n" .
                    "!voucher 1hari 5"
            ];
        }
        
        return [
            'success' => true,
            'message' => "🔰 *MENU UMUM*\n\n" .
                "!daftar <nama> - Daftar sebagai agen\n" .
                "!help - Tampilkan menu ini\n\n" .
                "Contoh:\n" .
                "!daftar John Doe"
        ];
    }
    
    private function handleAdminCommands($message) {
        if (strpos($message, '!cekagen') === 0) {
            $params = explode(' ', $message);
            $phone = $params[1] ?? '';
            
            $agent = $this->getAgent($phone);
            if ($agent) {
                return [
                    'success' => true,
                    'message' => "*INFO AGEN*\n\n" .
                        "Nama: {$agent['name']}\n" .
                        "No. WA: {$agent['phone']}\n" .
                        "Saldo: Rp" . number_format($agent['balance']) . "\n" .
                        "Status: {$agent['status']}\n" .
                        "Terdaftar: " . date('d/m/Y', strtotime($agent['created_at']))
                ];
            }
            return ['success' => false, 'message' => "Agen tidak ditemukan"];
        }
        
        if (strpos($message, '!deposit') === 0) {
            $params = explode(' ', $message);
            $phone = $params[1] ?? '';
            $amount = intval($params[2] ?? 0);
            
            if (empty($phone) || $amount <= 0) {
                return [
                    'success' => false,
                    'message' => "Format salah!\nContoh: !deposit 628123456789 100000"
                ];
            }
            
            return $this->depositAgent($phone, $amount);
        }
        
        // ... other admin commands ...
    }
    
    private function showAdminHelp() {
        return [
            'success' => true,
            'message' => "📝 *PANDUAN ADMIN*\n\n" .
                "*A. Manajemen Agen*\n" .
                "!cekagen <nomer> - Cek info agen\n" .
                "!deposit <nomer> <jumlah> - Deposit saldo\n" .
                "!block <nomer> - Blokir agen\n" .
                "!unblock <nomer> - Buka blokir\n\n" .
                "*B. Broadcast*\n" .
                "!broadcast <pesan> - Kirim ke semua agen\n" .
                "!info <pesan> - Kirim info penting\n\n" .
                "*C. Laporan*\n" .
                "!report hari - Laporan hari ini\n" .
                "!report bulan - Laporan bulan ini\n\n" .
                "*D. Pengaturan*\n" .
                "!setmin <jumlah> - Set minimal deposit\n" .
                "!setmax <jumlah> - Set maksimal voucher\n\n" .
                "Contoh:\n" .
                "!deposit 628123456789 100000\n" .
                "!broadcast Sistem maintenance pukul 23:00\n" .
                "!report hari"
        ];
    }
    
    private function checkPrice() {
        $prices = $this->db->query("SELECT * FROM voucher_prices ORDER BY agent_price ASC");
        $message = "*DAFTAR HARGA VOUCHER*\n\n";
        
        while ($price = $prices->fetch_assoc()) {
            $message .= "{$price['profile']}\n";
            $message .= "Harga: Rp" . number_format($price['agent_price']) . "\n\n";
        }
        
        $message .= "Cara Pembelian:\n";
        $message .= "!voucher <profile> <jumlah>\n\n";
        $message .= "Contoh:\n";
        $message .= "!voucher 2jam 1";
        
        return ['success' => true, 'message' => $message];
    }
} 