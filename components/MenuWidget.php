<?php

namespace app\components;

use yii\base\Widget;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;
use Yii;

class MenuWidget extends Widget
{
    public $parentId = null;
    public $options = [];
    public $menuClass = 'navbar-nav';
    public $mobileMode = false;

    public function init()
    {
        parent::init();
        
        // ✅ DETECCIÓN SIMPLIFICADA
        $this->mobileMode = false;
        
        if (isset($this->options['mobileMode'])) {
            $this->mobileMode = (bool)$this->options['mobileMode'];
        }
        
        if (isset($this->options['class'])) {
            $this->menuClass = $this->options['class'];
        }
    }

    public function run()
    {
        try {
            // ✅ DEBUG VISIBLE
            echo '<!-- MenuWidget - parentId: ' . ($this->parentId ?: 'null') . ' -->' . "\n";
            
            $menuItems = $this->getMenuItems($this->parentId);
            
            if (empty($menuItems)) {
                echo '<!-- MenuWidget - No items, usando fallback -->' . "\n";
                return $this->renderFallbackMenu();
            }
            
            echo '<!-- MenuWidget - Items encontrados: ' . count($menuItems) . ' -->' . "\n";
            
            if ($this->mobileMode) {
                return $this->renderMenuForMobile($menuItems);
            }
            
            return $this->renderMenuForDesktop($menuItems);
        } catch (\Exception $e) {
            echo '<!-- MenuWidget ERROR: ' . htmlspecialchars($e->getMessage()) . ' -->' . "\n";
            return $this->renderFallbackMenu();
        }
    }

    /**
     * ✅ OBTENER ITEMS DEL MENÚ - CORREGIDO PARA POSTGRESQL
     */
    protected function getMenuItems($parentId = null)
    {
        try {
            $db = Yii::$app->db;
            if (!$db || $db->getIsActive() === false) {
                echo '<!-- MenuWidget - Base de datos no disponible -->' . "\n";
                return [];
            }
            
            // ✅ CONSULTA CORREGIDA PARA POSTGRESQL (ESCAPAR "order")
            $query = new Query();
            
            $query->select([
                'm.id', 
                'm.name', 
                'm.route', 
                'm.parent', 
                'm."order" as menu_order',
                'm.data'
            ])
            ->from('seguridad.menu m')
            ->where(['m.parent' => $parentId])
            ->orderBy('m."order" ASC');
            
            $items = $query->all();
            
            echo '<!-- MenuWidget - Consulta ejecutada. Items: ' . count($items) . ' -->' . "\n";
            
        } catch (\Exception $e) {
            echo '<!-- MenuWidget DB ERROR: ' . htmlspecialchars($e->getMessage()) . ' -->' . "\n";
            return [];
        }

        $menuItems = [];

        foreach ($items as $item) {
            echo '<!-- Procesando item: ID=' . $item['id'] . ', Nombre=' . $item['name'] . ', Ruta=' . ($item['route'] ?: 'null') . ' -->' . "\n";
            
            // ✅ VERIFICAR SI ES UN MENÚ PÚBLICO (MARKETPLACE)
            $isPublic = $this->isPublicMenuItem($item);
            
            if ($isPublic) {
                echo '<!-- Item es PÚBLICO -->' . "\n";
                $childItems = $this->getMenuItems($item['id']);
                
                $menuItem = [
                    'label' => $item['name'],
                    'url' => $item['route'] ? [$item['route']] : '#',
                    'items' => $childItems,
                    'visible' => true
                ];

                $menuItems[] = $menuItem;
                continue;
            }
            
            // ✅ PARA USUARIOS NO REGISTRADOS, SOLO MOSTRAR MENÚS PÚBLICOS
            if (Yii::$app->user->isGuest) {
                echo '<!-- Usuario es guest, omitiendo item no público -->' . "\n";
                continue;
            }
            
            // ✅ VERIFICAR PERMISOS RBAC PARA MENÚS NO PÚBLICOS (solo usuarios logueados)
            $hasPermission = $this->checkMenuItemPermission($item);
            
            if (!$hasPermission) {
                echo '<!-- Item SIN permiso -->' . "\n";
                continue;
            }

            $childItems = $this->getMenuItems($item['id']);
            
            $menuItem = [
                'label' => $item['name'],
                'url' => $item['route'] ? [$item['route']] : '#',
                'items' => $childItems,
                'visible' => true
            ];

            $menuItems[] = $menuItem;
        }

        return $menuItems;
    }

    /**
     * ✅ VERIFICAR SI UN ITEM DEL MENÚ ES PÚBLICO - MEJORADO
     */
    protected function isPublicMenuItem($item)
    {
        // IDs de menús públicos conocidos
        $publicMenuIds = [177];
        
        if (isset($item['id']) && in_array($item['id'], $publicMenuIds)) {
            return true;
        }
        
        // Verificar por nombre o ruta
        $itemName = strtolower($item['name'] ?? '');
        $itemRoute = $item['route'] ?? '';
        
        echo '<!-- isPublicMenuItem: Nombre="' . $itemName . '", Ruta="' . $itemRoute . '" -->' . "\n";
        
        // Si el nombre contiene palabras clave de marketplace
        $marketplaceKeywords = ['market', 'tienda', 'comercio', 'shop', 'store', 'marketplace'];
        foreach ($marketplaceKeywords as $keyword) {
            if (strpos($itemName, $keyword) !== false) {
                echo '<!-- Encontrada palabra clave marketplace: ' . $keyword . ' -->' . "\n";
                return true;
            }
        }
        
        // Si la ruta pertenece al módulo tienda o marketplace
        $publicRoutePatterns = ['tienda/', 'marketplace', 'shop/', 'store/', 'tienda/default', 'tienda/marketplace'];
        foreach ($publicRoutePatterns as $pattern) {
            if (strpos($itemRoute, $pattern) === 0 || strpos($itemRoute, $pattern) !== false) {
                echo '<!-- Ruta pública detectada por patrón: ' . $pattern . ' -->' . "\n";
                return true;
            }
        }
        
        // Verificar si la ruta es pública usando el método isPublicRoute
        if (!empty($itemRoute) && $itemRoute !== '#' && $this->isPublicRoute($itemRoute)) {
            echo '<!-- Ruta marcada como pública en isPublicRoute -->' . "\n";
            return true;
        }
        
        return false;
    }

    /**
     * ✅ VERIFICAR PERMISOS RBAC - SIMPLIFICADO
     */
    protected function checkMenuItemPermission($item)
    {
        // ✅ PRIMERO VERIFICAR SI ES UN MENÚ PÚBLICO
        if ($this->isPublicMenuItem($item)) {
            echo '<!-- checkMenuItemPermission: Item es público -->' . "\n";
            return true;
        }
        
        // Si no hay ruta definida, es un contenedor
        if (empty($item['route']) || $item['route'] == '#') {
            echo '<!-- checkMenuItemPermission: Es contenedor sin ruta -->' . "\n";
            return true;
        }

        try {
            $route = $item['route'];
            
            // ✅ VERIFICAR RUTAS PÚBLICAS
            if ($this->isPublicRoute($route)) {
                echo '<!-- checkMenuItemPermission: Ruta pública -->' . "\n";
                return true;
            }
            
            // ✅ SI ES USUARIO GUEST, NO PUEDE ACCEDER A RUTAS NO PÚBLICAS
            if (Yii::$app->user->isGuest) {
                echo '<!-- checkMenuItemPermission: Usuario guest, ruta no pública -->' . "\n";
                return false;
            }

            // ✅ VERIFICAR PERMISO CON RBAC
            if (Yii::$app->user->can($route)) {
                echo '<!-- checkMenuItemPermission: Tiene permiso RBAC -->' . "\n";
                return true;
            }
            
            // ✅ VERIFICAR POR PATRÓN
            $routeParts = explode('/', $route);
            if (count($routeParts) >= 2) {
                $modulePattern = $routeParts[0] . '/*';
                if (Yii::$app->user->can($modulePattern)) {
                    echo '<!-- checkMenuItemPermission: Tiene permiso por patrón módulo -->' . "\n";
                    return true;
                }
                
                if (count($routeParts) >= 2) {
                    $controllerPattern = $routeParts[0] . '/' . $routeParts[1] . '/*';
                    if (Yii::$app->user->can($controllerPattern)) {
                        echo '<!-- checkMenuItemPermission: Tiene permiso por patrón controlador -->' . "\n";
                        return true;
                    }
                }
            }

            // ✅ VERIFICAR ROLES DE ADMINISTRADOR
            $adminRoles = ['admin', 'administrator', 'superadmin'];
            foreach ($adminRoles as $role) {
                if (Yii::$app->user->can($role)) {
                    echo '<!-- checkMenuItemPermission: Tiene rol de administrador -->' . "\n";
                    return true;
                }
            }

            echo '<!-- checkMenuItemPermission: NO tiene permiso -->' . "\n";
            return false;

        } catch (\Exception $e) {
            echo '<!-- checkMenuItemPermission ERROR: ' . htmlspecialchars($e->getMessage()) . ' -->' . "\n";
            return false;
        }
    }

    /**
     * ✅ VERIFICAR SI UNA RUTA ES PÚBLICA - EXPANDIDO
     */
    protected function isPublicRoute($route)
    {
        $publicRoutes = [
            'site/index',
            'site/login',
            'site/logout',
            'site/error',
            'site/about',
            'site/contact',
            'site/signup',
            'site/request-password-reset',
            'site/reset-password',
            'admin/user/signup',
            'admin/user/request-password-reset', 
            'admin/user/reset-password',
            'ged/*',
            'site/*',
            
            // ✅ TODAS LAS RUTAS DEL MARKETPLACE Y TIENDA
            'tienda/*',
            'tienda/marketplace/*',
            'tienda/marketplace/index',
            'tienda/marketplace/buscar',
            'tienda/marketplace/categoria',
            'tienda/marketplace/producto',
            'tienda/marketplace/detalle',
            'tienda/default/*',
            'tienda/default/index',
            'tienda/default/registro-vendedor',
            'tienda/default/dashboard-vendedor',
            'tienda/default/carrito',
            'tienda/default/checkout',
            'marketplace/*',
            'shop/*',
            'store/*',
            
            // Otras rutas públicas
            'municipio/get-by-edo',
            'parroquia/get-by-muni',
            'parroquia/get-by-muni-cod',
        ];

        if (in_array($route, $publicRoutes)) {
            return true;
        }

        foreach ($publicRoutes as $publicRoute) {
            if (strpos($publicRoute, '*') !== false) {
                $pattern = preg_quote($publicRoute, '/');
                $pattern = str_replace('\*', '.*', $pattern);
                if (preg_match('/^' . $pattern . '$/', $route)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function renderFallbackMenu()
    {
        $menuClass = $this->mobileMode ? 'sidebar-menu' : $this->menuClass;
        
        $menuItems = '
        <ul class="' . $menuClass . '">
            <li class="' . ($this->mobileMode ? 'menu-item' : 'nav-item') . '">
                <a class="' . ($this->mobileMode ? 'menu-link' : 'nav-link text-white') . '" href="' . Url::to(['/']) . '">Inicio</a>
            </li>';
            
        // ✅ SIEMPRE MOSTRAR MARKETPLACE EN EL FALLBACK
        $menuItems .= '
            <li class="' . ($this->mobileMode ? 'menu-item' : 'nav-item') . '">
                <a class="' . ($this->mobileMode ? 'menu-link' : 'nav-link text-white') . '" href="' . Url::to(['/tienda/marketplace']) . '">Marketplace</a>
            </li>';
            
        if (Yii::$app->user->isGuest) {
            $menuItems .= '
            <li class="' . ($this->mobileMode ? 'menu-item' : 'nav-item') . '">
                <a class="' . ($this->mobileMode ? 'menu-link' : 'nav-link text-white') . '" href="' . Url::to(['/site/login']) . '">Iniciar Sesión</a>
            </li>';
        }
        
        $menuItems .= '</ul>';
        
        return $menuItems;
    }

    // ✅ RENDERIZAR PARA MÓVIL
    protected function renderMenuForMobile($menuItems)
    {
        $content = $this->renderMobileMenuItems($menuItems);
        return '<ul class="sidebar-menu mobile-menu">' . $content . '</ul>';
    }

    // ✅ RENDERIZAR PARA ESCRITORIO
    protected function renderMenuForDesktop($menuItems)
    {
        $content = $this->renderDesktopMenuItems($menuItems);
        return '<ul class="' . $this->menuClass . ' desktop-menu">' . $content . '</ul>';
    }

    // ✅ RENDERIZAR ITEMS PARA MÓVIL
    protected function renderMobileMenuItems($items, $level = 0)
    {
        $html = '';
        
        foreach ($items as $item) {
            $hasChildren = !empty($item['items']);
            $url = $item['url'] == '#' ? '#' : Url::to($item['url']);
            $label = Html::encode($item['label']);
            
            if ($hasChildren) {
                $childrenHtml = $this->renderMobileMenuItems($item['items'], $level + 1);
                $html .= '
                <li class="menu-item has-children level-' . $level . '">
                    <a href="#" class="menu-link mobile-menu-link">
                        ' . $label . '
                        <span class="submenu-indicator">›</span>
                    </a>
                    <ul class="submenu submenu-level-' . $level . '" style="display: none;">
                        ' . $childrenHtml . '
                    </ul>
                </li>';
            } else {
                $html .= '
                <li class="menu-item level-' . $level . '">
                    <a href="' . $url . '" class="menu-link mobile-menu-link">' . $label . '</a>
                </li>';
            }
        }
        
        return $html;
    }

    // ✅ RENDERIZAR ITEMS PARA ESCRITORIO
    protected function renderDesktopMenuItems($items, $level = 0)
    {
        $html = '';
        
        foreach ($items as $item) {
            $hasChildren = !empty($item['items']);
            
            if ($hasChildren) {
                $html .= $this->renderDropdownItem($item, $level);
            } else {
                $html .= $this->renderSimpleItem($item, $level);
            }
        }
        
        return $html;
    }

    protected function renderSimpleItem($item, $level)
    {
        $url = $item['url'] == '#' ? '#' : Url::to($item['url']);
        $label = Html::encode($item['label']);
        
        if ($level === 0) {
            return '<li class="nav-item">
                <a class="nav-link text-white desktop-nav-link" href="' . $url . '">' . $label . '</a>
            </li>';
        } else {
            return '<li>
                <a class="dropdown-item text-white" href="' . $url . '">' . $label . '</a>
            </li>';
        }
    }

    protected function renderDropdownItem($item, $level)
    {
        $label = Html::encode($item['label']);
        $childrenHtml = $this->renderDesktopMenuItems($item['items'], $level + 1);
        
        if ($level === 0) {
            return '<li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white desktop-nav-link" href="#" role="button" 
                   data-bs-toggle="dropdown" aria-expanded="false" data-level="' . $level . '">
                    ' . $label . '
                </a>
                <ul class="dropdown-menu" data-level="' . $level . '">
                    ' . $childrenHtml . '
                </ul>
            </li>';
        } elseif ($level === 1) {
            return '<li class="dropdown-submenu position-relative" data-level="' . $level . '">
                <a class="dropdown-item dropdown-toggle text-white d-flex justify-content-between align-items-center" 
                   href="#" role="button" data-level="' . $level . '">
                    ' . $label . '
                    <span class="submenu-arrow">›</span>
                </a>
                <ul class="dropdown-menu submenu-level-1" data-level="' . $level . '">
                    ' . $childrenHtml . '
                </ul>
            </li>';
        } else {
            return '<li class="dropdown-submenu position-relative" data-level="' . $level . '">
                <a class="dropdown-item dropdown-toggle text-white d-flex justify-content-between align-items-center" 
                   href="#" role="button" data-level="' . $level . '">
                    ' . $label . '
                    <span class="submenu-arrow">›</span>
                </a>
                <ul class="dropdown-menu submenu-level-' . $level . ' deep-level" data-level="' . $level . '">
                    ' . $childrenHtml . '
                </ul>
            </li>';
        }
    }
    
    /**
     * ✅ MÉTODO PARA VERIFICAR ESTRUCTURA DEL MENÚ (POSTGRESQL)
     */
    public static function debugMenuStructure()
    {
        try {
            $query = new Query();
            $allMenus = $query->select([
                    'm.id', 
                    'm.name', 
                    'm.route', 
                    'm.parent', 
                    'm."order" as menu_order'
                ])
                ->from('seguridad.menu m')
                ->orderBy('m."order" ASC')
                ->all();
            
            $result = [];
            foreach ($allMenus as $menu) {
                $result[] = $menu;
            }
            
            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * ✅ MÉTODO PARA VER TODOS LOS MENÚS DISPONIBLES
     */
    public static function getAllMenus()
    {
        try {
            $query = new Query();
            $allMenus = $query->select([
                    'm.id', 
                    'm.name', 
                    'm.route', 
                    'm.parent', 
                    'm."order" as menu_order'
                ])
                ->from('seguridad.menu m')
                ->orderBy('m.parent ASC, m."order" ASC')
                ->all();
            
            echo '<!-- === TODOS LOS MENÚS EN LA BD === -->' . "\n";
            foreach ($allMenus as $menu) {
                echo '<!-- ID: ' . $menu['id'] . ' | Nombre: ' . $menu['name'] . ' | Ruta: ' . ($menu['route'] ?: 'null') . ' | Parent: ' . ($menu['parent'] ?: 'null') . ' -->' . "\n";
            }
            echo '<!-- === FIN MENÚS === -->' . "\n";
            
            return $allMenus;
        } catch (\Exception $e) {
            echo '<!-- ERROR obteniendo menús: ' . htmlspecialchars($e->getMessage()) . ' -->' . "\n";
            return [];
        }
    }
}