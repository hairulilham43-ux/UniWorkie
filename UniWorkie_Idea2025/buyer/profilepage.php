<?php
session_start();
require_once '../config.php';

// Redirect if not buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../login.php');
    exit();
}
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// ✅ Handle form update (kalau user submit form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // kalau nak tukar password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
    }
    $stmt->execute();
    $stmt->close();

    // reload semula supaya nampak perubahan
    header("Location: profilepage.php");
    exit();
}

// ✅ Ambil user info dari DB
$stmt = $conn->prepare("SELECT name, email, phone, password, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    /* ===== CSS yang awak bagi tadi ===== */
    :root {
        --primary: #4A90E2;
        --primary-light: #6BA4E7;
        --primary-lighter: #EDF5FF;
        --primary-dark: #357ABD;
        --secondary: #64B5F6;
        --accent: #2196F3;
        --light-bg: #F8FBFF;
        --white: #ffffff;
        --text-dark: #2C3E50;
        --text-light: #546E7A;
        --shadow-color: rgba(74, 144, 226, 0.1);
        --gradient-start: #4A90E2;
        --gradient-end: #64B5F6;
    }
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }
    .sidebar {
        width: 250px;
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        height: 100vh;
        position: fixed;
        box-shadow: 4px 0 15px var(--shadow-color);
        padding-top: 20px;
    }
    .logo {
        color: var(--white);
        font-size: 1.75rem;
        font-weight: bold;
        text-align: center;
        margin-bottom: 30px;
    }
        
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: var(--white);
        border-radius: 15px;
        box-shadow: 0 5px 15px var(--shadow-color);
    }
    .welcome {
        color: var(--primary);
        font-size: 1.75rem;
        font-weight: bold;
    }
    .user-profile {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        background: var(--primary-lighter);
        border-radius: 30px;
    }
    .user-profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 12px;
        border: 2px solid var(--primary-light);
    }
    /* Card Profile */
    .profile-card {
        background: var(--white);
        border-radius: 15px;
        box-shadow: 0 5px 15px var(--shadow-color);
        padding: 30px;
        max-width: 650px;
        margin: auto;
    }
    .profile-card .profile-pic {
        text-align: center;
        margin-bottom: 20px;
    }
    .profile-card .profile-pic img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 3px solid var(--primary);
        object-fit: cover;
    }
    .profile-card h2 {
        margin-top: 15px;
        color: var(--primary);
    }
    .profile-card table {
        width: 100%;
        border-collapse: collapse;
        font-size: 1rem;
    }
    .profile-card table td {
        padding: 12px;
    }
    .profile-card table tr:nth-child(even) {
        background: var(--light-bg);
    }
    .edit-btn {
        display: inline-block;
        margin-top: 25px;
        background: var(--primary);
        color: #fff;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        transition: 0.3s;
    }
    .edit-btn:hover { background: var(--primary-dark); }
    .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-menu li a {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            opacity: 0.85;
        }
        
        .nav-menu li a:hover {
            background: rgba(255,255,255,0.15);
            border-left-color: var(--white);
            opacity: 1;
            transform: translateX(5px);
        }
        
        .nav-menu li a.active {
            background: rgba(255,255,255,0.2);
            border-left-color: var(--white);
            opacity: 1;
        }
        
        .nav-menu li a i {
            margin-right: 12px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }
        
        .nav-menu li a:hover i {
            transform: scale(1.1);
        }

        .main-content {
             margin-left: 250px; /* bagi ruang ikut lebar sidebar */
            padding: 30px;
             width: calc(100% - 250px); /* elak overflow */
             box-sizing: border-box;}

        .hidden { display: none; }
  </style>
</head>
<body>
   <!-- Sidebar -->
     <div class="sidebar"> 
        <div class="logo">UniWorkie</div> <ul class="nav-menu">
            <li><a href="dashboard.php" ><i class="fas fa-home"></i> Home</a></li> 
            <li><a href="save.php" ><i class="fas fa-heart"></i> Saved Items</a></li>
            <li><a href="recruit.php"><i class="fas fa-user-graduate"></i> Find And Recruit</a></li>
            <li><a href="profilepage.php"class="active"><i class="fas fa-user"></i> Profile Page</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li> </ul>
             </div>

    <div class="main-content">
        <div class="header">
            <h1 class="welcome">My Profile</h1>
            <div class="user-profile">
                <img src="../uploads/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" alt="Profile">
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
        </div>

        <!-- Profile Card -->
        <div id="view-card" class="profile-card">
            <div class="profile-pic">
                <img src="../uploads/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" alt="Profile Picture">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            </div>
            <table>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone</strong></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td>********</td>
                </tr>
            </table>
            <div style="text-align:center;">
                <button class="edit-btn" onclick="toggleEdit(true)"><i class="fas fa-edit"></i> Edit Profile</button>
            </div>
        </div>

        <!-- Edit Form -->
        <div id="edit-form" class="profile-card hidden">
            <form method="POST">
                <div class="profile-pic">
                    <img src="../uploads/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" alt="Profile Picture">
                    <h2>Edit Profile</h2>
                </div>
                <table>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><strong>Phone</strong></td>
                        <td><input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"></td>
                    </tr>
                    <tr>
                        <td><strong>Password</strong></td>
                        <td><input type="password" name="password" placeholder="Leave blank to keep current"></td>
                    </tr>
                </table>
                <div style="text-align:center; margin-top:20px;">
                    <button type="submit" class="edit-btn"><i class="fas fa-save"></i> Save</button>
                    <button type="button" class="edit-btn" style="background:gray;" onclick="toggleEdit(false)">Cancel</button>
                </div>
            </form>
        </div>
    </div>

<script>
function toggleEdit(show) {
    document.getElementById('view-card').classList.toggle('hidden', show);
    document.getElementById('edit-form').classList.toggle('hidden', !show);
}
</script>
</body>
</html>