<?php
/**
 * Partial: sidebar del menú lateral.
 *
 * Requiere que la vista ya haya definido, ANTES de este include:
 *   $pagina_actual = basename($_SERVER['PHP_SELF']);
 *
 * Variables opcionales que la vista puede definir antes del include:
 *   $sidebar_subtitulo (string, opcional)
 *     - Si se define, se muestra como <h2> dentro del bloque .logo.
 */
$paginas_preingreso = ['preingreso.php', 'entrevistaPreSele.php'];
?>
        <aside class="sidebar">
            <div class="logo">
                <a href="dashboard.php"><img src="../IMG/LOGO.png" alt="Logo" /></a>
                <?php if (!empty($sidebar_subtitulo)): ?>
                <h2><?= htmlspecialchars($sidebar_subtitulo, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php endif; ?>
            </div>
            <nav class="menu">
                <ul>
                    <li><a href="dashboard.php" class="<?= ($pagina_actual == 'dashboard.php') ? 'active' : '' ?>">🏠 Inicio</a></li>
                    <li><a href="preingreso.php" class="<?= in_array($pagina_actual, $paginas_preingreso) ? 'active' : '' ?>">📋 Pre-Ingreso</a></li>
                    <li><a href="postingreso.php" class="<?= ($pagina_actual == 'postingreso.php') ? 'active' : '' ?>">📝 Post-Ingreso</a></li>
                    <li><a href="Dotacion.php" class="<?= in_array($pagina_actual, ['Dotacion.php', 'NuevaDotacion.php', 'editarDotacion.php', 'Verdotacion.php', 'TallasExistencias.php', 'EquivalenciasBodega.php']) ? 'active' : '' ?>">🦺 Dotaciones</a></li>
                    <li>
                        <a href="../Controller/logout.php" class="btn-logout" id="btnLogout"> 🚪 Cerrar sesión</a>

                    </li>
                </ul>
            </nav>
        </aside>
