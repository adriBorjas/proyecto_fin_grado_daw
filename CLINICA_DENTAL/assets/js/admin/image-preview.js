document.addEventListener('DOMContentLoaded', function() {
    // Vista previa de imagen
    const imageInput = document.getElementById('imagen');
    const imagePreviewContainer = document.getElementById('image-preview');
    
    if (imageInput && imagePreviewContainer) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Verificar tamaño (máximo 2MB)
                const maxSize = 2 * 1024 * 1024; // 2MB en bytes
                if (file.size > maxSize) {
                    alert('La imagen es demasiado grande. El tamaño máximo es 2MB.');
                    this.value = '';
                    return;
                }
                
                // Verificar tipo de archivo
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                if (!validTypes.includes(file.type)) {
                    alert('Tipo de archivo no válido. Por favor, sube una imagen JPG, PNG, GIF, WEBP o AVIF.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreviewContainer.innerHTML = `
                        <h4>Vista previa de la imagen</h4>
                        <img src="${e.target.result}" alt="Vista previa">
                    `;
                    imagePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.innerHTML = '';
                imagePreviewContainer.style.display = 'none';
            }
        });
    }
});