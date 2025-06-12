<?php
require_once 'includes/config.php';
session_start();


if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['error'] = 'Email is already registered!';
    } else {
        $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
        $_SESSION['success'] = 'Registration successful! Please login';
    }

    header("Location: register.php");
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
        <div class="card">

            <div class="card-header">
                <div class="text-center">
                    <h2>CIFPEC</h2>
                </div>
                <h3 class="mb-1">Daftar Akaun</h3>
                <p>Sila masukkan maklumat anda.</p>
            </div>


            <div class="card-body">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex">
                            <div>
                                <!-- Tabler alert-circle icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                    <path d="M12 8v4" />
                                    <path d="M12 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                        </div>

                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>


                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <div class="d-flex">
                            <div>
                                <?= htmlspecialchars($_SESSION['success']) ?>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="text" name="name" required="" placeholder="Nama Penuh" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="email" name="email" required="" placeholder="E-mail" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="password" type="password" required="" placeholder="Password">
                    </div>
                    <div class="form-group pt-2">
                        <button class="btn btn-block btn-primary" type="submit" name="register">Daftar Akaun</button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white">
                <p>Sudah mempunyai akaun? <a href="index.php" class="text-secondary">Log Masuk disini.</a></p>
            </div>
        </div>
    </div>
</body>


</html>