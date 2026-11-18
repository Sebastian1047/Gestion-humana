document.addEventListener('DOMContentLoaded', function () {
  const banner = document.createElement('div');
  banner.id = 'demo-print-banner';
  banner.textContent = ['/ControlCuatrimestral.php', '/NuevaDotacion.php'].some(function (p) { return location.pathname.endsWith(p); })
    ? 'SIMULADOR DEMO | Datos ficticios | Movimientos de prueba guardados solo en este navegador. No use datos personales reales.'
    : 'DEMOSTRACION | Datos ficticios | Guardar, eliminar y subir archivos estan deshabilitados. No ingrese datos personales.';
  banner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#fff1cf;color:#543900;padding:10px 16px;font:13px Arial;text-align:center;border-top:1px solid #dabd79';
  document.body.appendChild(banner);
  // El aviso de la DEMO es informativo en pantalla, no parte de los comprobantes PDF.
  const printStyle = document.createElement('style');
  printStyle.textContent = '@media print { #demo-print-banner { display: none !important; } body { padding-bottom: 0 !important; } }';
  document.head.appendChild(printStyle);
  document.body.style.paddingBottom = '52px';
  if (location.pathname.endsWith('/login.php')) {
    const subtitle = document.querySelector('.form-subtitulo');
    if (subtitle) subtitle.textContent = 'Acceso de demostracion: codigo DEMO y contrasena DEMO. No use credenciales reales.';
    document.getElementById('username').value = 'DEMO';
    document.getElementById('password').value = 'DEMO';
  }
});
