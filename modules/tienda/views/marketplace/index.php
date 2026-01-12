<?php
// modules/tienda/views/marketplace/index.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Marketplace Deportivo - Planealo';
$this->params['breadcrumbs'][] = ['label' => 'Tienda', 'url' => ['/tienda']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tienda-marketplace-index">
    <div class="container-fluid">
        <!-- Header del Marketplace -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="marketplace-header bg-light rounded p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-5 mb-2">🛍️ Marketplace</h1>
                            <p class="lead mb-0">Descubre productos y servicios de la comunidad deportiva</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= Url::to(['/tienda/default/registro-vendedor']) ?>" class="btn btn-success">
                                <i class="fas fa-store me-2"></i> Vender Productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" placeholder="Buscar productos, servicios, categorías...">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select form-select-lg">
                                    <option selected>Todas las categorías</option>
                                    <option value="1">Equipamiento Deportivo</option>
                                    <option value="2">Nutrición</option>
                                    <option value="3">Servicios</option>
                                    <option value="4">Ropa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filtros</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filtro por Categoría -->
                        <div class="mb-4">
                            <h6>Categorías</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat1">
                                <label class="form-check-label" for="cat1">
                                    Equipamiento Deportivo
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat2">
                                <label class="form-check-label" for="cat2">
                                    Nutrición
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat3">
                                <label class="form-check-label" for="cat3">
                                    Servicios
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat4">
                                <label class="form-check-label" for="cat4">
                                    Ropa Deportiva
                                </label>
                            </div>
                        </div>

                        <!-- Filtro por Precio -->
                        <div class="mb-4">
                            <h6>Rango de Precio</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" placeholder="Mín">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" placeholder="Máx">
                                </div>
                            </div>
                        </div>

                        <!-- Filtro por Ubicación -->
                        <div class="mb-4">
                            <h6>Ubicación</h6>
                            <select class="form-select">
                                <option selected>Todas las ubicaciones</option>
                                <option>Caracas</option>
                                <option>Valencia</option>
                                <option>Maracaibo</option>
                                <option>Barquisimeto</option>
                            </select>
                        </div>

                        <button class="btn btn-primary w-100">
                            <i class="fas fa-sync me-2"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Categorías Destacadas -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Categorías</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Equipamiento
                                <span class="badge bg-primary rounded-pill">0</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Nutrición
                                <span class="badge bg-primary rounded-pill">0</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Servicios
                                <span class="badge bg-primary rounded-pill">0</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Ropa
                                <span class="badge bg-primary rounded-pill">0</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Productos -->
            <div class="col-md-9">
                <!-- Barra de Herramientas -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="text-muted">Mostrando 0 productos</span>
                    </div>
                    <div>
                        <select class="form-select">
                            <option selected>Ordenar por: Más recientes</option>
                            <option>Precio: Menor a Mayor</option>
                            <option>Precio: Mayor a Menor</option>
                            <option>Mejor valorados</option>
                        </select>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="row" id="productos-grid">
                    <!-- Mensaje temporal - Sin productos -->
                    <div class="col-12">
                        <div class="card text-center py-5">
                            <div class="card-body">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h3 class="text-muted">No hay productos disponibles</h3>
                                <p class="text-muted mb-4">Aún no hay productos en el marketplace.</p>
                                <a href="<?= Url::to(['/tienda/default/registro-vendedor']) ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-store me-2"></i> Sé el primero en vender
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Anterior</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>