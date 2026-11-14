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
    <title>Pre-Ingreso – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/layout.css" />
    <link rel="stylesheet" href="../CSS/componentes.css" />
    <link rel="stylesheet" href="../CSS/preingreso.css" />

    <style>
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
    </style>
</head>

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

                <div class="hero-preingreso">

                    <h1>📋 Pre-Ingreso</h1>

                    <h2>Proceso de vinculación</h2>

                    <p>
                        Gestiona la documentación requerida para el ingreso de nuevos
                        colaboradores de manera organizada, segura y eficiente.
                    </p>

                </div>

                <div class="documentos-grid">

                    <a href="entrevistaPreSele.php" class="documento-card">

                        <div class="documento-icono">
                            👤
                        </div>

                        <h3>Entrevista de Selección de Personal</h3>

                        <p>
                            Registro inicial y evaluación del candidato.
                        </p>

                        <span>Abrir documento →</span>

                    </a>

                </div>

            </section>

        </main>
    </div>

    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Script.js"></script>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
