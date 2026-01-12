<?php
/** @var yii\web\View $this */
/** @var bool $isAuthenticated */

$this->title = 'Sistema GED - Gestión Escuelas Deportivas';
$this->params['breadcrumbs'] = [];

// ✅ DETECCIÓN MEJORADA - USAR DIRECTAMENTE isGuest
$isUserAuthenticated = !Yii::$app->user->isGuest;
$currentRoute = Yii::$app->controller->route;

// ✅ VERIFICAR SI YA ESTAMOS EN LOGIN PARA EVITAR BUCLE
if ($currentRoute === 'site/login' && $isUserAuthenticated) {
    // Si ya está autenticado y trata de acceder a login, redirigir al index
    Yii::$app->response->redirect(['site/index'])->send();
    return;
}

// ✅ URL BASE PARA MARKETPLACE - VERIFICAR QUE EXISTA
$marketplaceUrl = Yii::$app->urlManager->createUrl(['/tienda/marketplace/index']);
$hasMarketplace = true; // Asumir que existe, se puede verificar mejor

// ✅ MENÚ MARKETPLACE - SOLO MOSTRAR SI EXISTE EL MÓDULO
try {
    $testMenu = \app\components\MenuWidget::widget([
        'parentId' => 177,
        'options' => ['class' => 'nav justify-content-center marketplace-nav']
    ]);
    $showMarketplaceMenu = !empty($testMenu);
} catch (\Exception $e) {
    $showMarketplaceMenu = false;
    Yii::warning('MenuWidget error: ' . $e->getMessage());
}
?>

<div class="site-index landing-page">
    <!-- Carrusel Hero -->
    <section id="hero-carousel" class="carousel-hero">
        <div id="carouselHero" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselHero" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#carouselHero" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#carouselHero" data-bs-slide-to="2"></button>
            </div>
            
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="<?= Yii::getAlias('@web') ?>/img/Carrusel/slide1.jpg" 
                         alt="Gestión Escuelas Deportivas"
                         onerror="this.src='https://images.unsplash.com/photo-1552674605-db6ffd8facb5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'">
                    <div class="carousel-caption d-none d-md-block">
                        <h2 class="display-4">Sistema GED</h2>
                        <p class="lead">Gestión integral de escuelas deportivas</p>
                        <a href="#productos-mas-vendidos" class="btn btn-primary btn-lg mt-3">Ver Productos</a>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <img src="<?= Yii::getAlias('@web') ?>/img/Carrusel/slide2.png" 
                         alt="Marketplace Deportivo"
                         onerror="this.src='https://images.unsplash.com/photo-1519861531473-920034658307?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'">
                    <div class="carousel-caption d-none d-md-block">
                        <h2 class="display-4">Marketplace Deportivo</h2>
                        <p class="lead">Los mejores productos para atletas</p>
                        <a href="#tiendas-patrocinadas" class="btn btn-success btn-lg mt-3">Tiendas Destacadas</a>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <img src="<?= Yii::getAlias('@web') ?>/img/Carrusel/slide3.png" 
                         alt="Productos Más Vendidos"
                         onerror="this.src='https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'">
                    <div class="carousel-caption d-none d-md-block">
                        <h2 class="display-4">Productos Destacados</h2>
                        <p class="lead">Lo más vendido en nuestra comunidad</p>
                        <a href="#productos-mas-vendidos" class="btn btn-warning btn-lg mt-3">Más Vendidos</a>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselHero" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselHero" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </section>

    <!-- ✅ MENÚ MARKETPLACE SOLO SI EXISTE -->
    <?php if ($showMarketplaceMenu && $hasMarketplace): ?>
    <section id="marketplace-menu-landing" class="marketplace-menu-section py-3 bg-light">
        <div class="container">
            <h3 class="text-center mb-3">Marketplace Deportivo</h3>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?= \app\components\MenuWidget::widget([
                        'parentId' => 177,
                        'options' => [
                            'class' => 'nav justify-content-center marketplace-nav',
                            'mobileMode' => false
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="hero-section text-center py-5">
        <div class="container">
            <img src="<?= Yii::getAlias('@web') ?>/img/logos/logoGed.png" 
                 alt="GED Logo" 
                 class="mb-4 ged-logo"
                 id="ged-main-logo">
            
            <h1 class="display-4">Gestión Escuelas Deportivas</h1>
            <p class="lead">Plataforma tecnológica para la administración deportiva</p>
            
            <hr class="my-4">
            
            <!-- ✅ ACCIONES CON PREVENCIÓN DE BUCLE -->
            <div class="mt-4 landing-actions">
                <?php if (!$isUserAuthenticated): ?>
                    <!-- Usuario NO autenticado -->
                    <?php if ($currentRoute !== 'site/login'): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['/site/login']) ?>" 
                       class="btn btn-primary btn-lg mx-2 landing-btn">
                        Iniciar Sesión
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($currentRoute !== 'site/signup'): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['/site/signup']) ?>" 
                       class="btn btn-outline-primary btn-lg mx-2 landing-btn">
                        Registrarse
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Usuario autenticado -->
                    <a href="<?= Yii::$app->urlManager->createUrl(['/ged/default/index']) ?>" 
                       class="btn btn-success btn-lg mx-2 landing-btn"
                       id="btn-acceder-sistema">
                        Acceder al Sistema
                    </a>
                    
                    <a href="<?= Yii::$app->urlManager->createUrl(['/site/mi-cuenta']) ?>" 
                       class="btn btn-info btn-lg mx-2 landing-btn">
                        Mi Cuenta
                    </a>
                <?php endif; ?>
                
                <!-- ✅ MARKETPLACE SOLO SI EXISTE -->
                <?php if ($hasMarketplace): ?>
                <a href="<?= $marketplaceUrl ?>" 
                   class="btn btn-warning btn-lg mx-2 landing-btn"
                   id="btn-marketplace">
                    Marketplace Deportivo
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Banner de Tiendas Patrocinadas -->
    <section id="tiendas-patrocinadas" class="tiendas-patrocinadas-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div id="banner-tiendas-patrocinadas"></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Características del Sistema -->
    <div class="features-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 feature-card">
                        <div class="card-body">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-lock fa-2x"></i>
                            </div>
                            <h5 class="feature-title">Acceso Seguro</h5>
                            <p class="feature-description">Autenticación protegida y encriptada</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 feature-card">
                        <div class="card-body">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                            <h5 class="feature-title">Marketplace</h5>
                            <p class="feature-description">Productos y servicios deportivos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 feature-card">
                        <div class="card-body">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h5 class="feature-title">Sistema Privado</h5>
                            <p class="feature-description">Acceso restringido a usuarios autorizados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Productos Más Vendidos -->
    <section id="productos-mas-vendidos" class="productos-mas-vendidos py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-primary mb-3">🏆 Productos Más Vendidos</h2>
                <p class="lead text-muted">Los productos preferidos por nuestra comunidad</p>
            </div>
            
            <div class="row g-4">
                <!-- Vestimenta -->
                <div class="col-lg-3 col-md-6">
                    <div class="categoria-card h-100">
                        <div class="categoria-header vestimenta-bg">
                            <div class="categoria-icon">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                            <h3 class="categoria-title">Vestimenta</h3>
                            <span class="categoria-badge">3 productos</span>
                        </div>
                        <div class="categoria-body" id="productos-vestimenta">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Alimentación -->
                <div class="col-lg-3 col-md-6">
                    <div class="categoria-card h-100">
                        <div class="categoria-header alimentacion-bg">
                            <div class="categoria-icon">
                                <i class="bi bi-egg-fried"></i>
                            </div>
                            <h3 class="categoria-title">Alimentación</h3>
                            <span class="categoria-badge">3 productos</span>
                        </div>
                        <div class="categoria-body" id="productos-alimentacion">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Implementos Deportivos -->
                <div class="col-lg-3 col-md-6">
                    <div class="categoria-card h-100">
                        <div class="categoria-header implementos-bg">
                            <div class="categoria-icon">
                                <i class="bi bi-bicycle"></i>
                            </div>
                            <h3 class="categoria-title">Implementos Deportivos</h3>
                            <span class="categoria-badge">3 productos</span>
                        </div>
                        <div class="categoria-body" id="productos-implementos-deportivos">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Suplementos -->
                <div class="col-lg-3 col-md-6">
                    <div class="categoria-card h-100">
                        <div class="categoria-header suplementos-bg">
                            <div class="categoria-icon">
                                <i class="bi bi-capsule-pill"></i>
                            </div>
                            <h3 class="categoria-title">Suplementos</h3>
                            <span class="categoria-badge">3 productos</span>
                        </div>
                        <div class="categoria-body" id="productos-suplementos">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contador total -->
            <div class="text-center mt-5">
                <div class="total-vendidos-card">
                    <i class="bi bi-trophy-fill total-icon"></i>
                    <div class="total-info">
                        <h4 class="total-titulo">Total de Productos Vendidos</h4>
                        <div class="total-cantidad">
                            <span id="total-productos-vendidos">0</span>
                            <small class="text-muted">productos vendidos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Información adicional -->
    <?php if (!$isUserAuthenticated): ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card welcome-message">
                    <div class="card-body text-center">
                        <h4 class="card-title">¡Bienvenido al Sistema GED!</h4>
                        <p class="card-text">
                            Para acceder al sistema completo, por favor inicia sesión.
                        </p>
                        <div class="mt-3">
                            <?php if ($currentRoute !== 'site/login'): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['/site/login']) ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card welcome-message authenticated">
                    <div class="card-body text-center">
                        <h4 class="card-title">¡Hola de nuevo!</h4>
                        <p class="card-text">
                            Estás autenticado en el sistema GED.
                        </p>
                        <div class="mt-3">
                            <a href="<?= Yii::$app->urlManager->createUrl(['/ged/default/index']) ?>" 
                               class="btn btn-success btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Acceder al Sistema
                            </a>
                            <a href="<?= Yii::$app->urlManager->createUrl(['/site/mi-cuenta']) ?>" 
                               class="btn btn-info btn-lg ms-2">
                                <i class="fas fa-user-cog"></i> Mi Cuenta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="landing-footer py-4">
        <div class="container text-center">
            <p class="mb-2">
                <strong>Sistema GED</strong> &copy; <?= date('Y') ?> - Gestión Escuelas Deportivas
            </p>
            <p class="text-muted small mb-0">
                Plataforma tecnológica para la administración deportiva
            </p>
        </div>
    </div>
</div>

<!-- ✅ SCRIPT DE VERIFICACIÓN Y PREVENCIÓN DE BUCLE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 GED System - Página Index cargada correctamente');
    console.log('Usuario autenticado: <?= $isUserAuthenticated ? "Sí" : "No" ?>');
    console.log('Ruta actual: <?= $currentRoute ?>');
    
    // ✅ PREVENIR CLIC REPETIDO EN LOGIN SI YA ESTAMOS EN ESA PÁGINA
    const loginButtons = document.querySelectorAll('a[href*="login"]');
    loginButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (window.location.href.indexOf('login') > -1) {
                console.warn('⚠️ Ya estás en la página de login');
                e.preventDefault();
                return false;
            }
        });
    });
    
    // ✅ VERIFICAR MARKETPLACE
    const marketplaceBtn = document.getElementById('btn-marketplace');
    if (marketplaceBtn) {
        marketplaceBtn.addEventListener('click', function(e) {
            console.log('🛒 Intentando acceder al marketplace...');
            // Verificar si la URL existe
            fetch(this.href, { method: 'HEAD' })
                .then(response => {
                    if (!response.ok) {
                        console.error('❌ Marketplace no disponible');
                        e.preventDefault();
                        alert('El marketplace no está disponible en este momento.');
                    }
                })
                .catch(() => {
                    console.error('❌ Error de conexión al marketplace');
                    e.preventDefault();
                    alert('Error al acceder al marketplace.');
                });
        });
    }
});
</script>