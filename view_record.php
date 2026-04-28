<?php 
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['search'])){
    $date = $_POST['date'];

    $result = mysqli_query($conn,
        "SELECT * FROM daily_activity 
         WHERE user_id='$user_id' AND date='$date'");

    $record = mysqli_fetch_assoc($result);

    if(!$record){
        $error = "No record found for selected date.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Record</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="centered-action-layout">
    <div class="card centered-action-card" style="max-width: 600px;">
        <h3 class="card-title">View History</h3>

        <form method="POST">
            <div style="text-align: left; margin-bottom: 20px;">
                <label style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px; display: block;">Select Date</label>
                <input type="date" name="date" required>
            </div>
            <button name="search" class="btn-primary">Search Record</button>
        </form>

        <?php if(isset($error)){ ?>
            <p class="error-text">
                <?php echo $error; ?>
            </p>
        <?php } ?>

        <?php if(isset($record) && $record){ ?>
            <div class="metrics-grid" style="margin-top: 25px; text-align: left; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="stat-card" style="padding: 20px;">
                    <h4 style="font-size: 0.9rem; color: var(--text-muted);">Steps</h4>
                    <p class="highlight-text steps" style="font-size: 1.5rem;"><?php echo $record['steps']; ?></p>
                </div>

                <div class="stat-card" style="padding: 20px;">
                    <h4 style="font-size: 0.9rem; color: var(--text-muted);">Calories</h4>
                    <p class="highlight-text fire" style="font-size: 1.5rem;"><?php echo $record['calories']; ?></p>
                </div>

                <div class="stat-card" style="padding: 20px;">
                    <h4 style="font-size: 0.9rem; color: var(--text-muted);">Distance</h4>
                    <p class="highlight-text distance" style="font-size: 1.5rem;"><?php echo $record['distance']; ?> km</p>
                </div>

                <div class="stat-card" style="padding: 20px;">
                    <h4 style="font-size: 0.9rem; color: var(--text-muted);">Sleep</h4>
                    <p class="highlight-text sleep" style="font-size: 1.5rem;"><?php echo $record['sleep']; ?> hrs</p>
                </div>
            </div>
        <?php } ?>
        
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</div>

</body>
</html>