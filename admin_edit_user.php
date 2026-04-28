<?php 
include("db.php");

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("Location: admin_login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit();
}

$target_user_id = (int)$_GET['id'];

// Fetch user data
$query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$target_user_id'");
$user = mysqli_fetch_assoc($query);

if(!$user){
    header("Location: admin_dashboard.php");
    exit();
}

if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);

    if($age > 0 && $weight > 0 && $height > 0 && !empty($name)){
        mysqli_query($conn, 
            "UPDATE users SET name='$name', age='$age', weight='$weight', height='$height' WHERE user_id='$target_user_id'"
        );
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Please fill in all fields with valid values.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css?v=6">
</head>
<body class="dashboard-page">

<div class="centered-action-layout">
    <div class="card centered-action-card">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 class="card-title" style="margin: 0;">Edit User: <?php echo htmlspecialchars($user['name']); ?></h3>
        </div>

                <?php if(isset($error)){ ?>
                    <div class="error-text">
                        <?php echo $error; ?>
                    </div>
                <?php } ?>

                <div style="max-width: 450px; margin: 0 auto; text-align: left;">
                    <form method="POST">
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Age (years)</label>
                        <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>

                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" value="<?php echo htmlspecialchars($user['weight']); ?>" required>
                        
                        <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Height (cm)</label>
                        <input type="number" step="0.1" name="height" value="<?php echo htmlspecialchars($user['height']); ?>" required>

                        <button name="update_profile" class="btn-primary" style="margin-top: 15px; width: 100%;">Save Changes</button>
                    </form>
                </div>
                
                <a href="admin_dashboard.php" class="back-link">Cancel</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
