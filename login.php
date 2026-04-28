<?php 
include("db.php");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($res);

    if($user && password_verify($password,$user['password'])){
        $_SESSION['user_id'] = $user['user_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body class="index-page">

<div class="index-wrapper">

    <div class="card">
        <h3 class="card-title">Login</h3>

        <?php if(isset($error)){ ?>
            <p class="error-text">
                <?php echo $error; ?>
            </p>
        <?php } ?>

        <form method="POST" class="login-form">
            <input type="email" name="email" placeholder="Email" required class="input-field">
            <input type="password" name="password" placeholder="Password" required class="input-field">
            <button name="login" class="btn-primary">Login</button>
        </form>

        <p class="card-subtitle">
            Don't have an account?
        </p>

        <a href="register.php" style="text-decoration:none;">
            <button class="btn-secondary">Register</button>
        </a>

    </div>

</div>

</body>
</html>
