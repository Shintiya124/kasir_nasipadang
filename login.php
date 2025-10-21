<?php
require 'function.php';

if(!isset($_SESSION['login'])){
    // belum login
} else {
    header('location:index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login | Warung Nasi Sesuai Salero</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- 🌈 Desain Tambahan -->
    <style>
        body {
            background: url('kasir/img/bg-nasipadang.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
        }

        /* Overlay gelap agar teks tetap jelas */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 1;
        }

        .card-header h3 {
            color: #b71c1c;
            font-weight: 700;
            text-transform: uppercase;
        }

        .card-header p {
            margin-top: -10px;
            font-size: 14px;
            color: #555;
        }

        .btn-primary {
            background-color: #d32f2f;
            border: none;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background-color: #b71c1c;
        }

        label {
            font-weight: 600;
            color: #333;
        }

        .container {
            padding-top: 80px;
            padding-bottom: 80px;
        }
    </style>
</head>

<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header text-center">
                                    <h3>Login</h3>
                                    <p>Warung Nasi Sesuai Salero</p>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group mb-3">
                                            <label for="username">Username</label> 
                                            <input class="form-control py-2" id="username" name="username" type="text" placeholder="Masukkan username" required />
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="password">Password</label>
                                            <div class="input-group">
                                                <input class="form-control py-2" id="inputPassword" name="password" type="password" placeholder="Masukkan password" required />
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group d-flex justify-content-center">
                                            <button type="submit" name="login" class="btn btn-primary px-5">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePassword');
        const pwdInput = document.getElementById('inputPassword');
        const icon = document.getElementById('togglePasswordIcon');

        toggleBtn.addEventListener('click', function () {
            const isPwd = pwdInput.getAttribute('type') === 'password';
            pwdInput.setAttribute('type', isPwd ? 'text' : 'password');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
    </script>

</body>
</html>