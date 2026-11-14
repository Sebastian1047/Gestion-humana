<?php

// Redirige la raíz de la app a la pantalla de login.
//
// Necesario porque las rutas relativas de View/login.php (../CSS/...,
// ../Controller/...) solo resuelven bien si el navegador llega ahí con la
// URL COMPLETA (.../View/login.php). Antes, web.config servía
// "View/login.php" directo como documento predeterminado — eso mantiene la
// URL del navegador en la raíz de la app sin cambiarla, así que esas rutas
// relativas se resolvían contra la raíz de la app en vez de contra View/, y
// se escapaban un nivel de más. Esto es invisible cuando la app vive en la
// raíz de su propio sitio IIS (ahí "un nivel arriba" no tiene a dónde ir),
// pero se rompe en cuanto la app vive en una subcarpeta de otro sitio (ej.
// /GestorDocumentosJN/ dentro de "Default Web Site", junto a
// FlowerLogixProduccion).
//
// dirname($_SERVER['SCRIPT_NAME']) da la carpeta de la app tal cual la ve
// el navegador (ej. "/GestorDocumentosJN"), sin importar bajo qué alias/
// subcarpeta esté publicada — funciona igual si algún día vive en la raíz
// de su propio sitio (dirname da "/", y la ruta final queda "/View/login.php").
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

header('Location: ' . $base . '/View/login.php');
exit;
