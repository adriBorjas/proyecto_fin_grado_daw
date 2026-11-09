<?php include 'includes/header.php'; ?>

<section class="hero">
    <div class="container">
        <h2>Bienvenido a nuestra clínica dental</h2>
        <div class="hero-content">
            <h1>¿Quieres sacar una cita? ¡Es muy fácil!</h1>
            <p>Reserva tu cita en nuestra clínica dental con unos pocos clics.</p>
            <a href="<?php echo SITE_URL; ?>/pages/appointments.php" class="btn-appointment">
                <i class="far fa-calendar-alt"></i>
                <span>Reservar cita ahora</span>
            </a>
        </div>
    </div>
</section>

<section class="carousel">
    <div class="container">
        <h2>Nuestras instalaciones</h2>
        <div class="carousel-container">
            <div class="carousel-slides">
                <div class="slide"><img src="assets/img/dental_office_1.jpg" alt="Gabinete 1"></div>
                <div class="slide"><img src="assets/img/dental_office_3.jpg" alt="Gabinete 3"></div>
                <div class="slide"><img src="assets/img/waiting_room.jpg" alt="Sala de Espera"></div>
                <div class="slide"><img src="assets/img/dental_office_4.jpg" alt="Gabinete 4"></div>
            </div>
            <button class="carousel-prev">&#10094;</button>
            <button class="carousel-next">&#10095;</button>
        </div>
    </div>
</section>

<section class="tratamientos">
    <div class="container">
        <h2>Nuestros tratamientos</h2>
        
        <div class="tratamiento-item">
            <div class="tratamiento-img">
                <img src="assets/img/dental_office_2.jpg" alt="Gabinetes Dentales">
            </div>
            <div class="tratamiento-info">
                <h3>Gabinetes profesionales</h3>
                <p>Nuestros gabinetes dentales están diseñados para ofrecer máxima comodidad durante los tratamientos. Ambientes modernos, atractivos y relajantes que harán que su visita al dentista sea una experiencia agradable.</p>
            </div>
        </div>
        
        <div class="tratamiento-item reverse">
            <div class="tratamiento-info">
                <h3>Brackets e Invisalign</h3>
                <p>Ofrecemos tratamientos de ortodoncia con brackets tradicionales e Invisalign, adaptándonos a las necesidades de cada paciente. Nuestros especialistas te ayudarán a elegir el método más adecuado para conseguir esa sonrisa perfecta.</p>
            </div>
            <div class="tratamiento-img">
                <img src="assets/img/brackets_in_desk.jpg" alt="Brackets e Invisalign">
            </div>
        </div>
        
        <div class="tratamiento-item">
            <div class="tratamiento-img">
                <img src="assets/img/dental_instruments.jpg" alt="Instrumentos Dentales">
            </div>
            <div class="tratamiento-info">
                <h3>Instrumentos de última tecnología</h3>
                <p>Trabajamos con los mejores y más avanzados instrumentos dentales del mercado. Nuestra clínica invierte constantemente en tecnología de vanguardia para garantizar tratamientos precisos, seguros y con resultados óptimos.</p>
            </div>
        </div>
        
        <div class="tratamiento-item reverse">
            <div class="tratamiento-info">
                <h3>Implantes Dentales</h3>
                <p>Los implantes dentales son una solución permanente para dientes perdidos, proporcionando una apariencia y funcionalidad natural.</p>
            </div>
            <div class="tratamiento-img">
                <img src="assets/img/smile.jpg" alt="Implantes Dentales">
            </div>
        </div>
    </div>
</section>

<script src="assets/js/carousel.js"></script>

<?php if (isset($_GET['login_success']) && $_GET['login_success'] === 'true'): ?>
<script src="<?php echo SITE_URL; ?>/assets/js/pending-products.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>