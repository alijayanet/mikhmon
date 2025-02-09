<?php
session_start();
require_once('../include/header.php');
require_once('../include/config.php');

if (!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data agen
$stmt = $db->prepare("SELECT * FROM agents WHERE id = ?");
$stmt->bind_param("i", $_SESSION['agent_id']);
$stmt->execute();
$agent = $stmt->get_result()->fetch_assoc();

// Ambil daftar harga
$prices = $db->query("SELECT * FROM voucher_prices");

// Handle pembelian voucher
if (isset($_POST['buy_voucher'])) {
    $profile = $_POST['profile'];
    $qty = $_POST['qty'];
    
    // Proses pembelian seperti di WA Gateway
    // Tambah notifikasi WA
    
    $wa_message = "Pembelian Voucher Berhasil!\n\n";
    $wa_message .= "Profile: $profile\n";
    $wa_message .= "Username: $username\n";
    $wa_message .= "Password: $password\n";
    $wa_message .= "Saldo: Rp" . number_format($agent['balance']);
    
    // Kirim notifikasi WA
    sendWANotification($agent['phone'], $wa_message);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Info Agen</h3>
            </div>
            <div class="card-body">
                <p>Nama: <?= $agent['name'] ?></p>
                <p>Saldo: Rp<?= number_format($agent['balance']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Beli Voucher</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Profile</label>
                        <select name="profile" class="form-control" required>
                            <?php while ($price = $prices->fetch_assoc()): ?>
                            <option value="<?= $price['profile'] ?>">
                                <?= $price['profile'] ?> - Rp<?= number_format($price['agent_price']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="qty" class="form-control" value="1" min="1" required>
                    </div>
                    <button type="submit" name="buy_voucher" class="btn btn-primary">Beli Voucher</button>
                </form>
            </div>
        </div>
        
        <!-- Riwayat Transaksi -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Riwayat Transaksi</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Profile</th>
                            <th>Voucher</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $transactions = $db->query("SELECT * FROM transactions 
                            WHERE agent_id = {$agent['id']} 
                            ORDER BY created_at DESC LIMIT 10");
                        
                        while ($trx = $transactions->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                            <td><?= $trx['profile'] ?></td>
                            <td><?= $trx['voucher_code'] ?></td>
                            <td>Rp<?= number_format($trx['amount']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 