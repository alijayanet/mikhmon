<?php
session_start();
include('../include/header.php');

$db = new mysqli("localhost", "user", "password", "mikhmon");

// Filter
$agent_id = $_GET['agent_id'] ?? '';
$type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Query dasar
$query = "SELECT t.*, a.name, a.phone 
          FROM transactions t 
          JOIN agents a ON t.agent_id = a.id 
          WHERE t.created_at BETWEEN ? AND ?";

// Tambah filter
$params = [$start_date, $end_date];
$types = "ss";

if ($agent_id) {
    $query .= " AND t.agent_id = ?";
    $params[] = $agent_id;
    $types .= "i";
}

if ($type) {
    $query .= " AND t.type = ?";
    $params[] = $type;
    $types .= "s";
}

$query .= " ORDER BY t.created_at DESC";

// Eksekusi query
$stmt = $db->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result();

// Ambil daftar agen untuk filter
$agents = $db->query("SELECT id, name FROM agents ORDER BY name");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-history"></i> Riwayat Transaksi</h3>
            </div>
            <div class="card-body">
                <!-- Form Filter -->
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="agent_id" class="form-control">
                                <option value="">Semua Agen</option>
                                <?php while ($agent = $agents->fetch_assoc()): ?>
                                    <option value="<?= $agent['id'] ?>" <?= $agent_id == $agent['id'] ? 'selected' : '' ?>>
                                        <?= $agent['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="deposit" <?= $type == 'deposit' ? 'selected' : '' ?>>Deposit</option>
                                <option value="purchase" <?= $type == 'purchase' ? 'selected' : '' ?>>Pembelian</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Agen</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Voucher</th>
                            <th>Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($trx = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                            <td><?= $trx['name'] ?> (<?= $trx['phone'] ?>)</td>
                            <td><?= $trx['type'] == 'deposit' ? 'Deposit' : 'Pembelian' ?></td>
                            <td>Rp<?= number_format($trx['amount']) ?></td>
                            <td><?= $trx['voucher_code'] ?? '-' ?></td>
                            <td><?= $trx['profile'] ?? '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include('../include/footer.php');
?> 