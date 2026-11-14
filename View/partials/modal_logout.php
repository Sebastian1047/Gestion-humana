    <!-- ══ MODAL CERRAR SESIÓN (fuera del sidebar) ══ -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <div class="modal-icon">&#x2715;</div>
            <h2 class="modal-title">¿Cerrar sesión?</h2>
            <p class="modal-text">¿Estás seguro que quieres cerrar sesión?</p>
            <div class="modal-buttons">
                <a href="../Controller/logout.php" class="btn btn-confirm">Sí, cerrar sesión</a>
                <button class="btn btn-cancel" id="btnCancel">Cancelar</button>
            </div>
        </div>
    </div>


    <script>
        // ── Modal cerrar sesión ──────────────────────────────
        (function() {
            var btnLogout = document.getElementById('btnLogout');
            var btnCancel = document.getElementById('btnCancel');
            var modal = document.getElementById('modalOverlay');

            if (btnLogout && modal) {
                btnLogout.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.classList.add('active');
                });
            }

            if (btnCancel && modal) {
                btnCancel.addEventListener('click', function() {
                    modal.classList.remove('active');
                });
            }

            // Cerrar al hacer clic fuera del box
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) modal.classList.remove('active');
                });
            }
        })();
    </script>
