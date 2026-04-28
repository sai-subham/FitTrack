<?php 
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['set'])){
    $goal = intval($_POST['goal']);

    if($goal > 0){

        mysqli_query($conn,"REPLACE INTO goals (user_id,daily_step_goal)
        VALUES('$user_id','$goal')");

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Please enter a valid goal!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Goal</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="centered-action-layout">
    <div class="card centered-action-card">
        <h3 class="card-title">Set Daily Goal</h3>

        <?php if(isset($error)){ ?>
            <p class="error-text">
                <?php echo $error; ?>
            </p>
        <?php } ?>

        <form method="POST">
            <input type="number" name="goal" placeholder="Enter goal (e.g., 10000)" required>
            <button name="set" class="btn-primary">Save Goal</button>
        </form>
        
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</div>

</body>
</html>