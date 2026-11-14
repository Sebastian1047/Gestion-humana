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
    <title>PostIngreso – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/layout.css" />
    <link rel="stylesheet" href="../CSS/componentes.css" />
    <link rel="stylesheet" href="../CSS/postingreso.css" />
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

                <div class="hero-postingreso">

                    <h1>📝 Post-Ingreso</h1>

                    <h2>Documentación laboral</h2>

                    <p>
                        Gestiona los documentos posteriores a la vinculación
                        del colaborador y mantén actualizada la información
                        administrativa y laboral.
                    </p>

                </div>

                <div class="sin-documentos" style="text-align:center;color:#718096;padding:60px 0;">
                    Aún no hay documentos configurados para este proceso.
                </div>

            </section>
        </main>
    </div>

    <script src="../JS/Script.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
