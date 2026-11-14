<?php
/**
 * Partial: acceso al módulo "Papelera" (2026-09-26).
 *
 * A diferencia de GestorDocumentosJS (donde solo una persona puede verla),
 * en JL la Papelera es de acceso general: cualquier empleado logueado ve
 * este ícono 🗑️ junto a su nombre. Sigue sin tener entrada en el sidebar a
 * propósito — es un acceso discreto, no un módulo más del menú.
 *
 * Incluir dentro de <div style="display:flex;align-items:center;"> junto al
 * <div class="user">. Estilo con <style> embebido (no en un .css externo)
 * porque este partial se incluye en vistas que cargan hojas de estilo
 * distintas — así el ícono luce igual sin depender de cuál esté cargada.
 */
if (isset($_SESSION['user'])):
?>
<style>
    .icono-papelera-header {
        display: inline-block;
        line-height: 1;
        font-size: 1.4rem;
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 8px;
        vertical-align: middle;
        margin-right: 14px;
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .icono-papelera-header:hover {
        background: #f1f5f9;
        transform: scale(1.08);
    }
</style>
<a href="Papelera.php" class="icono-papelera-header" title="Papelera de documentos">🗑️</a>
<?php endif; ?>
