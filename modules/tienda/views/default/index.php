<?php
// modules/tienda/views/default/index.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tienda - Planealo';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tienda-default-index">
    <div class="container-fluid">
        <!-- Header del Marketplace -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="jumbotron bg-gradient-tienda text-white rounded">
                    <div class="container py-5">
                        <h1 class="display-4">🛍️ Marketplace Deportivo</h1>
                        <p class="lead">Encuentra todo lo que necesitas para el deporte en un solo lugar</p>
                        <hr class="my-4 bg-white">
                        <p>Equipamiento, nutrición, servicios y mucho más de emprendedores locales</p>
                        <a class="btn btn-light btn-lg" href="<?= Url::to(['/tienda/marketplace']) ?>" role="button">
                            Explorar Productos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-store fa-2x text-primary mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Tiendas Activas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-box fa-2x text-success mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Productos Disponibles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-warning mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Vendedores Registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-2x text-info mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Ventas Realizadas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Principales -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Comprar Productos
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>Explora nuestro catálogo de productos y servicios deportivos.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i> Equipamiento deportivo</li>
                            <li><i class="fas fa-check text-success me-2"></i> Suplementos nutricionales</li>
                            <li><i class="fas fa-check text-success me-2"></i> Servicios de entrenamiento</li>
                            <li><i class="fas fa-check text-success me-2"></i> Y mucho más...</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="<?= Url::to(['/tienda/marketplace']) ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-store me-2"></i> Ir al Marketplace
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-store me-2"></i>
                            Vender Productos
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>¿Tienes productos o servicios para ofrecer? Únete a nuestra comunidad de vendedores.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i> Llega a toda la comunidad deportiva</li>
                            <li><i class="fas fa-check text-success me-2"></i> Gestión fácil de productos</li>
                            <li><i class="fas fa-check text-success me-2"></i> Pagos seguros</li>
                            <li><i class="fas fa-check text-success me-2"></i> Soporte técnico</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="<?= Url::to(['/tienda/default/registro-vendedor']) ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Registrarse como Vendedor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">💡 ¿Cómo funciona?</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-search fa-2x"></i>
                                    </div>
                                    <h5>1. Explora</h5>
                                    <p class="text-muted">Encuentra productos y servicios en nuestro marketplace</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                    <h5>2. Compra</h5>
                                    <p class="text-muted">Realiza tus compras de forma segura</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-truck fa-2x"></i>
                                    </div>
                                    <h5>3. Recibe</h5>
                                    <p class="text-muted">Recibe tus productos o servicios</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-tienda {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.jumbotron {
    background-size: cover;
    position: relative;
}
</style>