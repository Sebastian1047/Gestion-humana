    <!-- ══ SCRIPT HAMBURGER ══ -->
    <script>
        (function() {
            var btn = document.getElementById('hamburger');
            var sidebar = document.querySelector('.sidebar');
            var overlay = document.getElementById('sidebarOverlay');

            function abrir() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                btn.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function cerrar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                btn.classList.remove('open');
                document.body.style.overflow = '';
            }

            btn.addEventListener('click', function() {
                sidebar.classList.contains('open') ? cerrar() : abrir();
            });

            overlay.addEventListener('click', cerrar);

            document.querySelectorAll('.menu a').forEach(function(a) {
                a.addEventListener('click', cerrar);
            });
        })();
    </script>
