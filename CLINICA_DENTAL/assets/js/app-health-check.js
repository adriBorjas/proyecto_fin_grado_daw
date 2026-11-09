/**
 * Script para verificar el estado de la aplicación
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar la conectividad a la API
    function checkApiHealth() {
        fetch(`${SITE_URL}/api/health-check.php`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('API no responde correctamente');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    console.error('La API reporta problemas:', data.message);
                    showErrorNotification('Hay problemas de conectividad con el servidor. Algunas funciones podrían no estar disponibles.');
                }
            })
            .catch(error => {
                console.error('Error al verificar estado de la API:', error);
                // No mostrar notificación al usuario para no alarmar
            });
    }
    
    // Solo verificar si hay una variable global SITE_URL disponible
    if (typeof SITE_URL !== 'undefined') {
        // Realizar la verificación con un retraso para no afectar la carga inicial
        setTimeout(checkApiHealth, 2000);
    }
});