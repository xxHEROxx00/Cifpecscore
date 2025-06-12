<?php
require_once 'includes/config.php';
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $selectedRole = $_POST['role']; // Get selected role from dropdown

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check password and selected role
        if (password_verify($password, $user['password']) && $user['role'] === $selectedRole) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: halaman-utama.php");
            exit();
        }
    }

    $_SESSION['error'] = 'Email, katalaluan atau peranan tidak sah';
    header("Location: index.php");
    exit();
}


?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CIFPEC</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link href="assets/vendor/fonts/circular-std/style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/libs/css/style.css">
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome/css/fontawesome-all.css">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            position: relative;
            z-index: 0;
            font-family: Arial, sans-serif;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: url('assets/images/bg_cifpec.jpg') no-repeat center center;
            background-size: cover;
            opacity: 0.3;
            /* Adjust opacity here */
            z-index: -1;
        }
    </style>

</head>

<body>
    <div class="splash-container">
        <div class="card ">
            <div class="card-header text-center">
                <h2>Sistem Pemarkahan CIFPEC</h2><span class="splash-description">Creative Innovation Final Projek Exhibition Connection.</span>
            </div>
            <div class="card-body">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex">
                            <div>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                        </div>

                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="email" type="email" placeholder="Email" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="password" type="password" placeholder="Katalaluan" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control form-control-lg" name="role" required>
                            <option value="" selected disabled>Pilih Peranan</option>
                            <option value="admin">Admin</option>
                            <option value="hakim">Panel / Hakim</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block" name="login">Log Masuk</button>
                </form>
            </div>
            <div class="card-footer bg-white p-0  ">
                <div class="card-footer-item card-footer-item-bordered">
                    <a href="register.php" class="footer-link">Daftar akaun baru</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Optional JavaScript -->
    <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
</body>

</html>