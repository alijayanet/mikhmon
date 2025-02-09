<?php
session_start();
include('../include/header.php');

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_POST['save_price'])) {
    $profile = $_POST['profile'];
    $buy_price = $_POST['buy_price'];
    $sell_price = $_POST['sell_price'];
    $agent_price = $_POST['agent_price'];
    
    $db->query("INSERT INTO voucher_prices (profile, buy_price, sell_price, agent_price) 
                VALUES ('$profile', $buy_price, $sell_price, $agent_price)
                ON DUPLICATE KEY UPDATE 
                buy_price = $buy_price,
                sell_price = $sell_price,
                agent_price = $agent_price");
}

$prices = $db->query("SELECT * FROM voucher_prices");

// Tambahkan unique key untuk profile
$db->query("ALTER TABLE voucher_prices ADD UNIQUE KEY (profile)");
?>

<div class="card">
    <div class="card-header">
        <h3>Pengaturan Harga Voucher</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <table class="table">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Harga Agen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($price = $prices->fetch_assoc()): ?>
                    <tr>
                        <td><?= $price['profile'] ?></td>
                        <td>
                            <input type="hidden" name="prices[<?= $price['id'] ?>][profile]" value="<?= $price['profile'] ?>">
                            <input type="number" name="prices[<?= $price['id'] ?>][buy_price]" value="<?= $price['buy_price'] ?>" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="prices[<?= $price['id'] ?>][sell_price]" value="<?= $price['sell_price'] ?>" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="prices[<?= $price['id'] ?>][agent_price]" value="<?= $price['agent_price'] ?>" class="form-control">
                        </td>
                        <td>
                            <button type="submit" name="save_price" class="btn btn-primary">Simpan</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    </div>
</div> 