document.addEventListener('DOMContentLoaded', function() {
    // Referencia al campo de precio
    const precioInput = document.getElementById('precio');
    
    if (precioInput) {
        precioInput.addEventListener('blur', function() {
            formatPrice(this);
        });
    }
});

// Función para validar números con máximo 2 decimales
function isNumberKeyWithDecimal(evt, input) {
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    const value = input.value;
    
    // Permitir números
    if (charCode >= 48 && charCode <= 57) {
        // Verificar si ya hay punto/coma decimal
        const decimalSeparator = value.indexOf(',') !== -1 ? ',' : (value.indexOf('.') !== -1 ? '.' : null);
        
        if (decimalSeparator !== null) {
            // Contar dígitos después del separador decimal
            const parts = value.split(decimalSeparator);
            if (parts[1] && parts[1].length >= 2) {
                // Ya hay 2 decimales, no permitir más dígitos
                if (getCaretPosition(input) > value.indexOf(decimalSeparator)) {
                    evt.preventDefault();
                    return false;
                }
            }
        }
        return true;
    }
    
    // Permitir punto o coma (una sola vez)
    if ((charCode === 44 || charCode === 46) && 
        value.indexOf(',') === -1 && 
        value.indexOf('.') === -1) {
        return true;
    }
    
    // Permitir teclas de control (backspace, delete, flechas, etc.)
    if (charCode < 32 || 
       (charCode >= 37 && charCode <= 40)) {
        return true;
    }
    
    evt.preventDefault();
    return false;
}

// Función para obtener la posición del cursor en el input
function getCaretPosition(input) {
    if (input.selectionStart !== undefined) {
        return input.selectionStart;
    }
    return 0;
}

// Función para formatear precio
function formatPrice(input) {
    // Reemplazar punto por coma si es necesario
    let value = input.value.replace('.', ',');
    
    // Si tiene decimales, asegurarse de que tenga máximo 2
    if (value.includes(',')) {
        const parts = value.split(',');
        if (parts[1].length > 2) {
            parts[1] = parts[1].substring(0, 2);
            value = parts.join(',');
        }
    }
    
    input.value = value;
}