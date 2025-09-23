jQuery(document).ready(function($) {
    
    // Inicializar el panel moderno
    initModernPanel();

    function initModernPanel() {
        // Actualizar previews de gradientes en tiempo real
        updateGradientPreviews();
        
        // Listeners para gradientes - CORREGIDOS
        $('select[name*="gradient_type"], select[name*="gradient_direction"]').on('change', updateGradientPreviews);
        $('input[name*="gradient_color"]').on('input change', updateGradientPreviews);
        
        // Listeners para overlays de video
        $('input[name="il_video_overlay_color"]').on('input change', updateVideoOverlayPreview);
        $('input[name="il_logo_overlay_color"]').on('input change', updateLogoOverlayPreview);
        
        // Inicializar previews
        updateVideoOverlayPreview();
        updateLogoOverlayPreview();
    }

    // Función CORREGIDA para actualizar previews de gradientes
    function updateGradientPreviews() {
        console.log('🎨 Actualizando previews de gradientes...');
        
        // Preview del body
        const bodyType = $('select[name="il_body_gradient_type"]').val();
        const bodyDirection = $('select[name="il_body_gradient_direction"]').val();
        const bodyColor1 = $('input[name="il_body_gradient_color1"]').val();
        const bodyColor2 = $('input[name="il_body_gradient_color2"]').val();
        
        if (bodyType && bodyDirection && bodyColor1 && bodyColor2) {
            const bodyGradientCSS = generateGradientCSS(bodyType, bodyDirection, bodyColor1, bodyColor2);
            const bodyPreview = $('.imagina-option-panel[data-type="gradient"][data-target="body"] .imagina-gradient-preview');
            if (bodyPreview.length) {
                bodyPreview.css('background', bodyGradientCSS);
                console.log('✅ Preview del body actualizado:', bodyGradientCSS);
            }
        }
        
        // Preview del logo
        const logoType = $('select[name="il_logo_gradient_type"]').val();
        const logoDirection = $('select[name="il_logo_gradient_direction"]').val();
        const logoColor1 = $('input[name="il_logo_gradient_color1"]').val();
        const logoColor2 = $('input[name="il_logo_gradient_color2"]').val();
        
        if (logoType && logoDirection && logoColor1 && logoColor2) {
            const logoGradientCSS = generateGradientCSS(logoType, logoDirection, logoColor1, logoColor2);
            const logoPreview = $('.imagina-option-panel[data-type="gradient"][data-target="logo"] .imagina-gradient-preview');
            if (logoPreview.length) {
                logoPreview.css('background', logoGradientCSS);
                console.log('✅ Preview del logo actualizado:', logoGradientCSS);
            }
        }
    }

    // Función CORREGIDA para generar CSS de gradiente
    function generateGradientCSS(type, direction, color1, color2) {
        const directionMap = {
            'vertical': 'to bottom',
            'horizontal': 'to right',
            'diagonal1': '45deg',
            'diagonal2': '-45deg'
        };
        
        if (type === 'radial') {
            return `radial-gradient(circle, ${color1}, ${color2})`;
        } else {
            const cssDirection = directionMap[direction] || 'to bottom';
            return `linear-gradient(${cssDirection}, ${color1}, ${color2})`;
        }
    }

    function updateVideoOverlayPreview() {
        const overlayValue = $('input[name="il_video_overlay_color"]').val();
        const preview = $('input[name="il_video_overlay_color"]').siblings('div');
        if (preview.length && overlayValue) {
            preview.css('background', overlayValue);
        }
    }

    function updateLogoOverlayPreview() {
        const overlayValue = $('input[name="il_logo_overlay_color"]').val();
        const preview = $('input[name="il_logo_overlay_color"]').siblings('div');
        if (preview.length && overlayValue && overlayValue !== 'transparent') {
            preview.css('background', overlayValue);
        } else if (preview.length) {
            preview.css('background', '#f0f0f0');
        }
    }

    // Funciones de subida mejoradas para el panel moderno
    function handleImageUploader(e) {
        e.preventDefault();
        const button = $(this);
        const inputField = button.siblings('input[type="hidden"]').first();
        const removeButton = button.siblings('.remove-image-button');
        const previewContainer = button.closest('.imagina-media-uploader').find('.imagina-media-preview');
        
        // Cambiar estado del botón
        const originalText = button.html();
        button.html('<span class="dashicons dashicons-update spin"></span> Cargando...').prop('disabled', true);

        const mediaFrame = wp.media({
            title: 'Seleccionar Imagen para el Fondo',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            inputField.val(attachment.id);
            
            // Crear preview moderno
            const imgElement = `<img src="${attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url}" 
                               alt="${attachment.alt || 'Imagen de fondo'}" 
                               style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">`;
            
            previewContainer.html(imgElement);
            
            // Mostrar botón de quitar y actualizar texto
            removeButton.show();
            button.html('<span class="dashicons dashicons-update"></span> Cambiar Imagen');
            
            // Mostrar notificación de éxito
            showNotification('Imagen subida correctamente', 'success');
        });

        mediaFrame.on('close', function() {
            button.html(originalText).prop('disabled', false);
        });

        mediaFrame.open();
    }

    // Función para manejar subida de videos
    function handleVideoUploader(e) {
        e.preventDefault();
        const button = $(this);
        const inputField = button.siblings('input[type="hidden"]').first();
        const removeButton = button.siblings('.remove-video-button');
        const previewContainer = button.closest('.imagina-media-uploader').find('.imagina-media-preview');
        
        // Cambiar estado del botón
        const originalText = button.html();
        button.html('<span class="dashicons dashicons-update spin"></span> Subiendo...').prop('disabled', true);

        const mediaFrame = wp.media({
            title: 'Seleccionar Video de Fondo',
            button: {
                text: 'Usar este video'
            },
            multiple: false,
            library: {
                type: 'video'
            }
        });

        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            
            // Validar tamaño del archivo (50MB = 52428800 bytes)
            if (attachment.filesizeInBytes && attachment.filesizeInBytes > 52428800) {
                showNotification('El video es demasiado grande. Máximo permitido: 50MB', 'error');
                button.html(originalText).prop('disabled', false);
                return;
            }
            
            inputField.val(attachment.id);
            
            // Crear preview del video
            const videoElement = `<video controls style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <source src="${attachment.url}" type="${attachment.mime}">
                Tu navegador no soporta videos HTML5.
            </video>
            <div style="margin-top: 10px; padding: 10px; background: #f0f9ff; border-radius: 6px; font-size: 13px; color: #0369a1;">
                <strong>📁 ${attachment.filename}</strong><br>
                📏 Tamaño: ${formatFileSize(attachment.filesizeInBytes || 0)}<br>
                ⏱️ El video se reproducirá automáticamente sin sonido en el login
            </div>`;
            
            previewContainer.html(videoElement);
            
            // Mostrar botón de quitar y actualizar texto
            removeButton.show();
            button.html('<span class="dashicons dashicons-video-alt3"></span> Cambiar Video');
            
            // Mostrar notificación de éxito
            showNotification('Video subido correctamente', 'success');
        });

        mediaFrame.on('close', function() {
            button.html(originalText).prop('disabled', false);
        });

        mediaFrame.open();
    }

    // Funciones para remover archivos
    function handleRemoveImage(e) {
        e.preventDefault();
        if (!confirm('¿Estás seguro de que quieres quitar esta imagen?')) {
            return;
        }
        
        const button = $(this);
        const inputField = button.siblings('input[type="hidden"]').first();
        const uploadButton = button.siblings('.upload-image-button');
        const previewContainer = button.closest('.imagina-media-uploader').find('.imagina-media-preview');

        inputField.val('');
        previewContainer.html(`
            <div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px;">
                <span class="dashicons dashicons-format-image" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
                <p>No hay imagen seleccionada</p>
            </div>
        `);
        button.hide();
        uploadButton.html('<span class="dashicons dashicons-upload"></span> Subir Imagen');
        
        showNotification('Imagen eliminada', 'success');
    }

    function handleRemoveVideo(e) {
        e.preventDefault();
        if (!confirm('¿Estás seguro de que quieres quitar este video?')) {
            return;
        }
        
        const button = $(this);
        const inputField = button.siblings('input[type="hidden"]').first();
        const uploadButton = button.siblings('.upload-video-button');
        const previewContainer = button.closest('.imagina-media-uploader').find('.imagina-media-preview');

        inputField.val('');
        previewContainer.html(`
            <div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px;">
                <span class="dashicons dashicons-video-alt3" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
                <p>No hay video seleccionado</p>
            </div>
        `);
        button.hide();
        uploadButton.html('<span class="dashicons dashicons-video-alt3"></span> Subir Video');
        
        showNotification('Video eliminado', 'success');
    }

    // Funciones auxiliares
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="imagina-notification imagina-notification-${type}">
                <span class="dashicons dashicons-${type === 'success' ? 'yes-alt' : type === 'error' ? 'warning' : 'info'}"></span>
                ${message}
            </div>
        `);
        
        // Agregar estilos si no existen
        if (!$('#imagina-notification-styles').length) {
            $('head').append(`
                <style id="imagina-notification-styles">
                .imagina-notification {
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    z-index: 10000;
                    padding: 12px 20px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 600;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    max-width: 300px;
                }
                .imagina-notification-success { background: #10b981; }
                .imagina-notification-error { background: #ef4444; }
                .imagina-notification-info { background: #3b82f6; }
                .imagina-notification.show { transform: translateX(0); }
                </style>
            `);
        }
        
        $('body').append(notification);
        
        // Mostrar animación
        setTimeout(() => notification.addClass('show'), 100);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Event listeners
    $(document).on('click', '.upload-image-button', handleImageUploader);
    $(document).on('click', '.upload-video-button', handleVideoUploader);
    $(document).on('click', '.remove-image-button', handleRemoveImage);
    $(document).on('click', '.remove-video-button', handleRemoveVideo);
    
    // Mejorar experiencia visual de color pickers
    $(document).on('input change', '.imagina-color-input', function() {
        const $this = $(this);
        const color = $this.val();
        const valueSpan = $this.closest('.imagina-color-picker').find('.imagina-color-value');
        const preview = $this.closest('.imagina-color-picker').find('.imagina-color-preview, [class*="preview"]');
        
        if (valueSpan.length) {
            valueSpan.text(color.toUpperCase());
        }
        if (preview.length) {
            preview.css('background-color', color);
        }
        
        // Actualizar gradientes cuando cambien los colores
        updateGradientPreviews();
    });

    // Agregar animación de rotación para iconos de carga
    if (!$('#imagina-spin-styles').length) {
        $('head').append(`
            <style id="imagina-spin-styles">
            .spin {
                animation: imagina-spin 1s linear infinite;
            }
            @keyframes imagina-spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            </style>
        `);
    }

    // Validación mejorada para archivos
    function validateMediaFile(file, type) {
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        const maxVideoSize = 50 * 1024 * 1024; // 50MB
        const maxImageSize = 10 * 1024 * 1024; // 10MB
        
        if (type === 'image') {
            if (!validImageTypes.includes(file.type)) {
                showNotification('Formato de imagen no válido. Usa JPG, PNG, GIF o WebP.', 'error');
                return false;
            }
            if (file.size > maxImageSize) {
                showNotification('La imagen es demasiado grande. Máximo 10MB.', 'error');
                return false;
            }
        } else if (type === 'video') {
            if (!validVideoTypes.includes(file.type)) {
                showNotification('Formato de video no válido. Usa MP4, WebM u OGG.', 'error');
                return false;
            }
            if (file.size > maxVideoSize) {
                showNotification('El video es demasiado grande. Máximo 50MB.', 'error');
                return false;
            }
        }
        
        return true;
    }

    // Función adicional para monitorear cambios en el panel de administración
    function monitorPanelChanges() {
        // Observar cambios en radio buttons para actualizar displays
        $('input[type="radio"][name^="il_"]').on('change', function() {
            setTimeout(function() {
                updateGradientPreviews();
            }, 100);
        });
        
        console.log('🎛️ Monitoreando cambios en el panel...');
    }
    
    // Inicializar monitoreo
    monitorPanelChanges();
    
    console.log('✅ Media Uploader inicializado correctamente');
});