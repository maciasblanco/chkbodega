/**
 * Dropdowns Dependientes para Pre-Registro de Escuelas
 * Maneja la carga dinámica de municipios y parroquias
 */

function initDropdownsDependientes(config) {
    $(document).ready(function() {
        console.log('=== INICIANDO DROPDOWNS DEPENDIENTES ===');
        
        const {
            urlMunicipios,
            urlParroquias,
            selectEstado = '#estado',
            selectMunicipio = '#municipio',
            selectParroquia = '#parroquia'
        } = config;
        
        // Referencias a los dropdowns
        const $estado = $(selectEstado);
        const $municipio = $(selectMunicipio);
        const $parroquia = $(selectParroquia);
        
        console.log('Dropdowns encontrados:');
        console.log('- Estado:', $estado.length, $estado.attr('id'));
        console.log('- Municipio:', $municipio.length, $municipio.attr('id'));
        console.log('- Parroquia:', $parroquia.length, $parroquia.attr('id'));
        
        // Verificar que todos los elementos existen
        if ($estado.length === 0 || $municipio.length === 0 || $parroquia.length === 0) {
            console.error('❌ No se encontraron todos los dropdowns necesarios');
            return;
        }
        
        // Evento para cargar municipios cuando cambia el estado
        $estado.on('change', function() {
            const estadoId = $(this).val();
            console.log('🔄 Estado cambiado:', estadoId);
            
            // Resetear dropdowns dependientes
            $municipio.empty().append('<option value="">Cargando municipios...</option>').prop('disabled', true);
            $parroquia.empty().append('<option value="">Seleccione municipio primero</option>').prop('disabled', true);
            
            // Limpiar mensajes de error previos
            $('.field-pre-registro-form-id_municipio').removeClass('has-error').find('.help-block').html('');
            $('.field-pre-registro-form-id_parroquia').removeClass('has-error').find('.help-block').html('');
            
            if (!estadoId) {
                console.log('❌ No se seleccionó estado');
                $municipio.empty().append('<option value="">Seleccione un estado</option>');
                return;
            }
            
            console.log('📡 Solicitando municipios para estado:', estadoId);
            
            // Petición AJAX para municipios
            $.ajax({
                url: urlMunicipios,
                type: 'GET',
                data: { edo: estadoId },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta municipios recibida:', response);
                    
                    // Limpiar dropdown
                    $municipio.empty();
                    
                    if (response.results && response.results.length > 0) {
                        console.log('📊 Municipios encontrados:', response.results.length);
                        
                        // Agregar opción por defecto
                        $municipio.append($('<option>', {
                            value: '',
                            text: 'Seleccione Municipio'
                        }));
                        
                        // Agregar cada municipio
                        $.each(response.results, function(index, municipio) {
                            $municipio.append($('<option>', {
                                value: municipio.id,
                                text: municipio.text
                            }));
                        });
                        
                        // Habilitar dropdown
                        $municipio.prop('disabled', false);
                        console.log('✅ Dropdown de municipios actualizado y habilitado');
                        console.log('Opciones disponibles:', $municipio.find('option').length);
                        
                    } else {
                        console.log('⚠️ No hay municipios disponibles');
                        $municipio.append('<option value="">No hay municipios disponibles</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error cargando municipios:', error);
                    console.log('Status:', status);
                    console.log('XHR:', xhr);
                    $municipio.empty().append('<option value="">Error al cargar municipios</option>');
                }
            });
        });
        
        // Evento para cargar parroquias cuando cambia el municipio
        $municipio.on('change', function() {
            const municipioId = $(this).val();
            console.log('🔄 Municipio cambiado:', municipioId);
            
            // Resetear dropdown dependiente
            $parroquia.empty().append('<option value="">Cargando parroquias...</option>').prop('disabled', true);
            
            // Limpiar mensajes de error previos
            $('.field-pre-registro-form-id_parroquia').removeClass('has-error').find('.help-block').html('');
            
            if (!municipioId) {
                console.log('❌ No se seleccionó municipio');
                $parroquia.empty().append('<option value="">Seleccione un municipio</option>');
                return;
            }
            
            console.log('📡 Solicitando parroquias para municipio:', municipioId);
            
            // Petición AJAX para parroquias
            $.ajax({
                url: urlParroquias,
                type: 'GET',
                data: { muni: municipioId },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Respuesta parroquias recibida:', response);
                    
                    // Limpiar dropdown
                    $parroquia.empty();
                    
                    if (response.results && response.results.length > 0) {
                        console.log('📊 Parroquias encontradas:', response.results.length);
                        
                        // Agregar opción por defecto
                        $parroquia.append($('<option>', {
                            value: '',
                            text: 'Seleccione Parroquia'
                        }));
                        
                        // Agregar cada parroquia
                        $.each(response.results, function(index, parroquia) {
                            $parroquia.append($('<option>', {
                                value: parroquia.id,
                                text: parroquia.text
                            }));
                        });
                        
                        // Habilitar dropdown
                        $parroquia.prop('disabled', false);
                        console.log('✅ Dropdown de parroquias actualizado y habilitado');
                        
                    } else {
                        console.log('⚠️ No hay parroquias disponibles');
                        $parroquia.append('<option value="">No hay parroquias disponibles</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error cargando parroquias:', error);
                    $parroquia.empty().append('<option value="">Error al cargar parroquias</option>');
                }
            });
        });
        
        // Validación personalizada del formulario
        $('#pre-registro-form').on('beforeValidate', function() {
            console.log('🔍 Iniciando validación del formulario...');
        });
        
        $('#pre-registro-form').on('afterValidate', function(event, messages, errorAttributes) {
            console.log('📋 Resultado validación:', errorAttributes);
        });
        
        // Debug inicial
        console.log('=== DROPDOWNS INICIALIZADOS ===');
        console.log('Estado value:', $estado.val());
        console.log('Municipio disabled:', $municipio.prop('disabled'));
        console.log('Parroquia disabled:', $parroquia.prop('disabled'));
    });
}

// Hacer la función disponible globalmente
window.initDropdownsDependientes = initDropdownsDependientes;