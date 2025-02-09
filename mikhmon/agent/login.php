<?php
session_start();
require_once('../include/config.php');

if (isset($_POST['login'])) {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $db->prepare("SELECT * FROM agents WHERE phone = ? AND password = ? AND status = 'active'");
    $password_hash = hash('sha256', $password);
    $stmt->bind_param("ss", $phone, $password_hash);
    $stmt->execute();
    $agent = $stmt->get_result()->fetch_assoc();
    
    if ($agent) {
        $_SESSION['agent_id'] = $agent['id'];
        $_SESSION['agent_name'] = $agent['name'];
        header("Location: dashboard.php");
    } else {
        $error = "Login gagal!";
    }
}
?>

<div class="row">
    <div class="col-md-4 offset-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Login Agen</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>No. WhatsApp</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
</div> 