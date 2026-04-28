<?php
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===== USER DATA ===== */
$user_query = mysqli_query($conn,
    "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* ===== BMI ===== */
$height_m = ($user['height'] ?? 0) / 100;
$bmi = 0;

if ($height_m > 0) {
    $bmi = round(($user['weight'] ?? 0) / ($height_m * $height_m), 2);
}

if ($bmi < 18.5) {
    $bmi_status = "Underweight";
    $bmi_color = "#3b82f6";
}
elseif ($bmi < 25) {
    $bmi_status = "Normal";
    $bmi_color = "#22c55e";
}
elseif ($bmi < 30) {
    $bmi_status = "Overweight";
    $bmi_color = "#f59e0b";
}
else {
    $bmi_status = "Obese";
    $bmi_color = "#ef4444";
}

/* ===== TODAY DATA ===== */
$today = date("Y-m-d");

$today_query = mysqli_query($conn,
    "SELECT * FROM daily_activity 
     WHERE user_id='$user_id' AND date='$today'");
$today_data = mysqli_fetch_assoc($today_query);

$today_steps = $today_data['steps'] ?? 0;
$today_sleep = $today_data['sleep'] ?? 0;

/* ===== GOAL ===== */
$goal_query = mysqli_query($conn,
    "SELECT daily_step_goal FROM goals WHERE user_id='$user_id'");
$goal_data = mysqli_fetch_assoc($goal_query);

$goal = $goal_data['daily_step_goal'] ?? 10000;

$percentage = 0;
$ring_color = '#FF375F'; // Initial Red/Pink

if ($goal > 0) {
    $percentage = min(100, ($today_steps / $goal) * 100);

    if ($percentage >= 100) {
        $ring_color = '#32D74B'; // Green (Goal Achieved)
    }
    elseif ($percentage >= 65) {
        $ring_color = '#FF9D0A'; // Orange/Yellow
    }
    elseif ($percentage >= 33) {
        $ring_color = '#0A84FF'; // Blue
    }
}

/* ===== WEEKLY CHART & AVERAGES ===== */
$chart_query = mysqli_query($conn,
    "SELECT date, steps, sleep FROM daily_activity 
     WHERE user_id='$user_id'
     ORDER BY date DESC LIMIT 7");

$dates = [];
$steps = [];
$total_weekly_steps = 0;
$total_weekly_sleep = 0;
$days_recorded = 0;

while ($row = mysqli_fetch_assoc($chart_query)) {
    $dates[] = $row['date'];
    $steps[] = $row['steps'];
    $total_weekly_steps += $row['steps'];
    $total_weekly_sleep += $row['sleep'];
    $days_recorded++;
}

$dates = array_reverse($dates);
$steps = array_reverse($steps);

/* ===== AVERAGES ===== */
$avg_steps = $days_recorded > 0 ? round($total_weekly_steps / $days_recorded) : 0;
$avg_sleep = $days_recorded > 0 ? round($total_weekly_sleep / $days_recorded, 1) : 0;


/* ===== BMR CALCULATION (Mifflin-St Jeor) ===== */
// Assuming a simplified approach without Gender initially: (10 * weight) + (6.25 * height) - (5 * age) + 5
$weight_kg = $user['weight'] ?? 0;
$height_cm = $user['height'] ?? 0;
$age = $user['age'] ?? 30; // default age if missing

$bmr = 0;
if ($weight_kg > 0 && $height_cm > 0 && $age > 0) {
    $bmr = round((10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + 5);
}

/* ===== SMART INSIGHTS ===== */
$insight_text = "Keep moving to reach your goals today!";
$insight_color = "#3b82f6"; // default blue

if ($today_sleep > 0 && $today_sleep < 6) {
    $insight_text = "You're getting less sleep than recommended. Prioritize rest tonight!";
    $insight_color = "#f59e0b"; // warning orange
}
elseif ($percentage >= 100) {
    $insight_text = "Goal crushed! Incredible work today 🏆";
    $insight_color = "#10b981"; // success green
}
elseif ($percentage >= 80) {
    $insight_text = "You're almost there! Just a short walk to hit your goal.";
    $insight_color = "#3b82f6";

}
elseif ($days_recorded > 0 && $today_steps < ($avg_steps * 0.5)) {
    $insight_text = "You're moving less than your weekly average. Time for a walk!";
    $insight_color = "#ef4444"; // danger red
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css?v=6">
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-logo"><img src="logo.png" class="logo-img"> FitTrack</h2>
        
        <div class="sidebar-nav">
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="edit_profile.php">⚡ Edit Profile</a>
            <a href="add_activity.php">🎯 Add Activity</a>
            <a href="set_goal.php">📅 Set Goal</a>
            <a href="view_record.php">⚙️ View Record</a>
            <a href="delete_account.php">🚪 Delete Account</a>
        </div>
        
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">

        <div class="top-bar">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
        </div>

        <!-- Smart Insights Banner -->
        <div class="insight-banner" style="border-left: 4px solid <?php echo $insight_color; ?>;">
            <div class="insight-icon">💡</div>
            <div class="insight-content">
                <strong>Smart Insight</strong>
                <p><?php echo $insight_text; ?></p>
            </div>
        </div>

        <!-- Main Rings & Stats -->
        <div class="activity-rings-container">

            <!-- Activity Ring Card -->
            <div class="stat-card ring-card">
                <h4>Daily Goal Progress</h4>
                <a href="add_activity.php?date=<?php echo $today; ?>" class="edit-today-btn">✏️ Edit Today</a>
                
                <div class="circular-progress" style="--progress: <?php echo $percentage; ?>; --color: <?php echo $ring_color; ?>;">
                    <div class="inner-circle">
                        <h2><?php echo round($percentage); ?>%</h2>
                        <span><?php echo $today_steps; ?> / <?php echo $goal; ?></span>
                    </div>
                </div>
            </div>

            <!-- Health Metrics Grid -->
            <div class="metrics-grid">
                <div class="stat-card">
                    <h4>Calories Burned</h4>
                    <p class="highlight-text fire"><?php echo $today_data['calories'] ?? 0; ?> <small>kcal</small></p>
                    <small>Basal Metabolic Rate: <strong><?php echo $bmr; ?> kcal</strong></small>
                </div>

                <div class="stat-card">
                    <h4>Distance Traveled</h4>
                    <p class="highlight-text distance"><?php echo $today_data['distance'] ?? 0; ?> <small>km</small></p>
                </div>

                <div class="stat-card">
                    <h4>Sleep Tracked</h4>
                    <p class="highlight-text sleep"><?php echo $today_sleep; ?> <small>hrs</small></p>
                    <small>7-Day Avg: <strong><?php echo $avg_sleep; ?> hrs</strong></small>
                </div>

                <div class="stat-card">
                    <h4>Weekly Steps Avg</h4>
                    <p class="highlight-text steps"><?php echo $avg_steps; ?></p>
                    <small>Over last <?php echo $days_recorded; ?> days recorded</small>
                </div>
                
                <div class="stat-card">
                    <h4>Current BMI</h4>
                    <p class="highlight-text bmi" style="color:<?php echo $bmi_color; ?>;"><?php echo $bmi; ?></p>
                    <small>Status: <strong><?php echo $bmi_status; ?></strong></small>
                </div>
            </div>

        </div>

        <!-- Chart Section -->
        <div class="chart-card">
            <br><h3>Last 7 Days Steps</h3>
            <canvas id="chart"></canvas>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.color = '#86868B';
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.weight = '600';

new Chart(document.getElementById("chart"),{
    type:"line",
    data:{
        labels: <?php echo json_encode($dates); ?>,
        datasets:[{
            label:"Steps",
            data: <?php echo json_encode($steps); ?>,
            borderColor:"#FF2E93",
            backgroundColor:"rgba(255, 46, 147, 0.1)",
            borderWidth: 4,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "#FF2E93",
            pointHoverBackgroundColor: "#FF2E93",
            pointHoverBorderColor: "#fff",
            pointRadius: 5,
            pointHoverRadius: 7,
            fill:true,
            tension:0.4
        }]
    },
    options:{
        responsive: true,
        plugins:{
            legend:{ display:false },
            tooltip: {
                backgroundColor: 'rgba(28, 28, 30, 0.95)',
                titleColor: '#FFFFFF',
                bodyColor: '#FF2E93',
                bodyFont: { weight: 'bold' },
                padding: 12,
                cornerRadius: 12,
                displayColors: false,
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1
            }
        },
        scales:{
            x:{ 
                grid: { color: "rgba(255,255,255,0.04)", drawBorder: false },
                ticks:{ color:"#86868B" }
            },
            y:{ 
                grid: { color: "rgba(255,255,255,0.04)", drawBorder: false },
                ticks:{ color:"#86868B" },
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>