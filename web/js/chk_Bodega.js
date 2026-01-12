// js/ged.js - Sistema GED - VERSIÓN OPTIMIZADA Y MEJORADA
// Versión: 3.0.0 - Con todas las mejoras sugeridas implementadas

// ==================================================
// MÓDULOS DEL SISTEMA
// ==================================================

class GEDSystem {
    constructor() {
        this.isMobile = this.checkIsMobile();
        this.menuOpen = false;
        this.navbarHeight = this.getNavbarHeight();
        this.debouncedResize = this.debounce(() => this.handleResize(), 250);
        this.modules = {};
        this.init();
    }
    
    checkIsMobile() {
        return window.innerWidth < 992;
    }
    
    getNavbarHeight() {
        const navbar = document.querySelector('.navbar-contextual');
        if (!navbar) return this.isMobile ? 70 : 180;
        
        if (this.isMobile) {
            if (window.innerWidth < 576) return 55;
            if (window.innerWidth < 768) return 60;
            return 70;
        } else {
            return window.innerHeight * 0.25;
        }
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }
    
    setup() {
        console.log('🚀 Sistema GED v3.0 inicializado - Modo:', this.isMobile ? 'Móvil' : 'Escritorio');
        
        try {
            // Inicializar todos los módulos
            this.modules = {
                navbar: new NavbarManager(),
                sidebar: new OffCanvasSidebar(),
                landing: new LandingPageManager(),
                search: new SchoolSearch(),
                components: new ComponentsManager()
            };
            
            // Inicializar cada módulo
            Object.values(this.modules).forEach(module => module.init());
            
            // Aplicar correcciones iniciales
            this.applyBodyCorrections();
            
            // Manejar cambios de tamaño con debounce mejorado
            window.addEventListener('resize', this.debouncedResize);
            
            // Forzar recálculo después de la carga completa
            setTimeout(() => {
                this.forceNavbarRecalculation();
                this.applyBodyCorrections();
            }, 500);
            
            console.log('✅ Todos los módulos inicializados correctamente');
            
        } catch (error) {
            console.error('❌ Error crítico en inicialización del sistema:', error);
            this.showCriticalError('Error al inicializar el sistema');
        }
    }
    
    applyBodyCorrections() {
        try {
            console.log('🔧 Aplicando correcciones de body y layout...');
            
            this.navbarHeight = this.getNavbarHeight();
            document.body.style.paddingTop = this.navbarHeight + 'px';
            
            const mainElements = document.querySelectorAll('main#main');
            mainElements.forEach(main => {
                main.style.marginTop = '0';
                main.style.minHeight = `calc(100vh - ${this.navbarHeight}px)`;
            });
            
            const mainContainers = document.querySelectorAll('.main-container');
            mainContainers.forEach(container => {
                container.style.marginTop = '0';
                container.style.minHeight = `calc(100vh - ${this.navbarHeight}px)`;
            });
            
            console.log('✅ Correcciones aplicadas - Navbar height:', this.navbarHeight);
        } catch (error) {
            console.error('Error en applyBodyCorrections:', error);
        }
    }
    
    forceNavbarRecalculation() {
        try {
            const navbar = document.querySelector('.navbar-contextual');
            if (navbar) {
                navbar.style.display = 'none';
                void navbar.offsetHeight;
                navbar.style.display = '';
                console.log('🔄 Navbar recalculation forzado');
            }
        } catch (error) {
            console.error('Error en forceNavbarRecalculation:', error);
        }
    }
    
    handleResize() {
        try {
            const newIsMobile = this.checkIsMobile();
            const oldNavbarHeight = this.navbarHeight;
            
            if (newIsMobile !== this.isMobile) {
                this.isMobile = newIsMobile;
                console.log('🔄 Cambio de modo:', this.isMobile ? 'Móvil' : 'Escritorio');
                
                if (this.modules.sidebar) {
                    this.modules.sidebar.handleViewportChange(this.isMobile);
                }
            }
            
            this.navbarHeight = this.getNavbarHeight();
            
            if (this.navbarHeight !== oldNavbarHeight) {
                setTimeout(() => {
                    if (this.modules.navbar) this.modules.navbar.forceFullWidth();
                    this.applyBodyCorrections();
                    this.forceNavbarRecalculation();
                }, 100);
            }
        } catch (error) {
            console.error('Error en handleResize:', error);
        }
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    showCriticalError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #dc3545;
            color: white;
            padding: 15px;
            text-align: center;
            z-index: 9999;
            font-weight: bold;
        `;
        errorDiv.textContent = `⚠️ ${message}. Por favor, recarga la página.`;
        document.body.appendChild(errorDiv);
    }
}

// ==================================================
// NAVBAR MANAGER - MODIFICADO PARA OCULTAR EN MÓVIL
// ==================================================

class NavbarManager {
    constructor() {
        this.navbar = null;
        this.isMobile = window.innerWidth < 992;
    }
    
    init() {
        try {
            this.navbar = document.querySelector('.navbar-contextual');
            
            if (!this.navbar) {
                console.warn('❌ Navbar contextual no encontrado');
                return;
            }
            
            // ✅ CORRECCIÓN CRÍTICA: Ocultar navbar-collapse en móviles
            if (this.isMobile) {
                this.hideNavbarCollapseOnMobile();
            }
            
            this.stabilizeNavbar();
            this.initNavbarEscuelaSelector();
            
            console.log('✅ NavbarManager inicializado - Móvil:', this.isMobile);
        } catch (error) {
            console.error('Error en NavbarManager.init:', error);
        }
    }
    
    hideNavbarCollapseOnMobile() {
        try {
            const navbarCollapse = document.getElementById('navbarGedCollapse');
            if (navbarCollapse) {
                navbarCollapse.style.display = 'none';
                navbarCollapse.classList.remove('show');
                console.log('✅ Navbar-collapse ocultado en móvil');
            }
        } catch (error) {
            console.error('Error en hideNavbarCollapseOnMobile:', error);
        }
    }
    
    stabilizeNavbar() {
        try {
            const criticalStyles = `
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 1030 !important;
                width: 100% !important;
                transform: none !important;
            `;
            this.navbar.style.cssText += criticalStyles;
        } catch (error) {
            console.error('Error en stabilizeNavbar:', error);
        }
    }
    
    forceFullWidth() {
        try {
            const fullWidthStyle = `
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            `;
            
            const elementsToFullWidth = [
                '.navbar-contextual',
                '.navbar-collapse',
                '.container-fluid'
            ];
            
            elementsToFullWidth.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    element.style.cssText += fullWidthStyle;
                });
            });
        } catch (error) {
            console.error('Error en forceFullWidth:', error);
        }
    }
    
    initNavbarEscuelaSelector() {
        try {
            const escuelaSelect = document.getElementById('navbar-escuela-select');
            if (escuelaSelect) {
                escuelaSelect.addEventListener('change', function() {
                    const escuelaId = this.value;
                    if (escuelaId && escuelaId > 0) {
                        const escuelaNombre = this.options[this.selectedIndex].text;
                        window.location.href = '/ged/default/escuela?id=' + escuelaId + '&nombre=' + encodeURIComponent(escuelaNombre);
                    }
                });
                console.log('✅ Selector de escuelas del navbar inicializado');
            }
        } catch (error) {
            console.error('Error en initNavbarEscuelaSelector:', error);
        }
    }
}

// ==================================================
// OFF-CANVAS SIDEBAR CON LAZY LOADING
// ==================================================

class OffCanvasSidebar {
    constructor() {
        this.isOpen = false;
        this.isMobile = window.innerWidth < 992;
        this.menuLoaded = false;
        this.sidebar = null;
        this.backdrop = null;
        this.sidebarNav = null;
    }
    
    init() {
        try {
            this.createOffCanvas();
            this.bindEvents();
            console.log('✅ Off-Canvas Sidebar inicializado - Móvil:', this.isMobile);
        } catch (error) {
            console.error('Error en OffCanvasSidebar.init:', error);
        }
    }
    
    createOffCanvas() {
        try {
            if (document.querySelector('.ged-offcanvas-sidebar')) {
                this.sidebar = document.querySelector('.ged-offcanvas-sidebar');
                this.backdrop = document.querySelector('.ged-sidebar-backdrop');
                this.sidebarNav = this.sidebar.querySelector('.sidebar-nav');
                return;
            }

            const sidebar = document.createElement('div');
            sidebar.className = 'ged-offcanvas-sidebar';
            sidebar.innerHTML = `
                <div class="sidebar-header">
                    <button class="close-sidebar" aria-label="Cerrar menú">✕</button>
                    <span>Menú Principal</span>
                </div>
                <nav class="sidebar-nav" aria-label="Navegación principal">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando menú...</span>
                        </div>
                        <p class="text-muted mt-2">Cargando menú...</p>
                    </div>
                </nav>
            `;
            
            const backdrop = document.createElement('div');
            backdrop.className = 'ged-sidebar-backdrop';
            
            document.body.appendChild(sidebar);
            document.body.appendChild(backdrop);
            
            this.sidebar = sidebar;
            this.backdrop = backdrop;
            this.sidebarNav = this.sidebar.querySelector('.sidebar-nav');
        } catch (error) {
            console.error('Error en createOffCanvas:', error);
        }
    }
    
    loadMobileMenu() {
        try {
            if (this.menuLoaded) return;
            
            console.log('📱 Cargando menú específico para móvil...');
            
            if (typeof $ !== 'undefined') {
                this.loadMobileMenuViaAJAX();
            } else {
                setTimeout(() => {
                    this.loadRealMenu();
                }, 100);
            }
        } catch (error) {
            console.error('Error en loadMobileMenu:', error);
            this.loadFallbackMenu();
        }
    }
    
    loadMobileMenuViaAJAX() {
        try {
            $.ajax({
                url: '/site/mobile-menu',
                type: 'GET',
                data: {
                    _csrf: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    console.log('✅ Menú móvil cargado via AJAX');
                    this.sidebarNav.innerHTML = response;
                    this.adaptMenuForOffCanvas(this.sidebarNav);
                    this.menuLoaded = true;
                },
                error: (xhr, status, error) => {
                    console.error('❌ Error cargando menú móvil via AJAX:', error);
                    console.log('🔄 Intentando cargar menú desde navbar...');
                    this.loadRealMenu();
                }
            });
        } catch (error) {
            console.error('Error en loadMobileMenuViaAJAX:', error);
            this.loadRealMenu();
        }
    }
    
    loadRealMenu() {
        try {
            console.log('🔄 Cargando menú real desde navbar...');
            
            const realMenu = document.querySelector('.navbar-nav');
            
            if (!realMenu) {
                console.warn('❌ No se encontró el menú real en el navbar');
                this.loadFallbackMenu();
                return;
            }
            
            const clonedMenu = realMenu.cloneNode(true);
            this.sidebarNav.innerHTML = '';
            this.sidebarNav.appendChild(clonedMenu);
            this.adaptMenuForOffCanvas(this.sidebarNav);
            this.menuLoaded = true;
            
            console.log('✅ Menú real cargado y adaptado correctamente');
        } catch (error) {
            console.error('Error en loadRealMenu:', error);
            this.loadFallbackMenu();
        }
    }
    
    loadFallbackMenu() {
        try {
            console.log('🔄 Cargando menú de respaldo...');
            
            this.sidebarNav.innerHTML = `
                <ul class="sidebar-menu">
                    <li class="menu-item">
                        <a href="/" class="menu-link">Inicio</a>
                    </li>
                    <li class="menu-item has-children">
                        <a href="#" class="menu-link">
                            Sistema
                            <span class="submenu-indicator">›</span>
                        </a>
                        <ul class="submenu">
                            <li class="menu-item">
                                <a href="/ged/default/index" class="menu-link">Seleccionar Escuela</a>
                            </li>
                            <li class="menu-item">
                                <a href="/site/login" class="menu-link">Iniciar Sesión</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-divider"></li>
                    <li class="menu-item">
                        <a href="#" class="menu-link">Ayuda</a>
                    </li>
                </ul>
            `;
            
            this.adaptMenuForOffCanvas(this.sidebarNav);
            this.menuLoaded = true;
            console.log('✅ Menú de respaldo cargado');
        } catch (error) {
            console.error('Error en loadFallbackMenu:', error);
        }
    }
    
    adaptMenuForOffCanvas(menuElement) {
        try {
            let mainMenu = menuElement.querySelector('.navbar-nav, .sidebar-menu');
            if (!mainMenu) return;
            
            if (mainMenu.classList.contains('navbar-nav')) {
                this.convertBootstrapToMobileMenu(mainMenu);
            }
            
            this.addMobileMenuEvents(menuElement);
            console.log('✅ Menú adaptado correctamente para móvil');
        } catch (error) {
            console.error('Error en adaptMenuForOffCanvas:', error);
        }
    }
    
    convertBootstrapToMobileMenu(menuElement) {
        try {
            const dropdowns = menuElement.querySelectorAll('.dropdown, .dropdown-submenu');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('dropdown', 'dropdown-submenu');
                dropdown.classList.add('has-children');
                
                const toggle = dropdown.querySelector('.dropdown-toggle');
                if (toggle) {
                    toggle.classList.remove('dropdown-toggle');
                    toggle.removeAttribute('data-bs-toggle');
                    toggle.removeAttribute('aria-expanded');
                    
                    if (!toggle.querySelector('.submenu-indicator')) {
                        const indicator = document.createElement('span');
                        indicator.className = 'submenu-indicator';
                        indicator.textContent = '›';
                        toggle.appendChild(indicator);
                    }
                }
                
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.classList.remove('dropdown-menu');
                    menu.classList.add('submenu');
                    menu.style.display = 'none';
                }
            });
            
            const navItems = menuElement.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('nav-item');
                item.classList.add('menu-item');
            });
            
            const navLinks = menuElement.querySelectorAll('.nav-link, .dropdown-item');
            navLinks.forEach(link => {
                link.classList.remove('nav-link', 'dropdown-item');
                link.classList.add('menu-link');
                
                if (link.getAttribute('href') === '#' && link.parentElement.classList.contains('has-children')) {
                    link.style.cursor = 'pointer';
                }
            });
            
            menuElement.classList.remove('navbar-nav');
            menuElement.classList.add('sidebar-menu');
        } catch (error) {
            console.error('Error en convertBootstrapToMobileMenu:', error);
        }
    }
    
    addMobileMenuEvents(menuElement) {
        try {
            const menuItems = menuElement.querySelectorAll('.has-children > .menu-link');
            menuItems.forEach(menuItem => {
                menuItem.replaceWith(menuItem.cloneNode(true));
            });
            
            const refreshedMenuItems = menuElement.querySelectorAll('.has-children > .menu-link');
            refreshedMenuItems.forEach(menuItem => {
                menuItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleSubmenu(menuItem.parentElement);
                });
            });
            
            const normalLinks = menuElement.querySelectorAll('.menu-item:not(.has-children) > .menu-link');
            normalLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (this.isMobile) {
                        setTimeout(() => this.close(), 300);
                    }
                });
            });
        } catch (error) {
            console.error('Error en addMobileMenuEvents:', error);
        }
    }
    
    toggleSubmenu(parentItem) {
        try {
            const submenu = parentItem.querySelector('.submenu');
            if (!submenu) return;
            
            const isCurrentlyOpen = submenu.style.display === 'block';
            const indicator = parentItem.querySelector('.submenu-indicator');
            
            const siblings = parentItem.parentElement.querySelectorAll('.has-children');
            siblings.forEach(sibling => {
                if (sibling !== parentItem) {
                    const siblingSubmenu = sibling.querySelector('.submenu');
                    const siblingIndicator = sibling.querySelector('.submenu-indicator');
                    if (siblingSubmenu) siblingSubmenu.style.display = 'none';
                    if (siblingIndicator) siblingIndicator.style.transform = 'rotate(0deg)';
                    sibling.classList.remove('open');
                }
            });
            
            if (isCurrentlyOpen) {
                submenu.style.display = 'none';
                if (indicator) indicator.style.transform = 'rotate(0deg)';
                parentItem.classList.remove('open');
            } else {
                submenu.style.display = 'block';
                if (indicator) indicator.style.transform = 'rotate(90deg)';
                parentItem.classList.add('open');
            }
        } catch (error) {
            console.error('Error en toggleSubmenu:', error);
        }
    }
    
    bindEvents() {
        try {
            this.interceptBootstrapToggler();
            
            const closeButton = this.sidebar.querySelector('.close-sidebar');
            if (closeButton) {
                closeButton.addEventListener('click', () => this.close());
            }
            
            if (this.backdrop) {
                this.backdrop.addEventListener('click', () => this.close());
            }
            
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) this.close();
            });
        } catch (error) {
            console.error('Error en bindEvents:', error);
        }
    }
    
    interceptBootstrapToggler() {
        try {
            const navbarToggler = document.querySelector('.navbar-toggler');
            if (!navbarToggler) {
                console.warn('❌ No se encontró el navbar toggler');
                return;
            }
            
            const originalOnClick = navbarToggler.onclick;
            
            navbarToggler.addEventListener('click', (e) => {
                if (this.isMobile) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    if (this.isOpen) {
                        this.close();
                    } else {
                        this.open();
                    }
                    return false;
                }
                
                if (originalOnClick) originalOnClick.call(navbarToggler, e);
            });
            
            console.log('✅ Toggler interceptado correctamente');
        } catch (error) {
            console.error('Error en interceptBootstrapToggler:', error);
        }
    }
    
    open() {
        try {
            if (this.isOpen) return;
            
            if (!this.menuLoaded) {
                this.loadMobileMenu();
                this.menuLoaded = true;
            }
            
            this.isOpen = true;
            this.sidebar.classList.add('open');
            this.backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
            this.sidebar.setAttribute('tabindex', '-1');
            this.sidebar.focus();
            
            console.log('✅ Off-Canvas abierto correctamente');
        } catch (error) {
            console.error('Error en open:', error);
        }
    }
    
    close() {
        try {
            if (!this.isOpen) return;
            
            this.isOpen = false;
            this.sidebar.classList.remove('open');
            this.backdrop.classList.remove('show');
            document.body.style.overflow = '';
            this.closeAllSubmenus();
            
            console.log('✅ Off-Canvas cerrado correctamente');
        } catch (error) {
            console.error('Error en close:', error);
        }
    }
    
    closeAllSubmenus() {
        try {
            const submenus = this.sidebar.querySelectorAll('.submenu');
            const parentItems = this.sidebar.querySelectorAll('.has-children');
            
            submenus.forEach(submenu => submenu.style.display = 'none');
            parentItems.forEach(item => {
                item.classList.remove('open');
                const indicator = item.querySelector('.submenu-indicator');
                if (indicator) indicator.style.transform = 'rotate(0deg)';
            });
        } catch (error) {
            console.error('Error en closeAllSubmenus:', error);
        }
    }
    
    handleViewportChange(isMobile) {
        this.isMobile = isMobile;
        console.log('🔄 Off-Canvas cambió a modo:', this.isMobile ? 'Móvil' : 'Escritorio');
        
        if (!this.isMobile && this.isOpen) {
            this.close();
        }
    }
}

// ==================================================
// SCHOOL SEARCH MANAGER
// ==================================================

class SchoolSearch {
    constructor() {
        this.searchTimeout = null;
        this.elements = {};
        this.selectors = {
            searchInput: '#schoolSearch',
            searchResults: '#schoolSearchResults',
            searchBtn: '#searchSchoolBtn',
            currentSchool: '#current-school',
            currentSchoolId: '#current-school-id'
        };
        this.urls = {
            search: '/ged/default/search-schools',
            setSchool: '/ged/default/set-school'
        };
    }
    
    init() {
        try {
            if (typeof window.jQuery === 'undefined') {
                console.error('jQuery no está cargado. SchoolSearch desactivado.');
                return;
            }
            
            if (!document.querySelector('#schoolSearch')) return;
            
            this.cacheElements();
            this.bindEvents();
            console.log('✅ SchoolSearch inicializado');
        } catch (error) {
            console.error('Error en SchoolSearch.init:', error);
        }
    }
    
    cacheElements() {
        try {
            for (const [key, selector] of Object.entries(this.selectors)) {
                this.elements[key] = $(selector);
            }
        } catch (error) {
            console.error('Error en cacheElements:', error);
        }
    }
    
    bindEvents() {
        try {
            const { searchInput, searchResults, searchBtn } = this.elements;
            if (searchInput.length === 0) return;
            
            searchInput.on('input', (e) => this.handleSearchInput(e.target.value.trim()));
            searchBtn.on('click', () => this.handleSearchClick());
            searchInput.on('keypress', (e) => {
                if (e.which === 13) this.handleEnterKey(e);
            });
            
            $(document).on('click', (e) => {
                if (!$(e.target).closest('.school-search-container').length) {
                    this.hideResults();
                }
            });
        } catch (error) {
            console.error('Error en bindEvents:', error);
        }
    }
    
    handleSearchInput(query) {
        clearTimeout(this.searchTimeout);
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }
    
    handleSearchClick() {
        const query = this.elements.searchInput.val().trim();
        if (query.length >= 2) {
            this.performSearch(query);
        } else {
            this.elements.searchInput.focus();
        }
    }
    
    handleEnterKey(e) {
        const query = this.elements.searchInput.val().trim();
        if (query.length >= 2) {
            this.performSearch(query);
            e.preventDefault();
        }
    }
    
    performSearch(query) {
        try {
            this.showLoading();
            
            $.ajax({
                url: this.urls.search,
                type: 'GET',
                data: { 
                    q: query,
                    _csrf: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => this.displayResults(response),
                error: (xhr, status, error) => {
                    console.error('Error en la búsqueda:', error);
                    this.showError('Error en la búsqueda');
                }
            });
        } catch (error) {
            console.error('Error en performSearch:', error);
            this.showError('Error en la búsqueda');
        }
    }
    
    showLoading() {
        this.elements.searchResults.html('<div class="search-result-item text-muted">Buscando...</div>').show();
    }
    
    showError(message) {
        this.elements.searchResults.html(`<div class="search-result-item text-danger">${message}</div>`).show();
    }
    
    displayResults(escuelas) {
        try {
            const { searchResults } = this.elements;
            searchResults.empty();
            
            if (!escuelas || escuelas.length === 0) {
                searchResults.append('<div class="search-result-item text-muted">No se encontraron escuelas</div>');
            } else {
                escuelas.forEach((escuela) => this.createResultItem(escuela));
            }
            
            searchResults.show();
        } catch (error) {
            console.error('Error en displayResults:', error);
        }
    }
    
    createResultItem(escuela) {
        try {
            const item = $('<div class="search-result-item"></div>');
            let escuelaInfo = `<div class="school-name">${escuela.nombre}</div><div class="school-id">ID: ${escuela.id}</div>`;
            
            if (escuela.direccion_administrativa) {
                escuelaInfo += `<div class="school-address text-muted">${escuela.direccion_administrativa}</div>`;
            }
            
            item.html(escuelaInfo);
            item.on('click', () => this.selectSchool(escuela));
            this.elements.searchResults.append(item);
        } catch (error) {
            console.error('Error en createResultItem:', error);
        }
    }
    
    selectSchool(escuela) {
        try {
            const originalHtml = this.elements.searchBtn.html();
            this.elements.searchBtn.html('<i class="bi bi-hourglass-split"></i>').prop('disabled', true);
            
            $.ajax({
                url: this.urls.setSchool,
                type: 'POST',
                data: {
                    schoolId: escuela.id,
                    schoolName: escuela.nombre,
                    _csrf: $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    if (response.success) {
                        this.updateUI(escuela);
                        this.showNotification('Escuela seleccionada: ' + escuela.nombre, 'success');
                        this.reloadPage();
                    } else {
                        this.showNotification('Error al seleccionar la escuela', 'error');
                    }
                },
                error: () => this.showNotification('Error de conexión', 'error'),
                complete: () => {
                    this.elements.searchBtn.html(originalHtml).prop('disabled', false);
                }
            });
        } catch (error) {
            console.error('Error en selectSchool:', error);
            this.showNotification('Error al seleccionar escuela', 'error');
        }
    }
    
    updateUI(escuela) {
        this.elements.currentSchool.text(escuela.nombre);
        this.elements.currentSchoolId.text('ID: ' + escuela.id).show();
        this.elements.searchInput.val('');
        this.hideResults();
    }
    
    hideResults() {
        this.elements.searchResults.hide().empty();
    }
    
    showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" 
                 style="position: fixed; top: 20px; right: 20px; z-index: 10000;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.alert('close');
        }, 3000);
    }
    
    reloadPage() {
        setTimeout(() => location.reload(), 800);
    }
}

// ==================================================
// COMPONENTS MANAGER
// ==================================================

class ComponentsManager {
    constructor() {
        this.tooltipsInitialized = false;
    }
    
    init() {
        try {
            this.initTooltips();
            this.initEscuelaSelector();
            this.initCarousel();
            console.log('✅ ComponentsManager inicializado');
        } catch (error) {
            console.error('Error en ComponentsManager.init:', error);
        }
    }
    
    initTooltips() {
        try {
            if (typeof bootstrap !== 'undefined' && !this.tooltipsInitialized) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
                this.tooltipsInitialized = true;
                console.log('✅ Tooltips inicializados');
            }
        } catch (error) {
            console.error('Error en initTooltips:', error);
        }
    }
    
    initEscuelaSelector() {
        try {
            if (typeof $ === 'undefined') return;
            
            $('.back-to-top').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, 300);
            });
            
            $(window).on('scroll', function() {
                $('.back-to-top').toggleClass('active', $(this).scrollTop() > 300);
            });
            
            console.log('✅ Escuela selector inicializado');
        } catch (error) {
            console.error('Error en initEscuelaSelector:', error);
        }
    }
    
    initCarousel() {
        try {
            const carouselHero = document.getElementById('carouselHero');
            if (carouselHero && typeof bootstrap !== 'undefined') {
                new bootstrap.Carousel(carouselHero, {
                    interval: 5000,
                    ride: 'carousel',
                    wrap: true,
                    pause: 'hover'
                });
                console.log('✅ Carrusel Hero inicializado');
            }
        } catch (error) {
            console.error('Error en initCarousel:', error);
        }
    }
}

// ==================================================
// LANDING PAGE MANAGER OPTIMIZADO
// ==================================================

class LandingPageManager {
    constructor() {
        this.productos = {};
        this.carrito = [];
        this.totalVendidos = 0;
        this.cachedElements = {};
        this.observer = null;
    }

    init() {
        try {
            console.log('🚀 Landing Page Manager inicializado');
            this.cargarProductos();
            this.renderizarProductos();
            this.actualizarTotalVendidos();
            this.setupEventListeners();
            this.mostrarBannerTiendas();
            this.initAnimaciones();
            this.initMarketplace();
            console.log('✅ LandingPageManager listo');
        } catch (error) {
            console.error('Error en LandingPageManager.init:', error);
        }
    }

    getElement(id) {
        try {
            if (!this.cachedElements[id]) {
                this.cachedElements[id] = document.getElementById(id);
            }
            return this.cachedElements[id];
        } catch (error) {
            console.error(`Error obteniendo elemento ${id}:`, error);
            return null;
        }
    }

    cargarProductos() {
        try {
            this.productos = {
                vestimenta: [
                    { id: 1, nombre: 'Camiseta Deportiva', precio: 25, vendidos: 150 },
                    { id: 2, nombre: 'Pantalón Deportivo', precio: 35, vendidos: 120 },
                    { id: 3, nombre: 'Sudadera con Capucha', precio: 45, vendidos: 95 }
                ],
                alimentacion: [
                    { id: 4, nombre: 'Barra Energética', precio: 3, vendidos: 200 },
                    { id: 5, nombre: 'Bebida Isotónica', precio: 2, vendidos: 180 },
                    { id: 6, nombre: 'Snack Proteico', precio: 4, vendidos: 150 }
                ],
                'implementos-deportivos': [
                    { id: 7, nombre: 'Balón de Fútbol', precio: 20, vendidos: 80 },
                    { id: 8, nombre: 'Cuerda para Saltar', precio: 10, vendidos: 75 },
                    { id: 9, nombre: 'Banda Elástica', precio: 15, vendidos: 90 }
                ],
                suplementos: [
                    { id: 10, nombre: 'Proteína en Polvo', precio: 50, vendidos: 110 },
                    { id: 11, nombre: 'Multivitamínico', precio: 15, vendidos: 85 },
                    { id: 12, nombre: 'Creatina', precio: 30, vendidos: 70 }
                ]
            };
            console.log('✅ Productos cargados');
        } catch (error) {
            console.error('Error en cargarProductos:', error);
        }
    }

    renderizarProductos() {
        try {
            for (const categoria in this.productos) {
                const contenedor = this.getElement(`productos-${categoria}`);
                if (!contenedor) {
                    console.warn(`Contenedor no encontrado: productos-${categoria}`);
                    continue;
                }

                contenedor.innerHTML = '';
                this.productos[categoria].forEach(producto => {
                    const productoHTML = `
                        <div class="producto-card">
                            <h3>${producto.nombre}</h3>
                            <p class="precio">$${producto.precio}</p>
                            <p class="vendidos">${producto.vendidos} vendidos</p>
                            <button class="btn-agregar-carrito" 
                                    data-id="${producto.id}"
                                    data-nombre="${producto.nombre}"
                                    data-precio="${producto.precio}">
                                Agregar al carrito
                            </button>
                        </div>
                    `;
                    contenedor.insertAdjacentHTML('beforeend', productoHTML);
                });
            }
            console.log('✅ Productos renderizados');
        } catch (error) {
            console.error('Error en renderizarProductos:', error);
        }
    }

    actualizarTotalVendidos() {
        try {
            this.totalVendidos = 0;
            for (const categoria in this.productos) {
                this.productos[categoria].forEach(producto => {
                    this.totalVendidos += producto.vendidos;
                });
            }
            
            const totalElement = this.getElement('total-productos-vendidos');
            if (totalElement) {
                totalElement.textContent = this.totalVendidos.toLocaleString();
            }
            console.log('✅ Total vendidos actualizado:', this.totalVendidos);
        } catch (error) {
            console.error('Error en actualizarTotalVendidos:', error);
        }
    }

    setupEventListeners() {
        try {
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-agregar-carrito')) {
                    const id = parseInt(e.target.dataset.id);
                    const nombre = e.target.dataset.nombre;
                    const precio = parseFloat(e.target.dataset.precio);
                    
                    this.agregarAlCarrito(id, nombre, precio);
                    
                    e.target.textContent = '✓ Agregado';
                    e.target.disabled = true;
                    setTimeout(() => {
                        e.target.textContent = 'Agregar al carrito';
                        e.target.disabled = false;
                    }, 1500);
                }
            });
            
            const accederBtn = this.getElement('btn-acceder-sistema');
            if (accederBtn) this.enhanceAccederButton(accederBtn);
            
            const marketplaceBtn = this.getElement('btn-marketplace');
            if (marketplaceBtn) this.enhanceMarketplaceButton(marketplaceBtn);
            
            const logo = this.getElement('ged-main-logo');
            if (logo) this.addLogoAnimation(logo);
            
            if (typeof $ !== 'undefined') {
                $('#main-escuela-select').on('change', function() {
                    const escuelaId = $(this).val();
                    if (escuelaId && escuelaId > 0) {
                        const escuelaNombre = $(this).find('option:selected').text();
                        window.location.href = '/ged/default/escuela?id=' + escuelaId + '&nombre=' + encodeURIComponent(escuelaNombre);
                    }
                });

                $('#filtro-escuelas').on('input', function() {
                    const filtro = $(this).val().toLowerCase();
                    $('.escuela-item').each(function() {
                        const nombre = $(this).find('.school-name').text().toLowerCase();
                        $(this).toggle(nombre.includes(filtro));
                    });
                });

                $('#btn-limpiar-filtro').on('click', function() {
                    $('#filtro-escuelas').val('').trigger('input');
                });

                $('.school-card').hover(
                    function() { $(this).addClass('shadow-lg'); },
                    function() { $(this).removeClass('shadow-lg'); }
                );

                $('a[href^="#"]').on('click', function(event) {
                    const target = $(this).attr('href');
                    if (target && target !== '#' && $(target).length) {
                        event.preventDefault();
                        $('html, body').stop().animate({
                            scrollTop: $(target).offset().top - 100
                        }, 1000);
                    }
                });
            }
            
            console.log('✅ Event listeners configurados');
        } catch (error) {
            console.error('Error en setupEventListeners:', error);
        }
    }

    agregarAlCarrito(id, nombre, precio) {
        try {
            const productoExistente = this.carrito.find(p => p.id === id);
            
            if (productoExistente) {
                productoExistente.cantidad++;
            } else {
                this.carrito.push({ id, nombre, precio, cantidad: 1 });
            }
            
            this.actualizarContadorCarrito();
            this.mostrarNotificacion(`${nombre} agregado al carrito`);
            console.log('✅ Producto agregado al carrito:', nombre);
        } catch (error) {
            console.error('Error en agregarAlCarrito:', error);
        }
    }

    actualizarContadorCarrito() {
        try {
            const contador = this.getElement('contador-carrito');
            if (contador) {
                const totalItems = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
                contador.textContent = totalItems;
                contador.style.display = totalItems > 0 ? 'block' : 'none';
            }
        } catch (error) {
            console.error('Error en actualizarContadorCarrito:', error);
        }
    }

    mostrarNotificacion(mensaje) {
        try {
            let notificacion = this.getElement('notificacion-carrito');
            
            if (!notificacion) {
                notificacion = document.createElement('div');
                notificacion.id = 'notificacion-carrito';
                notificacion.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #4CAF50;
                    color: white;
                    padding: 15px 25px;
                    border-radius: 5px;
                    z-index: 1000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: slideIn 0.3s ease-out;
                `;
                document.body.appendChild(notificacion);
                this.cachedElements['notificacion-carrito'] = notificacion;
            }
            
            notificacion.textContent = mensaje;
            notificacion.style.display = 'block';
            
            setTimeout(() => {
                notificacion.style.display = 'none';
            }, 3000);
        } catch (error) {
            console.error('Error en mostrarNotificacion:', error);
        }
    }

    mostrarBannerTiendas() {
        try {
            const banner = this.getElement('banner-tiendas-patrocinadas');
            if (banner) {
                banner.style.cssText = `
                    width: 60%;
                    margin: 20px auto;
                    padding: 20px;
                    background-color: #f8f9fa;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                `;
                
                banner.innerHTML = `
                    <h2 style="color: #2c3e50; margin-bottom: 15px;">🏪 Tiendas Patrocinadas</h2>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Descubre nuestras tiendas aliadas con los mejores productos deportivos y descuentos exclusivos.
                    </p>
                    <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
                        <div class="tienda" style="text-align: center; margin: 10px;">
                            <div style="background: #3498db; color: white; width: 60px; height: 60px; 
                                        line-height: 60px; border-radius: 50%; margin: 0 auto 10px;">🏀</div>
                            <p style="font-weight: bold;">Deportes Total</p>
                            <p style="font-size: 0.9em;">15% descuento</p>
                        </div>
                        <div class="tienda" style="text-align: center; margin: 10px;">
                            <div style="background: #2ecc71; color: white; width: 60px; height: 60px; 
                                        line-height: 60px; border-radius: 50%; margin: 0 auto 10px;">👟</div>
                            <p style="font-weight: bold;">Running Pro</p>
                            <p style="font-size: 0.9em;">Envío gratis</p>
                        </div>
                        <div class="tienda" style="text-align: center; margin: 10px;">
                            <div style="background: #e74c3c; color: white; width: 60px; height: 60px; 
                                        line-height: 60px; border-radius: 50%; margin: 0 auto 10px;">🥤</div>
                            <p style="font-weight: bold;">NutriSport</p>
                            <p style="font-size: 0.9em;">2x1 en suplementos</p>
                        </div>
                    </div>
                `;
                console.log('✅ Banner tiendas mostrado');
            }
        } catch (error) {
            console.error('Error en mostrarBannerTiendas:', error);
        }
    }
    
    initAnimaciones() {
        try {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        
                        if (entry.target.classList.contains('feature-card')) {
                            setTimeout(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, 100);
                        }
                        
                        if (entry.target.classList.contains('categoria-card')) {
                            setTimeout(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, 200);
                        }
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.feature-card, .categoria-card').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                this.observer.observe(element);
            });
            
            console.log('✅ Animaciones inicializadas');
        } catch (error) {
            console.error('Error en initAnimaciones:', error);
        }
    }
    
    initMarketplace() {
        try {
            if (!document.querySelector('.landing-page')) return;
            
            const marketplaceMenu = document.querySelector('.marketplace-nav');
            if (!marketplaceMenu) return;
            
            const dropdownToggles = marketplaceMenu.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        e.preventDefault();
                        const dropdownMenu = this.nextElementSibling;
                        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                    }
                });
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.marketplace-nav .dropdown')) {
                    marketplaceMenu.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                }
            });
            
            marketplaceMenu.querySelectorAll('a[href="#"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (window.innerWidth >= 992) e.preventDefault();
                });
            });
            
            document.querySelectorAll('.marketplace-nav .nav-link').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                link.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
            
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const dropdowns = marketplaceMenu.querySelectorAll('.dropdown-menu');
                    if (window.innerWidth >= 992) {
                        dropdowns.forEach(menu => menu.style.display = '');
                    } else {
                        dropdowns.forEach(menu => menu.style.display = 'none');
                    }
                }, 250);
            });
            
            console.log('✅ Marketplace inicializado');
        } catch (error) {
            console.error('Error en initMarketplace:', error);
        }
    }
    
    enhanceAccederButton(button) {
        button.addEventListener('click', (e) => {
            const originalText = button.innerHTML;
            button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Accediendo...`;
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1500);
        });
        
        button.addEventListener('mouseenter', () => {
            button.style.boxShadow = '0 15px 30px rgba(40, 167, 69, 0.3)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.boxShadow = '';
        });
    }
    
    enhanceMarketplaceButton(button) {
        button.addEventListener('click', (e) => {
            button.classList.add('pulse-animation');
            setTimeout(() => button.classList.remove('pulse-animation'), 500);
        });
        
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.05) rotate(2deg)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = '';
        });
    }
    
    addLogoAnimation(logo) {
        logo.addEventListener('mouseenter', () => {
            logo.style.transform = 'scale(1.1) rotate(5deg)';
            logo.style.filter = 'drop-shadow(0 8px 16px rgba(0,0,0,0.4))';
        });
        
        logo.addEventListener('mouseleave', () => {
            logo.style.transform = '';
            logo.style.filter = 'drop-shadow(0 4px 8px rgba(0,0,0,0.3))';
        });
        
        setTimeout(() => {
            logo.style.transition = 'transform 0.5s ease, filter 0.5s ease';
        }, 100);
    }
}

// ==================================================
// INICIALIZACIÓN GLOBAL MEJORADA
// ==================================================

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (!window.gedSystem) {
            window.gedSystem = new GEDSystem();
            console.log('🚀 Sistema GED v3.0 completamente inicializado');
        }
    }, 100);
    
    if (document.querySelector('.landing-page')) {
        setTimeout(() => {
            if (typeof window.landingPageManager !== 'undefined') {
                console.log('✅ Landing Page Manager ya está cargado');
            } else if (typeof LandingPageManager !== 'undefined') {
                window.landingPageManager = new LandingPageManager();
                console.log('✅ Landing Page Manager inicializado correctamente');
                
                ['vestimenta', 'alimentacion', 'implementos-deportivos', 'suplementos'].forEach(categoria => {
                    const contenedor = document.getElementById(`productos-${categoria}`);
                    console.log(`Contenedor productos-${categoria}:`, contenedor ? '✅ Encontrado' : '❌ No encontrado');
                });
            } else {
                console.error('❌ LandingPageManager no está definido');
            }
        }, 500);
    }
});

// ==================================================
// FUNCIONES DE DEBUG Y UTILIDADES
// ==================================================

function debugGEDSystem() {
    console.group('🐛 DEBUG GED SYSTEM v3.0 - ESTADO COMPLETO');
    console.log('GED System:', window.gedSystem);
    console.log('Módulos cargados:', Object.keys(window.gedSystem?.modules || {}));
    console.log('Navbar Height:', window.gedSystem?.navbarHeight);
    console.log('Modo móvil:', window.gedSystem?.isMobile);
    console.log('Body padding-top:', document.body.style.paddingTop);
    console.log('jQuery cargado:', typeof $ !== 'undefined');
    console.log('Bootstrap cargado:', typeof bootstrap !== 'undefined');
    
    const main = document.querySelector('main#main');
    console.log('Main min-height:', main?.style.minHeight);
    
    console.groupEnd();
}

window.debugGEDSystem = debugGEDSystem;

window.reloadOffCanvasMenu = function() {
    if (window.gedSystem && window.gedSystem.modules.sidebar) {
        window.gedSystem.modules.sidebar.loadMobileMenu();
        console.log('🔄 Menú del off-canvas recargado manualmente');
    }
};

window.forceNavbarRecalculation = function() {
    if (window.gedSystem) {
        window.gedSystem.forceNavbarRecalculation();
        window.gedSystem.applyBodyCorrections();
    }
};

window.limpiarCarrito = function() {
    sessionStorage.removeItem('ged-carrito');
    if (window.landingPageManager) {
        window.landingPageManager.carrito = [];
        window.landingPageManager.actualizarContadorCarrito();
    }
    console.log('🧹 Carrito limpiado');
};

function debugLandingPage() {
    console.group('🐛 DEBUG LANDING PAGE - CON PRODUCTOS');
    console.log('Landing Page Manager:', window.landingPageManager);
    console.log('Productos cargados:', window.landingPageManager?.productos);
    console.log('Carrito:', window.landingPageManager?.carrito);
    console.log('Total vendidos:', window.landingPageManager?.totalVendidos);
    console.groupEnd();
}

window.debugLandingPage = debugLandingPage;

if (window.location.href.indexOf('localhost') > -1 || window.location.href.indexOf('debug') > -1) {
    setTimeout(() => {
        debugGEDSystem();
        console.log('🔧 Modo desarrollo activo - Debug functions disponibles');
    }, 2000);
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { GEDSystem, LandingPageManager, OffCanvasSidebar, NavbarManager, SchoolSearch, ComponentsManager };
}