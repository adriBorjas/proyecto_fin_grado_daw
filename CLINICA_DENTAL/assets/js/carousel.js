document.addEventListener('DOMContentLoaded', function() {
    // Variables para el carrusel
    const slides = document.querySelectorAll('.slide');
    const prevButton = document.querySelector('.carousel-prev');
    const nextButton = document.querySelector('.carousel-next');
    let currentSlide = 0;
    
    // Función para mostrar una diapositiva específica
    function showSlide(index) {
        // Ocultar todas las diapositivas
        slides.forEach(slide => {
            slide.style.display = 'none';
        });
        
        // Mostrar la diapositiva actual
        slides[index].style.display = 'block';
    }
    
    // Función para ir a la siguiente diapositiva
    function nextSlide() {
        currentSlide++;
        if (currentSlide >= slides.length) {
            currentSlide = 0;
        }
        showSlide(currentSlide);
    }
    
    // Función para ir a la diapositiva anterior
    function prevSlide() {
        currentSlide--;
        if (currentSlide < 0) {
            currentSlide = slides.length - 1;
        }
        showSlide(currentSlide);
    }
    
    // Asignar eventos a los botones
    if (prevButton && nextButton) {
        prevButton.addEventListener('click', prevSlide);
        nextButton.addEventListener('click', nextSlide);
    }
    
    // Mostrar la primera diapositiva al cargar
    showSlide(currentSlide);
    
    // Avanzar automáticamente cada 5 segundos
    setInterval(nextSlide, 5000);
});