<?php
require_once __DIR__ . '/../Config/demo.php';
demoSession();
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || ($_GET['action'] ?? '') !== 'listar') demoBlocked();
demoJson(demoData()['equivalencias']);
