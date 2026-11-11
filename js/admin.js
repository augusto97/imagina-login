/* Imagina Login — comportamiento del panel de ajustes */
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
            if (radio) radio.checked = true;
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
    if (confirm('¿Estás seguro de que quieres restaurar todas las configuraciones por defecto? Esta acción no se puede deshacer.')) {
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
