/* Imagina Login — comportamiento del panel de ajustes */
function ilT(key) { return (window.ilAdmin && ilAdmin.i18n && ilAdmin.i18n[key]) || key; }
    // JavaScript para el panel moderno
document.addEventListener('DOMContentLoaded', function() {
    // Manejar selección de templates
    const templateCards = document.querySelectorAll('.imagina-template-card');
    templateCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remover clase active de todas las cards
            templateCards.forEach(c => c.classList.remove('active'));
            // Agregar clase active a la card seleccionada
            this.classList.add('active');
            // Marcar el radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', { bubbles: true })); }
        });
    });

    // Manejar cambios en los radio buttons
    const radioButtons = document.querySelectorAll('input[type="radio"][name^="il_"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            updateOptionsDisplay();
            updateColorValues();
            updateGradientPreviews();
        });
    });

    // Manejar cambios en color inputs
    const colorInputs = document.querySelectorAll('.imagina-color-input');
    colorInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateColorValues();
            updateGradientPreviews();
        });
    });

    // Manejar cambios en selects de gradiente
    const gradientSelects = document.querySelectorAll('select[name*="gradient"]');
    gradientSelects.forEach(select => {
        select.addEventListener('change', updateGradientPreviews);
    });

    // Función para mostrar/ocultar opciones
    function updateOptionsDisplay() {
        const bodyType = document.querySelector('input[name="il_body_bg_type"]:checked')?.value;
        const logoType = document.querySelector('input[name="il_logo_bg_type"]:checked')?.value;
        
        // Actualizar paneles del body
        document.querySelectorAll('.imagina-option-panel[data-target="body"]').forEach(panel => {
            panel.classList.remove('active');
            if (panel.dataset.type === bodyType) {
                panel.classList.add('active');
            }
        });

        // Actualizar paneles del logo
        document.querySelectorAll('.imagina-option-panel[data-target="logo"]').forEach(panel => {
            panel.classList.remove('active');
            if (panel.dataset.type === logoType) {
                panel.classList.add('active');
            }
        });
    }

    // Función para actualizar valores de color mostrados
    function updateColorValues() {
        colorInputs.forEach(input => {
            const valueSpan = input.parentNode.querySelector('.imagina-color-value');
            if (valueSpan) {
                valueSpan.textContent = input.value.toUpperCase();
            }
        });
    }

    // Función CORREGIDA para actualizar previews de gradientes
    function updateGradientPreviews() {
        // Preview del body
        const bodyType = document.querySelector('select[name="il_body_gradient_type"]')?.value;
        const bodyDirection = document.querySelector('select[name="il_body_gradient_direction"]')?.value;
        const bodyColor1 = document.querySelector('input[name="il_body_gradient_color1"]')?.value;
        const bodyColor2 = document.querySelector('input[name="il_body_gradient_color2"]')?.value;
        
        if (bodyType && bodyDirection && bodyColor1 && bodyColor2) {
            const bodyGradientCSS = generateGradientCSS(bodyType, bodyDirection, bodyColor1, bodyColor2);
            const bodyPreview = document.querySelector('.imagina-option-panel[data-type="gradient"][data-target="body"] .imagina-gradient-preview');
            if (bodyPreview) {
                bodyPreview.style.background = bodyGradientCSS;
            }
        }
        
        // Preview del logo
        const logoType = document.querySelector('select[name="il_logo_gradient_type"]')?.value;
        const logoDirection = document.querySelector('select[name="il_logo_gradient_direction"]')?.value;
        const logoColor1 = document.querySelector('input[name="il_logo_gradient_color1"]')?.value;
        const logoColor2 = document.querySelector('input[name="il_logo_gradient_color2"]')?.value;
        
        if (logoType && logoDirection && logoColor1 && logoColor2) {
            const logoGradientCSS = generateGradientCSS(logoType, logoDirection, logoColor1, logoColor2);
            const logoPreview = document.querySelector('.imagina-option-panel[data-type="gradient"][data-target="logo"] .imagina-gradient-preview');
            if (logoPreview) {
                logoPreview.style.background = logoGradientCSS;
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

    // Inicializar
    updateOptionsDisplay();
    updateColorValues();
    updateGradientPreviews();
    
    // Sliders: muestran su valor con la unidad de data-unit ("s" o "px").
    // Antes todos mostraban "s", también los del logo ("200s").
    document.querySelectorAll('.imagina-slider').forEach(slider => {
        const valueDisplay = slider.parentNode.querySelector('.imagina-slider-value');
        if (!valueDisplay) return;
        const render = () => {
            const zero = slider.dataset.zeroLabel;
            valueDisplay.textContent = (zero && Number(slider.value) === 0) ? zero : slider.value + (slider.dataset.unit || 's');
        };
        slider.addEventListener('input', render);
    });
});

// Funciones para toggles
function toggleColorOptions(checkbox) {
    const controls = document.getElementById('custom-color-controls');
    if (checkbox.checked) {
        controls.style.display = 'block';
        controls.style.animation = 'fadeInUp 0.3s ease';
    } else {
        controls.style.display = 'none';
    }
}

function toggleTransitionOptions(checkbox) {
    const controls = document.getElementById('transition-controls');
    if (checkbox.checked) {
        controls.style.display = 'block';
        controls.style.animation = 'fadeInUp 0.3s ease';
    } else {
        controls.style.display = 'none';
    }
}

function toggleBorderColor(checkbox) {
    const colorInput = checkbox.closest('.imagina-control-group').querySelector('input[name="il_logo_area_border_color"]');
    if (!checkbox.checked) {
        colorInput.value = '';
        colorInput.type = 'hidden';
    } else {
        colorInput.type = 'color';
        if (!colorInput.value || colorInput.value === '') colorInput.value = '#e3e8ee';
    }
}

function confirmReset() {
    if (confirm(ilT('confirmReset'))) {
        // URL sin escapar para HTML (wp_nonce_url convierte & en &amp; y rompe la URL en JS)
        window.location.href = ilAdmin.resetUrl;
    }
}

    function updateDurationDisplay(slider) { /* conservado por compatibilidad; el texto lo actualiza admin.js */ return;
    const valueDisplay = slider.parentNode.querySelector(".imagina-slider-value");
    if (valueDisplay) {
        valueDisplay.textContent = slider.value + "s";
    }
}

function updateTransitionPreview() {
    const select = document.querySelector("select[name=\"il_transition_type\"]");
    const demos = document.querySelectorAll(".transition-demo");
    
    demos.forEach(demo => {
        demo.classList.remove("active");
        if (demo.dataset.type === select.value) {
            demo.classList.add("active");
        }
    });
}

// Inicializar preview
document.addEventListener("DOMContentLoaded", function() {
    updateTransitionPreview();
});

// Presentación de fondo: elegir varias imágenes de la biblioteca
jQuery(function ($) {
    $(document).on('click', '.il-slides-button', function (e) {
        e.preventDefault();
        var $group = $(this).closest('.imagina-control-group');
        var $input = $group.find('input[name="il_body_slides"]');
        var frame = wp.media({ title: ilT('slidesTitle'), button: { text: ilT('slidesButton') }, multiple: 'add', library: { type: 'image' } });
        frame.on('open', function () {
            var selection = frame.state().get('selection');
            ($input.val() || '').split(',').filter(Boolean).forEach(function (id) {
                var attachment = wp.media.attachment(id); attachment.fetch(); selection.add(attachment);
            });
        });
        frame.on('select', function () {
            var items = frame.state().get('selection').toJSON();
            $input.val(items.map(function (a) { return a.id; }).join(',')).trigger('change');
            $group.find('.il-slides-preview').html(items.map(function (a) {
                var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                return '<img src="' + url + '" alt="">';
            }).join(''));
        });
        frame.open();
    });
});

// "Secuencia de animación": muestra cuándo empieza y termina cada parte según los sliders
jQuery(function ($) {
    function seq(part, label) {
        var delay = parseFloat($('[name="il_' + part + '_transition_delay"]').val()) || 0;
        var duration = parseFloat($('[name="il_' + part + '_transition_duration"]').val()) || 0;
        $('[data-il-seq="' + part + '"]').text(label + ': ' + delay.toFixed(2) + 's → ' + (delay + duration).toFixed(2) + 's');
    }
    function update() { seq('logo', ilT('logo')); seq('form', ilT('form')); }
    $(document).on('input change', '[name$="_transition_delay"], [name$="_transition_duration"]', update);
    update();
});

// ===== Vista previa en vivo =====
// Envía los ajustes del formulario (sin guardar) y recarga el login real dentro del panel.
jQuery(function ($) {
    var $drawer = $('#il-preview-drawer'), $iframe = $drawer.find('iframe'), $stage = $drawer.find('.il-preview-stage');
    var $form = $('form.imagina-form'), $status = $drawer.find('.il-preview-status');
    var devices = { desktop: [1280, 800], tablet: [820, 1100], mobile: [390, 844] };
    var device = 'desktop', timer = null, pending = null;
    if (!$drawer.length) return;

    function fit() {
        var size = devices[device], w = $stage.width(), h = $stage.height();
        var scale = Math.min(w / size[0], h / size[1], 1);
        $iframe.css({ width: size[0], height: size[1], transform: 'scale(' + scale + ')' });
    }

    function collect() {
        var settings = {};
        $.each($form.serializeArray(), function (_, field) {
            if (field.name.indexOf('il_') === 0) settings[field.name] = field.value;
        });
        return settings;
    }

    function refresh() {
        if ($drawer.prop('hidden')) return;
        if (pending) pending.abort();
        $status.text(ilT('updating'));
        pending = $.post(ajaxurl, { action: 'il_preview_store', _ajax_nonce: ilAdmin.previewNonce, settings: collect() })
            .done(function (res) {
                if (res && res.success) { $iframe.attr('src', res.data.url); }
                else { $status.text(ilT('updateFailed')); }
            })
            .fail(function (xhr) { if (xhr.statusText !== 'abort') $status.text(ilT('updateFailed')); });
    }
    $iframe.on('load', function () { $status.text(ilT('updated')); });

    function schedule() { clearTimeout(timer); timer = setTimeout(refresh, 600); }

    $('#il-live-preview-toggle').on('click', function () {
        var open = $drawer.prop('hidden');
        $drawer.prop('hidden', !open);
        $('body').toggleClass('il-preview-open', open);
        if (open) { fit(); refresh(); }
    });
    $drawer.on('click', '.il-preview-close', function () {
        $drawer.prop('hidden', true); $('body').removeClass('il-preview-open');
    });
    $drawer.on('click', '[data-device]', function () {
        device = $(this).data('device');
        $drawer.find('[data-device]').removeClass('is-active'); $(this).addClass('is-active');
        fit();
    });
    $(window).on('resize', fit);
    $form.on('input change', ':input', schedule);
});

// ===== Diseños rápidos =====
// Rellena el formulario con los valores del diseño (no guarda): el usuario lo ve en la vista previa y decide.
jQuery(function ($) {
    function setField(name, value) {
        var $fields = $('form.imagina-form [name="' + name + '"]');
        if (!$fields.length) return;
        var el = $fields[0];
        if (el.type === 'radio') {
            var radio = $fields.filter('[value="' + value + '"]')[0];
            if (!radio) return;
            var card = radio.closest('.imagina-template-card');
            if (card) { card.click(); return; } // la tarjeta marca, resalta y dispara "change"
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (el.type === 'checkbox') {
            el.checked = !!value;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            $(el).val(value);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    $(document).on('click', '.il-preset', function () {
        var settings = (ilAdmin.presets || {})[$(this).data('preset')];
        if (!settings) return;
        Object.keys(settings).forEach(function (name) { setField(name, settings[name]); });
        $('.il-preset').removeClass('is-active'); $(this).addClass('is-active');
        $('.il-preset-notice').text(ilAdmin.presetApplied);
    });
});

// "Vista previa de colores": sigue a los selectores de color en tiempo real
jQuery(function ($) {
    function paint() {
        $('[data-il-sample]').each(function () {
            var parts = $(this).data('ilSample').split(':');
            var value = $('[name="' + parts[0] + '"]').val();
            if (value) this.style[parts[1]] = value;
        });
    }
    $(document).on('input change', '[name="il_label_color"], [name="il_button_color"], [name="il_button_hover_color"], [name="il_link_color"]', paint);
});
