<?php
require_once '../controllers/UserController.php';
$controller = new UserController();

if($controller->isLoggedIn()) {
    header("Location: game.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else if($controller->register($username, $password)) {
        header("Location: login.php?registered=true");
        exit();
    } else {
        $error = "Error al registrar el usuario";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Mi Juego</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            height: 100vh;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
            margin: 50px auto;
        }
        .form-control {
            border-radius: 20px;
            padding: 10px 20px;
        }
        .btn-primary {
            border-radius: 20px;
            padding: 10px 20px;
            background: linear-gradient(120deg, #4CAF50 0%, #45a049 100%);
            border: none;
            width: 100%;
        }
        .btn-primary:hover {
            background: linear-gradient(120deg, #45a049 0%, #4CAF50 100%);
        }
        .title {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="title">Registro</h2>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Usuario" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirmar Contraseña" required>
                </div>
                <button type="submit" class="btn btn-primary">Registrarse</button>
                <p class="text-center mt-3">
                    ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html> 