<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>LOGIN ADMIN | SPK JURNAL</title>
    
    <link href="assets/css/flatly-bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script> 
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        .login-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border: none;
        }

        .login-header {
            background: #18bc9c;
            padding: 35px 20px;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 22px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }

        /* Ikon di dalam input */
        .inner-addon { position: relative; }
        .inner-addon i {
            position: absolute;
            padding: 15px;
            pointer-events: none;
            color: #95a5a6;
        }
        .inner-addon input { padding-left: 45px !important; }

        .form-control {
            height: 48px;
            border-radius: 8px;
            border: 2px solid #f1f3f5;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #18bc9c;
            box-shadow: none;
        }

        .btn-login {
            background: #18bc9c;
            border: none;
            height: 50px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            color: white;
        }

        .btn-login:hover {
            background: #128f76;
            transform: translateY(-2px);
            color: white;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #95a5a6;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <i class="glyphicon glyphicon-lock"></i>
            <h2>Admin Login</h2>
        </div>
        
        <div class="login-body">
            <?php if($_POST) include 'aksi.php'; ?>

            <form action="?act=login" method="post">
                <div class="form-group">
                    <label>Username</label>
                    <div class="inner-addon">
                        <i class="glyphicon glyphicon-user"></i>
                        <input type="text" class="form-control" placeholder="Username" name="user" required autofocus autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="inner-addon">
                        <i class="glyphicon glyphicon-eye-open"></i>
                        <input type="password" class="form-control" placeholder="Password" name="pass" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    MASUK SEKARANG
                </button>
                
                <a href="index.html" class="back-link">
                    <i class="glyphicon glyphicon-arrow-left"></i> Kembali ke Beranda
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>