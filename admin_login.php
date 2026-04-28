<?php
include("db.php");

if(isset($_POST['login'])){
    $admin_id = trim($_POST['admin_id']);
    $admin_pass = trim($_POST['admin_pass']);

    if($admin_id === 'admin' && $admin_pass === 'admin123'){
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body class="index-page">

<div class="index-wrapper">

    <div class="card">
        <h3 class="card-title">Admin Login</h3>

        <?php if(isset($error)){ ?>
            <p class="error-text">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php } ?>

        <form method="POST" class="login-form">
            <input type="text" name="admin_id" placeholder="Admin ID" required class="input-field">
            <input type="password" name="admin_pass" placeholder="Password" required class="input-field">
            <button name="login" class="btn-primary">Login</button>
        </form>
        
        <br>
        <a href="index.php" style="text-decoration:none;">
            <button class="btn-secondary">Back to Home</button>
        </a>

    </div>

</div>

</body>
</html>
