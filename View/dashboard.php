<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<?php
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: ../View/login.php");
  exit();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png" />
  <title>Gestor de Documentos - Jardines De San Nicolas</title>
  <link rel="stylesheet" href="../CSS/variables.css">
  <link rel="stylesheet" href="../CSS/layout.css">
  <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= filemtime(__DIR__ . '/../CSS/dashboard.css') ?>">
  <link rel="stylesheet" href="../CSS/componentes.css">
</head>

<style>
  body {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
</style>

<body>
  <?php include __DIR__ . '/partials/hamburger.php'; ?>

  <div class="container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>


    <main class="main">
      <header class="header">
        <div style="display:flex;align-items:center;">
            <?php include __DIR__ . '/partials/icono_papelera_header.php'; ?>
            <div class="user">
              <span>👤 <?php echo $_SESSION['user']['nombre'] ?? 'Usuario'; ?></span>
            </div>
        </div>
      </header>

      <section class="content">

        <div class="hero-dashboard">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;flex-wrap:wrap;">
            <div>
              <h1>
                Bienvenido, <?php echo $_SESSION['user']['nombre'] ?? 'Usuario'; ?>
              </h1>

              <h2>
                Gestor de Documentos
              </h2>

              <p>
                Administra y consulta la documentación corporativa de
                Jardines de San Nicolas S.A.S. desde un único lugar de manera
                segura, organizada y eficiente.
              </p>
            </div>

            <a href="ManualUsuario.html" target="_blank" class="btn-manual-usuario">
              <span>📘</span> Manual de Usuario
            </a>
          </div>
        </div>

        <div class="flujo-grid">

          <div class="flujo-card">
            <div class="flujo-icon">📋</div>
            <h3>Pre-Ingreso</h3>
            <p>
              Gestión documental previa a la vinculación del colaborador.
            </p>
          </div>

          <div class="flujo-card">
            <div class="flujo-icon">📝</div>
            <h3>Post-Ingreso</h3>
            <p>
              Documentación posterior al ingreso y procesos internos.
            </p>
          </div>

          <div class="flujo-card">
            <div class="flujo-icon">🦺</div>
            <h3>Dotaciones</h3>
            <p>
              Registro y seguimiento de entregas de dotación.
            </p>
          </div>

        </div>

        <div class="dashboard-footer-title">
          Beneficios del Gestor
        </div>

        <div class="beneficios-bar">

          <div class="beneficio-item">
            <span>📂</span>
            <div>
              <strong>Centralizado</strong>
              <p>Toda la documentación en un solo lugar.</p>
            </div>
          </div>

          <div class="beneficio-item">
            <span>⚡</span>
            <div>
              <strong>Ágil</strong>
              <p>Procesos documentales más rápidos.</p>
            </div>
          </div>

          <div class="beneficio-item">
            <span>🔒</span>
            <div>
              <strong>Seguro</strong>
              <p>Información protegida y controlada.</p>
            </div>
          </div>

          <div class="beneficio-item">
            <span>🌱</span>
            <div>
              <strong>Sostenible</strong>
              <p>Reducción del uso de papel.</p>
            </div>
          </div>

        </div>

        <div class="recomendaciones">

          <h3>Recomendaciones</h3>

          <ul>
            <li>Verifique la información antes de guardar.</li>
            <li>Adjunte documentos legibles.</li>
            <li>Revise las firmas digitales.</li>
            <li>Mantenga la documentación actualizada.</li>
          </ul>

        </div>

        <a href="#"
          target="_blank"
          class="sitio-web">

          <div class="web-icon">🌐</div>

          <div>
            <h3>Sitio web institucional</h3>
            <p>
              Conoce Mas Acerca De Jardines De San Nicolas S.A.S
            </p>
          </div>

        </a>

      </section>
    </main>
  </div>

  <script src="../JS/ui-common.js"></script>
  <script src="../JS/Script.js"></script>
  <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

  <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
