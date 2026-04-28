<?php 
include("db.php");

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $error = "Email already registered!";
    } else {

        mysqli_query($conn,"INSERT INTO users
        (name,age,email,password,weight,height)
        VALUES('$name','$age','$email','$password','$weight','$height')");

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body class="index-page">

<div class="index-wrapper">

    <div class="card">
        <h3 class="card-title">Create Account</h3>

        <?php if(isset($error)){ ?>
            <p class="error-text">
                <?php echo $error; ?>
            </p>
        <?php } ?>

        <form method="POST" class="register-form">
            <input type="text" name="name" placeholder="Full Name" required class="input-field">

            <input type="number" name="age" placeholder="Age" required class="input-field">

            <input type="email" name="email" placeholder="Email" required class="input-field">

            <input type="number" step="0.1" name="weight" placeholder="Weight (kg)" required class="input-field">

            <input type="number" step="0.1" name="height" placeholder="Height (cm)" required class="input-field">

            <input type="password" name="password" placeholder="Password" required class="input-field">

            <button name="register" class="btn-primary">Register</button>
        </form>

        <p class="card-subtitle">
            Already have an account?
        </p>

        <a href="login.php" style="text-decoration:none;">
            <button class="btn-secondary">Login</button>
        </a>

    </div>

</div>

</body>
</html>
