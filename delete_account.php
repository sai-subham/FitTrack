<?php
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['delete'])){
    
    mysqli_query($conn,"DELETE FROM users WHERE user_id='$user_id'");
    
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Account</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="centered-action-layout">
    <div class="card centered-action-card">
        <h3 class="card-title">Delete Account</h3>

        <form method="POST">
            <p style="margin-bottom:25px; color: #FFFFFF; font-weight: 500; line-height: 1.6;">
                Are you sure you want to permanently delete your account?
                <br><span style="color: #FF3B30; font-size: 0.9rem; font-weight: 700;">This action cannot be undone.</span>
            </p>

            <button name="delete" class="btn-primary" style="background: #FF3B30; color: white;">
                Yes, Delete My Account
            </button>
        </form>
        
        <a href="dashboard.php" class="back-link">Cancel and return</a>
    </div>
</div>

</body>
</html>