<?php
session_start();
// Cek status WhatsApp
$wa_status = @file_get_contents('http://localhost:3000/status');
$wa_status = json_decode($wa_status, true);

include('../include/header.php');
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-whatsapp"></i> WhatsApp Gateway Status</h3>
            </div>
            <div class="card-body">
                <p>Status: <?= ($wa_status['connected'] ? '<span class="text-success">Connected</span>' : '<span class="text-danger">Disconnected</span>') ?></p>
                <p>Phone: <?= $wa_status['phone'] ?? '-' ?></p>
            </div>
        </div>
    </div>
</div>

<?php
include('../include/footer.php');
?> 