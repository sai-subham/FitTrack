<?php 
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$is_edit = false;

if(isset($_POST['save_activity'])){
    $date = $_POST['date'];
    $steps = !empty($_POST['steps']) ? $_POST['steps'] : 0;
    $calories = !empty($_POST['calories']) ? $_POST['calories'] : 0;
    $distance = !empty($_POST['distance']) ? $_POST['distance'] : 0;
    $sleep = !empty($_POST['sleep']) ? $_POST['sleep'] : 0;
    $mode = $_POST['mode']; // 'add' or 'edit'

    if($mode == 'edit'){
        // Strictly UPDATE
        mysqli_query($conn, "UPDATE daily_activity 
            SET steps='$steps', calories='$calories', distance='$distance', sleep='$sleep' 
            WHERE user_id='$user_id' AND date='$date'");
        header("Location: dashboard.php");
        exit();
    } else {
        // Mode is 'add' - Check for restriction
        $check = mysqli_query($conn, "SELECT user_id FROM daily_activity WHERE user_id='$user_id' AND date='$date'");
        if(mysqli_num_rows($check) > 0){
            $error = "Activity already logged for this date. Please use the 'Edit' button on the dashboard to update it.";
        } else {
            // INSERT new record
            mysqli_query($conn,"INSERT INTO daily_activity
            (user_id,date,steps,calories,distance,sleep)
            VALUES('$user_id','$date','$steps','$calories','$distance','$sleep')");
            header("Location: dashboard.php");
            exit();
        }
    }
}

// Check if a specific date was passed in the URL to edit
$edit_date = isset($_GET['date']) ? $_GET['date'] : '';
$edit_steps = '';
$edit_calories = '';
$edit_distance = '';
$edit_sleep = '';

if(!empty($edit_date)){
    $is_edit = true;
    $fetch_query = mysqli_query($conn, "SELECT * FROM daily_activity WHERE user_id='$user_id' AND date='$edit_date'");
    if($row = mysqli_fetch_assoc($fetch_query)){
        $edit_steps = $row['steps'];
        $edit_calories = $row['calories'];
        $edit_distance = $row['distance'];
        $edit_sleep = $row['sleep'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $is_edit ? 'Edit Activity' : 'Add Activity'; ?></title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="centered-action-layout">
    <div class="card centered-action-card">
        <h3 class="card-title"><?php echo $is_edit ? 'Edit Activity' : 'Log New Activity'; ?></h3>

        <?php if(!empty($error)){ ?>
            <div class="error-text"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
            <!-- Hidden field to handle logic -->
            <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">

            <div style="text-align: left; margin-bottom: 20px;">
                <label style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px; display: block;">Activity Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($edit_date); ?>" <?php echo $is_edit ? 'readonly' : 'required'; ?>>
            </div>

            <input type="number" name="steps" value="<?php echo htmlspecialchars($edit_steps); ?>" placeholder="Steps">

            <input type="number" name="calories" value="<?php echo htmlspecialchars($edit_calories); ?>" placeholder="Calories">

            <input type="number" step="0.1" name="distance" value="<?php echo htmlspecialchars($edit_distance); ?>" placeholder="Distance (km)">

            <input type="number" step="0.1" name="sleep" value="<?php echo htmlspecialchars($edit_sleep); ?>" placeholder="Sleep Hours">

            <button name="save_activity" class="btn-primary">
                <?php echo $is_edit ? 'Update Activity' : 'Save Activity'; ?>
            </button>
        </form>
        
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</div>

</body>
</html>