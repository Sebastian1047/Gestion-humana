<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png" />
  <title>Recuperar contraseña</title>
  <link rel="stylesheet" href="../CSS/variables.css">
  <link rel="stylesheet" href="../CSS/StyleLogin.css?v=<?= filemtime(__DIR__ . '/../CSS/StyleLogin.css') ?>">
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <h2>Recuperar contraseña</h2>
    <form action="../Controller/recuperarcontroller.php" method="POST">
      <div class="input-group">
        <label>Correo</label>
        <input type="email" name="email" required>
      </div>
      <button class="btn-login">Enviar token</button>
    </form>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert error"><?= $_GET['error'] ?></div>
    <?php endif; ?>
  </div>
</div>
<script src="../JS/demo.js"></script>
</body>
</html>
