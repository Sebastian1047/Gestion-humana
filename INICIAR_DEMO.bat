@echo off
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
  if exist C:\xampp\php\php.exe (
    start "" http://127.0.0.1:8095/View/login.php
    C:\xampp\php\php.exe -S 127.0.0.1:8095 -t .
  ) else (
    echo Se requiere PHP 8 o superior. Puede usar XAMPP o agregar PHP al PATH.
    pause
  )
) else (
  start "" http://127.0.0.1:8095/View/login.php
  php -S 127.0.0.1:8095 -t .
)
