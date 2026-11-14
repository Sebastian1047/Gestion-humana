<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión – Jardines de San Nicolás</title>
  <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png" />
  <link rel="stylesheet" href="../CSS/variables.css" />
  <link rel="stylesheet" href="../CSS/StyleLogin.css?v=<?= filemtime(__DIR__ . '/../CSS/StyleLogin.css') ?>" />
</head>

<body>
  <div class="login-page">

    <!-- ══ Panel de marca (izquierda) ══ -->
    <div class="login-panel-marca">
      <img src="../IMG/LOGOWINDOW.png" alt="" class="marca-marca-agua" aria-hidden="true" />

      <div class="marca-contenido">
        <p class="marca-eyebrow">Gestor de Documentos</p>
        <h1 class="marca-titulo">Todo tu talento humano, en un solo lugar.</h1>
        <p class="marca-subtitulo">Jardines de San Nicolás S.A.S. JN</p>
      </div>
    </div>

    <!-- ══ Panel de formulario (derecha) ══ -->
    <div class="login-panel-form">
      <div class="login-form-box">

        <img src="../IMG/LOGOWINDOW.png" alt="Jardines de San Nicolás" class="form-logo-movil" />

        <h2 class="form-titulo">Iniciar sesión</h2>
        <p class="form-subtitulo">Ingresa con tu código para continuar.</p>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert error">
            <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form action="../Controller/logincontroller.php" method="POST">

          <div class="input-group">
            <label for="username">Código</label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Ingresa tu código"
              autocomplete="username"
              required />
          </div>

          <div class="input-group">
            <label for="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Ingresa tu contraseña"
              autocomplete="current-password"
              required />
          </div>

          <button type="submit" class="btn-login">Ingresar</button>

        </form>

        <div class="extra">
          <a href="../View/recuperar.php">¿Olvidaste tu contraseña?</a>
        </div>
      </div>
    </div>

  </div>

  <script src="../JS/Script.js"></script>
<script src="../JS/demo.js"></script>
</body>

</html>
