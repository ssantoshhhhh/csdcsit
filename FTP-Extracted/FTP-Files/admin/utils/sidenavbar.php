<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require '../utils/connect.php';
$toastMessage = '';
$toastType = '';
$username = $_SESSION['username'];

// Fetch admin's house ID
$query = "SELECT hid FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $house_id = $row['hid'];
} else {
    $toastMessage = "Admin not found.";
    $toastType = "error";
    echo "<script>alert('$toastMessage');</script>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.1/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --text-color: #333;
            --bg-light: #f8f9fa;
            --border-color: #e0e4e8;
            --hover-color: rgba(52, 152, 219, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Arial', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.4;
        }

        .sidebar-container {
            position: fixed;
            top: 5;
            left: 0;
            height: 100vh;
            width: 280px;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-right: 1px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border-radius: 0 20px 20px 0;
            overflow: hidden;
        }

        .sidebar-container.collapsed {
            width: 90px;
        }

        .sidebar-container.collapsed .sidebar-nav span {
            opacity: 0;
            visibility: hidden;
            transform: translateX(-20px);
            height: 20px;
        }

        .sidebar-container.collapsed .user-profile h4 {
            display: none;
        }

        .toggle-btn {
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            transform: scale(0.95);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .toggle-btn:hover {
            transform: scale(1.05);
            background: color-mix(in srgb, var(--primary-color) 90%, white);
        }

        .user-profile {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
        }

        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-nav {
            flex-grow: 1;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            gap: 15px;
            border-left: 4px solid transparent;
            border-radius: 0 10px 10px 0;
        }

        .sidebar-nav li a:hover {
            background-color: var(--hover-color);
            border-left-color: var(--primary-color);
            transform: translateX(10px);
        }

        .sidebar-nav li a i {
            font-size: 20px;
            min-width: 24px;
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }

        .sidebar-nav li a:hover i {
            transform: scale(1.1) rotate(10deg);
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media screen and (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
                border-radius: 0 20px 20px 0;
            }

            .sidebar-container.open {
                transform: translateX(0);
            }

            .overlay.open {
                display: block;
            }
        }

        @media screen and (min-width: 769px) {
            .sidebar-container {
                transform: translateX(0);
            }

            body {
                padding-left: 280px;
                transition: padding-left 0.3s ease;
            }

            body.collapsed-sidebar {
                padding-left: 90px;
            }
        }
    </style>
</head>

<body>
    <div class="overlay"></div>

    <div class="sidebar-container">

        <div class="scroll-sidebar">
            <div class="user-profile">
                <button class="toggle-btn" aria-label="Toggle Sidebar">☰</button>
                <img class="profile-img" src="../files/logos/<?php echo $house_id; ?>.jpg" alt="<?php echo $house_id; ?>">
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="../pages/index.php">
                            <i class="mdi mdi-bullseye"></i>
                            <span>DashBoard 🏠</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pages/events.php">
                            <i class="mdi mdi-bullseye"></i>
                            <span>Events 📅</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pages/add_event.php">
                            <i class="mdi mdi-gauge"></i>
                            <span>New Event 🎉</span>
                        </a>
                    </li>

                    <li>
                        <a href="../pages/add_Bulkpoints.php">
                            <i class="mdi mdi-file-excel"></i>
                            <span>Upload Points 📊</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pages/add_member.php">
                            <i class="mdi mdi-chart-bar"></i>
                            <span>Add New Member 👥</span>

                        </a>
                    </li>
                   
                    <li>
                        <a href="logout.php">
                            <i class="mdi mdi-logout"></i>
                            <span>Logout 🚪</span>
                            
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar-container');
        const body = document.body;
        const toggle = document.querySelector('.toggle-btn');
        const overlay = document.querySelector('.overlay');

        function toggleSidebar() {
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('open');
            } else {
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('collapsed-sidebar');
            }
        }

        toggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        window.addEventListener('resize', () => {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                sidebar.classList.remove('collapsed');
                body.classList.remove('collapsed-sidebar');
            }
        });
    </script>
</body>
</html>