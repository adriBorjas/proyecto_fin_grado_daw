document.addEventListener('DOMContentLoaded', function() {
    // Verificar integridad de la sesión
    function checkSessionIntegrity() {
        fetch(SITE_URL + '/api/check_user.php')
            .then(response => response.json())
            .then(data => {
                if (!data.valid_session) {
                    // Mostrar mensaje al usuario
                    showSessionErrorModal();
                }
            })
            .catch(error => {
                console.error('Error al verificar sesión:', error);
            });
    }
    
    // Mostrar modal de error de sesión
    function showSessionErrorModal() {
        const modal = document.createElement('div');
        modal.className = 'session-error-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Problema de sesión</h3>
                </div>
                <div class="modal-body">
                    <p>Hemos detectado un problema con tu sesión. Por favor, vuelve a iniciar sesión para continuar.</p>
                    <div class="confirmation-buttons">
                        <a href="${SITE_URL}/pages/auth.php?action=login&session_error=true" class="btn btn-primary">Iniciar sesión</a>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    // Si el usuario está logueado (verificar mediante una variable global establecida desde PHP)
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
        checkSessionIntegrity();
    }
});