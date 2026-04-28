<?php
include("db.php");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['delete_user'])) {
    $del_id = (int)$_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id='$del_id'");
    header("Location: admin_dashboard.php");
    exit();
}

$users_query = mysqli_query($conn, "SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=6">
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar-logo"><img src="logo.png" class="logo-img"> FitTrack </h2>
        
        <div class="sidebar-nav">
            <a href="admin_dashboard.php" class="active">🛡️ All Users</a>
        </div>
        
        <a href="admin_logout.php" class="logout-link">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">

        <div class="top-bar">
            <h2>Hello, Administrator 👋</h2>
        </div>

        <div class="card" style="margin-top: 20px; text-align: left;">
            <h3 style="margin-bottom: 5px;">All Registered Users</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Manage all user accounts below.</p>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">Name</th>
                            <th style="padding: 12px; text-align: left;">Email</th>
                            <th style="padding: 12px; text-align: left;">Stats</th>
                            <th style="padding: 12px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($users_query)) { ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;">
                            <td style="padding: 15px 12px; font-weight: 500;">#<?php echo htmlspecialchars($u['user_id']); ?></td>
                            <td style="padding: 15px 12px;"><?php echo htmlspecialchars($u['name']); ?></td>
                            <td style="padding: 15px 12px; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="padding: 15px 12px; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($u['age']); ?>y &bull; <?php echo htmlspecialchars($u['weight']); ?>kg &bull; <?php echo htmlspecialchars($u['height']); ?>cm
                            </td>
                            <td style="padding: 15px 12px; text-align: right;">
                                <a href="admin_view_user.php?id=<?php echo urlencode($u['user_id']); ?>" style="color: #0A84FF; text-decoration: none; margin-right: 15px; font-size: 0.9rem; font-weight: 500;">View</a>
                                <a href="admin_edit_user.php?id=<?php echo urlencode($u['user_id']); ?>" style="color: #FF9D0A; text-decoration: none; margin-right: 15px; font-size: 0.9rem; font-weight: 500;">Edit</a>
                                <a href="admin_dashboard.php?delete_user=<?php echo urlencode($u['user_id']); ?>" onclick="return confirm('Delete this user permanently?');" style="color: #FF375F; text-decoration: none; font-size: 0.9rem; font-weight: 500;">Delete</a>
                            </td>
                        </tr>
                        <?php
}?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

</body>
</html>
