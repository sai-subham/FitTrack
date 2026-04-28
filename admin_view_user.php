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

/* ===== BMI ===== */
$height_m = ($user['height'] ?? 0) / 100;
$bmi = 0;
if($height_m > 0){
    $bmi = round(($user['weight'] ?? 0) / ($height_m * $height_m), 2);
}
if($bmi < 18.5)      { $bmi_status = "Underweight"; $bmi_color = "#3b82f6"; }
elseif($bmi < 25)    { $bmi_status = "Normal";      $bmi_color = "#22c55e"; }
elseif($bmi < 30)    { $bmi_status = "Overweight";  $bmi_color = "#f59e0b"; }
else                 { $bmi_status = "Obese";        $bmi_color = "#ef4444"; }

/* ===== BMR (Mifflin-St Jeor simplified) ===== */
$weight_kg  = $user['weight'] ?? 0;
$height_cm  = $user['height'] ?? 0;
$age        = $user['age']    ?? 0;
$bmr = ($weight_kg > 0 && $height_cm > 0 && $age > 0)
    ? round((10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + 5)
    : 0;

/* ===== GOAL ===== */
$goal_query = mysqli_query($conn, "SELECT daily_step_goal FROM goals WHERE user_id='$target_user_id'");
$goal_data  = mysqli_fetch_assoc($goal_query);
$goal       = $goal_data['daily_step_goal'] ?? 10000;

/* ===== TODAY ===== */
$today       = date("Y-m-d");
$today_query = mysqli_query($conn, "SELECT * FROM daily_activity WHERE user_id='$target_user_id' AND date='$today'");
$today_data  = mysqli_fetch_assoc($today_query);
$today_steps = $today_data['steps'] ?? 0;

/* ===== STEP GOAL PROGRESS ===== */
$percentage = ($goal > 0) ? min(100, ($today_steps / $goal) * 100) : 0;
if($percentage >= 100)      { $ring_color = '#32D74B'; }
elseif($percentage >= 65)   { $ring_color = '#FF9D0A'; }
elseif($percentage >= 33)   { $ring_color = '#0A84FF'; }
else                        { $ring_color = '#FF375F'; }

/* ===== WEEKLY CHART DATA ===== */
$chart_query = mysqli_query($conn,
    "SELECT date, steps, sleep FROM daily_activity
     WHERE user_id='$target_user_id'
     ORDER BY date DESC LIMIT 7");

$chart_dates = []; $chart_steps = [];
$total_weekly_steps = 0; $total_weekly_sleep = 0; $days_recorded = 0;

while($row = mysqli_fetch_assoc($chart_query)){
    $chart_dates[] = $row['date'];
    $chart_steps[] = (int)$row['steps'];
    $total_weekly_steps += $row['steps'];
    $total_weekly_sleep += $row['sleep'];
    $days_recorded++;
}
$chart_dates = array_reverse($chart_dates);
$chart_steps = array_reverse($chart_steps);

$avg_steps = $days_recorded > 0 ? round($total_weekly_steps / $days_recorded) : 0;
$avg_sleep = $days_recorded > 0 ? round($total_weekly_sleep / $days_recorded, 1) : 0;

/* ===== ALL ACTIVITY RECORDS ===== */
$activity_query = mysqli_query($conn,
    "SELECT * FROM daily_activity WHERE user_id='$target_user_id' ORDER BY date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>View User – <?php echo htmlspecialchars($user['name']); ?></title>
    <link rel="stylesheet" href="style.css?v=6">
    <style>
        .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .profile-item { background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px 18px; }
        .profile-item small { display: block; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .profile-item strong { font-size: 1.1rem; font-weight: 700; }
        .activity-table { width: 100%; border-collapse: collapse; min-width: 600px; font-size: 0.9rem; }
        .activity-table th { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .activity-table td { padding: 13px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .activity-table tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge-green { background: rgba(50,215,75,0.15); color: #32D74B; }
        .badge-blue  { background: rgba(10,132,255,0.15); color: #0A84FF; }
        .badge-red   { background: rgba(255,55,95,0.15);  color: #FF375F; }
        .section-title { font-size: 1rem; font-weight: 700; margin-bottom: 15px; }
        .stat-mini-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px,1fr)); gap: 12px; margin-bottom: 22px; }
        .stat-mini { background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 16px; text-align: center; }
        .stat-mini .val { font-size: 1.4rem; font-weight: 800; }
        .stat-mini .lbl { font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; }
        .no-activity { text-align: center; color: var(--text-muted); padding: 30px; font-size: 0.9rem; }
        .back-btn { display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted); text-decoration: none; font-size: 0.88rem; margin-bottom: 18px; transition: color .2s; }
        .back-btn:hover { color: #fff; }
    </style>
</head>
<body>

<div class="centered-action-layout" style="align-items: flex-start; padding-top: 40px;">
    <div class="card centered-action-card" style="max-width: 1100px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 class="card-title" style="margin: 0;">User Profile: <?php echo htmlspecialchars($user['name']); ?></h3>
        </div>
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
                <div>
                    <h3 style="margin:0; font-size:1.3rem;"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p style="margin:3px 0 0; color:var(--text-muted); font-size:0.88rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="admin_edit_user.php?id=<?php echo urlencode($user['user_id']); ?>" style="background:#FF9D0A; color:#000; padding:8px 18px; border-radius:10px; text-decoration:none; font-weight:700; font-size:0.88rem;">✏️ Edit User</a>
                </div>
            </div>

            <!-- Profile Grid -->
            <div class="profile-grid">
                <div class="profile-item">
                    <small>User ID</small>
                    <strong>#<?php echo htmlspecialchars($user['user_id']); ?></strong>
                </div>
                <div class="profile-item">
                    <small>Age</small>
                    <strong><?php echo htmlspecialchars($user['age']); ?> yrs</strong>
                </div>
                <div class="profile-item">
                    <small>Weight</small>
                    <strong><?php echo htmlspecialchars($user['weight']); ?> kg</strong>
                </div>
                <div class="profile-item">
                    <small>Height</small>
                    <strong><?php echo htmlspecialchars($user['height']); ?> cm</strong>
                </div>
                <div class="profile-item">
                    <small>BMI</small>
                    <strong style="color:<?php echo $bmi_color; ?>;"><?php echo $bmi; ?> <span style="font-size:0.75rem; font-weight:500;">(<?php echo $bmi_status; ?>)</span></strong>
                </div>
                <div class="profile-item">
                    <small>BMR</small>
                    <strong><?php echo $bmr; ?> kcal</strong>
                </div>
                <div class="profile-item">
                    <small>Step Goal</small>
                    <strong><?php echo number_format($goal); ?> steps</strong>
                </div>
            </div>
        
        <!-- Today's Stats -->
        <div class="card" style="margin-bottom: 20px; text-align: left;">
            <p class="section-title">📊 Today's Activity <span style="font-size:0.8rem; color:var(--text-muted); font-weight:400;">(<?php echo $today; ?>)</span></p>
            <div class="stat-mini-grid">
                <div class="stat-mini">
                    <div class="val" style="color:#FF375F;"><?php echo number_format($today_steps); ?></div>
                    <div class="lbl">Steps</div>
                </div>
                <div class="stat-mini">
                    <div class="val" style="color:#FF9D0A;"><?php echo $today_data['calories'] ?? 0; ?></div>
                    <div class="lbl">Calories (kcal)</div>
                </div>
                <div class="stat-mini">
                    <div class="val" style="color:#0A84FF;"><?php echo $today_data['distance'] ?? 0; ?></div>
                    <div class="lbl">Distance (km)</div>
                </div>
                <div class="stat-mini">
                    <div class="val" style="color:#32D74B;"><?php echo $today_data['sleep'] ?? 0; ?></div>
                    <div class="lbl">Sleep (hrs)</div>
                </div>
                <div class="stat-mini">
                    <div class="val"><?php echo round($percentage); ?>%</div>
                    <div class="lbl">Goal Progress</div>
                </div>
                <div class="stat-mini">
                    <div class="val"><?php echo number_format($avg_steps); ?></div>
                    <div class="lbl">7-Day Avg Steps</div>
                </div>
                <div class="stat-mini">
                    <div class="val"><?php echo $avg_sleep; ?></div>
                    <div class="lbl">7-Day Avg Sleep (hrs)</div>
                </div>
            </div>
        </div>

        <!-- 7-Day Steps Chart -->
        <?php if($days_recorded > 0): ?>
        <div class="chart-card" style="margin-bottom: 20px;">
            <br><h3>Last 7 Days – Steps</h3>
            <canvas id="stepsChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- All Activity Records -->
        <div class="card" style="text-align: left;">
            <p class="section-title">🗂️ All Activity Records</p>
            <div style="overflow-x: auto;">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Steps</th>
                            <th>Calories (kcal)</th>
                            <th>Distance (km)</th>
                            <th>Sleep (hrs)</th>
                            <th>Goal Hit?</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $has_records = false;
                    while($act = mysqli_fetch_assoc($activity_query)){
                        $has_records = true;
                        $hit = $act['steps'] >= $goal;
                        $badge_class = $hit ? 'badge-green' : 'badge-red';
                        $badge_text  = $hit ? '✓ Yes' : '✗ No';
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($act['date']); ?></strong></td>
                            <td><?php echo number_format($act['steps']); ?></td>
                            <td><?php echo number_format($act['calories']); ?></td>
                            <td><?php echo $act['distance']; ?></td>
                            <td><?php echo $act['sleep']; ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if(!$has_records): ?>
                        <tr><td colspan="6" class="no-activity">No activity records found for this user.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="admin_dashboard.php" class="back-link">Back to All Users</a>
    </div>
</div>

<!-- Chart.js -->
<?php if($days_recorded > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.color = '#86868B';
Chart.defaults.font.family = "'Outfit', sans-serif";

new Chart(document.getElementById('stepsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_dates); ?>,
        datasets: [{
            label: 'Steps',
            data: <?php echo json_encode($chart_steps); ?>,
            backgroundColor: 'rgba(255,46,147,0.25)',
            borderColor: '#FF2E93',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(255,46,147,0.5)'
        }, {
            label: 'Goal',
            data: Array(<?php echo $days_recorded; ?>).fill(<?php echo $goal; ?>),
            type: 'line',
            borderColor: '#32D74B',
            borderWidth: 2,
            borderDash: [6,4],
            pointRadius: 0,
            fill: false,
            tension: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, labels: { color: '#86868B' } },
            tooltip: {
                backgroundColor: 'rgba(28,28,30,0.95)',
                titleColor: '#FFFFFF',
                bodyColor: '#FF2E93',
                padding: 12,
                cornerRadius: 12,
                displayColors: false
            }
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#86868B' } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#86868B' }, beginAtZero: true }
        }
    }
});
</script>
<?php endif; ?>

</body>
</html>
