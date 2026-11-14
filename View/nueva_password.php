<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png" />
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="../CSS/variables.css">
    <link rel="stylesheet" href="../CSS/StyleLogin.css?v=<?= filemtime(__DIR__ . '/../CSS/StyleLogin.css') ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Nueva contraseña</h2>
            <form action="../Controller/updatePassword.php" method="POST">
                <div class="input-group">
                    <label>Token</label>
                    <input type="text" name="token" required>
                </div>
                <div class="input-group">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button class="btn-login">Cambiar contraseña</button>
            </form>
        </div>
    </div>
<script src="../JS/demo.js"></script>
</body>
</html>
