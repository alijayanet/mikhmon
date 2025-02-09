<?php
session_start();
include('../include/header.php');

$db = new mysqli("localhost", "user", "password", "mikhmon");

// Handle deposit
if (isset($_POST['deposit'])) {
    $agent_id = $_POST['agent_id'];
    $amount = $_POST['amount'];
    
    $db->begin_transaction();
    try {
        // Update saldo
        $stmt = $db->prepare("UPDATE agents SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $agent_id);
        $stmt->execute();
        
        // Catat transaksi
        $stmt = $db->prepare("INSERT INTO transactions (agent_id, type, amount) VALUES (?, 'deposit', ?)");
        $stmt->bind_param("id", $agent_id, $amount);
        $stmt->execute();
        
        $db->commit();
        $success = "Deposit berhasil!";
    } catch (Exception $e) {
        $db->rollback();
        $error = "Gagal melakukan deposit";
    }
}

// Ambil daftar agen
$agents = $db->query("SELECT * FROM agents ORDER BY created_at DESC");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-users"></i> Manajemen Agen</h3>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No. WhatsApp</th>
                            <th>Saldo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($agent = $agents->fetch_assoc()): ?>
                        <tr>
                            <td><?= $agent['name'] ?></td>
                            <td><?= $agent['phone'] ?></td>
                            <td>Rp<?= number_format($agent['balance']) ?></td>
                            <td><?= $agent['status'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="showDepositModal(<?= $agent['id'] ?>)">Deposit</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Deposit -->
<div class="modal" id="depositModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Deposit Saldo</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="agent_id" id="agent_id">
                    <div class="form-group">
                        <label>Jumlah Deposit</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="deposit" class="btn btn-primary">Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDepositModal(agentId) {
    document.getElementById('agent_id').value = agentId;
    $('#depositModal').modal('show');
}
</script>

<?php
include('../include/footer.php');
?> 