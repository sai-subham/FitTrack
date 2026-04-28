<?php 
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$query = mysqli_query($conn, "SELECT name, age, weight, height FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($query);

if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $age = intval($_POST['age']);
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);

    if($age > 0 && $weight > 0 && $height > 0 && !empty($name)){
        mysqli_query($conn, 
            "UPDATE users SET name='$name', age='$age', weight='$weight', height='$height' WHERE user_id='$user_id'"
        );
        // Force refresh to show updated data
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Please fill in all fields with valid values.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<div class="centered-action-layout">
    <div class="card centered-action-card">
        <h3 class="card-title">Edit Profile Data</h3>

        <?php if(isset($error)){ ?>
            <div class="error-text">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <div style="max-width: 450px; margin: 0 auto;">
            <form method="POST">
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="Full Name" required>
                
                <input type="number" name="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" placeholder="Age (years)" required>

                <input type="number" step="0.1" name="weight" value="<?php echo htmlspecialchars($user['weight'] ?? ''); ?>" placeholder="Weight (kg)" required>
                
                <input type="number" step="0.1" name="height" value="<?php echo htmlspecialchars($user['height'] ?? ''); ?>" placeholder="Height (cm)" required>

                <button name="update_profile" class="btn-primary" style="width: 100%;">Update Profile</button>
            </form>
        </div>
        
        <a href="dashboard.php" class="back-link">Cancel and Return</a>
    </div>
</div>

</body>
</html>
