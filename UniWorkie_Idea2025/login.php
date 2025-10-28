<?php
session_start();
require_once 'config.php'; // pastikan fungsi getDBConnection() wujud di sini
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();

    // sanitize / trim input
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // simple validation
    if ($email === '' || $password === '') {
        $error = "Please fill in both email and password.";
    } else {
        // Prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, role, password, profile_pic, is_banned FROM users WHERE email = ?");
        if ($stmt === false) {
            // query prepare failed
            $error = "Server error (prepare).";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if ($user['is_banned']) {
                    $error = "Account banned.";
                } else {
                    $stored = $user['password'];
                    $verified = false;

                    // First try password_verify (works for hashed passwords)
                    if (password_verify($password, $stored)) {
                        $verified = true;

                        // Rehash if algorithm/options changed
                        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                            if ($u_stmt) {
                                $u_stmt->bind_param("si", $newHash, $user['id']);
                                $u_stmt->execute();
                                $u_stmt->close();
                            }
                        }
                    } else {
                        // If password_verify failed, maybe stored password is plain-text (legacy)
                        // Compare safely with hash_equals to avoid timing attacks
                        if (hash_equals((string)$stored, (string)$password)) {
                            $verified = true;
                            // Rehash and update DB to secure one-way hash
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                            if ($u_stmt) {
                                $u_stmt->bind_param("si", $newHash, $user['id']);
                                $u_stmt->execute();
                                $u_stmt->close();
                            }
                        }
                    }

                    if ($verified) {
                        // set session and update last_online
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['profile_pic'] = $user['profile_pic'];

                        $update_stmt = $conn->prepare("UPDATE users SET last_online = NOW() WHERE id = ?");
                        if ($update_stmt) {
                            $update_stmt->bind_param("i", $user['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }

                        // Redirect based on role
                        $redirect = match($user['role']) {
                            'admin' => 'admin/dashboard.php',
                            'seller' => 'seller/dashboard.php',
                            default => 'buyer/dashboard.php'
                        };
                        $stmt->close();
                        $conn->close();
                        header("Location: $redirect");
                        exit();
                    } else {
                        $error = "Invalid password!";
                    }
                }
            } else {
                $error = "Email not registered or account banned!";
            }

            if ($stmt) $stmt->close();
        }
    }

    if ($conn) $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>UniWorkie - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root{
            --primary: #4A90E2;
            --primary-light: #61B0FE;
            --primary-dark: #357ABD;
            --accent: #2196F3;
            --white: #ffffff;
            --text-dark: #2C3E50;
            --text-light: #546E7A;
            --muted: #9AA8B2;
            --bg-layer: rgba(255,255,255,0.92);
            --error: #EF5350;
            --shadow-color: rgba(74, 144, 226, 0.16);
            --glass-border: rgba(38, 78, 118, 0.06);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html,body { height: 100%; }

        body {
            font-family: 'Segoe UI', Roboto, system-ui, -apple-system, "Helvetica Neue", Arial;
            background: linear-gradient(180deg, #E6F0FB 0%, #F7FBFF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            color: var(--text-dark);
        }

        /* decorative background shapes */
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
            pointer-events: none;
        }
        .bg-shape.one {
            width: 420px; height: 420px; left: -80px; top: -60px;
            background: linear-gradient(135deg, rgba(74,144,226,0.18), rgba(97,176,254,0.08));
        }
        .bg-shape.two {
            width: 380px; height: 380px; right: -100px; bottom: -80px;
            background: linear-gradient(135deg, rgba(33,150,243,0.14), rgba(53,122,189,0.06));
        }

        .login-wrap {
            width: 420px;
            max-width: 100%;
            position: relative;
            z-index: 2;
        }

        .card {
            background: var(--bg-layer);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 36px;
            box-shadow: 0 12px 30px var(--shadow-color);
            backdrop-filter: blur(6px) saturate(110%);
            -webkit-backdrop-filter: blur(6px) saturate(110%);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(74,144,226,0.18); }

        .brand {
            display:flex; align-items:center; gap:12px; justify-content:center; margin-bottom: 22px;
        }
        .brand i {
            font-size: 28px; color: var(--primary);
            width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;
            background: linear-gradient(135deg, rgba(74,144,226,0.12), rgba(97,176,254,0.08));
            border-radius: 12px;
        }
        .brand .title {
            font-weight: 700; font-size: 1.5rem; color: var(--text-dark);
            letter-spacing: -0.4px;
        }

        .subtitle {
            text-align:center; color: var(--muted); font-size: 0.95rem; margin-bottom: 18px;
        }

        .error {
            color: var(--error);
            margin: 10px 0 18px 0;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(239, 83, 80, 0.06);
            font-size: 0.95rem;
            border: 1px solid rgba(239,83,80,0.08);
            animation: shake 0.45s ease;
        }

        form .input-group {
            position: relative;
            margin-bottom: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 14px 14px 46px;
            border-radius: 12px;
            border: 1px solid #e6f0fb;
            background: linear-gradient(180deg, #fbfeff 0%, #f6fbff 100%);
            font-size: 1rem;
            color: var(--text-dark);
            transition: box-shadow .18s ease, transform .12s ease, border-color .18s ease;
        }
        .input-group input::placeholder { color: var(--muted); }
        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(74,144,226,0.09);
            transform: translateY(-2px);
        }
        .input-group .icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--muted);
        }

        .actions {
            margin-top: 10px;
        }
        .btn-primary {
            display: inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            box-shadow: 0 10px 28px rgba(74,144,226,0.12);
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(74,144,226,0.15); }

        .helper {
            display:flex; align-items:center; justify-content:space-between; margin-top: 12px;
            font-size: 0.92rem; color: var(--muted);
        }
        .register-link { text-align:center; margin-top: 18px; font-size: 0.95rem; color: var(--muted); }
        .register-link a { color: var(--primary); text-decoration:none; font-weight:600; }
        .register-link a:hover { text-decoration: underline; color: var(--primary-dark); }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
            100% { transform: translateX(0); }
        }

        @media (max-width:520px) {
            .card { padding: 24px; border-radius: 14px; }
        }

        .bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;  
            z-index: -1;        
            opacity: 1;   /* clear video */
        }

        body::before {
            background: transparent; /* no overlay */
        }

    </style>
</head>
<body>
    <div class="bg-shape one" aria-hidden="true"></div>
    <div class="bg-shape two" aria-hidden="true"></div>

    <video autoplay muted loop playsinline class="bg-video">
        <source src="assets/background.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>


    <div class="login-wrap">
        <div class="card" role="main" aria-labelledby="loginTitle">
            <div class="brand">
                <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                <div>
                    <div id="loginTitle" class="title">UniWorkie</div>
                    <div class="subtitle">Sign in to your account</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary" aria-label="Login">
                        <span>Login</span>
                    </button>
                </div>

                <div class="helper">
                    <div><label style="font-weight:600;"><input type="checkbox" onclick="togglePassword(this)" style="margin-right:8px;transform:translateY(2px)"> Show</label></div>
                    <div><a href="forgot.php">Forgot password?</a></div>
                </div>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(cb) {
            const pw = document.querySelector('input[name="password"]');
            if (!pw) return;
            pw.type = cb.checked ? 'text' : 'password';
        }
    </script>
    
</body>
</html>
