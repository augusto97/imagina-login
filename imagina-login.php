<?php
/**
 * Plugin Name: Imagina Login
 * Plugin URI:  https://imaginawp.com
 * Description: Customized wp login with 9 professional templates and advanced background options
 * Version:     2.3.7
 * Author:      IMAGINA WP
 * Author URI:  https://imaginawp.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: imagina-login
 */

if(!defined('ABSPATH')) {
    die;
}

/**
 * Función para ajustar el brillo de un color hexadecimal.
 */
if (!function_exists('adjustBrightness')) {
    function adjustBrightness($hex, $steps) {
        $steps = max(-255, min(255, $steps));
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
        $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
        $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        return '#' . $r_hex . $g_hex . $b_hex;
    }
}

/**
 * Obtener todas las opciones de Imagina Login en una sola consulta (OPTIMIZACIÓN)
 */
function il_get_all_options($reset = false) {
    static $cached_options = null;

    if ($reset) {
        $cached_options = null;
        return [];
    }

    if ($cached_options !== null) {
        return $cached_options;
    }

    // Lista de todas las opciones del plugin
    $option_keys = [
        'il_login_template',
        'il_body_bg_type', 'il_body_bg_color', 'il_body_background_image', 'il_body_background_video',
        'il_body_gradient_type', 'il_body_gradient_direction', 'il_body_gradient_color1', 'il_body_gradient_color2',
        'il_video_overlay_color',
        'il_logo_bg_type', 'il_logo_bg_color', 'il_logo_background_image',
        'il_logo_gradient_type', 'il_logo_gradient_direction', 'il_logo_gradient_color1', 'il_logo_gradient_color2',
        'il_logo_overlay_color', 'il_logo_max_size', 'il_custom_logo',
        'il_logo_area_height', 'il_logo_area_border_color',
        'il_use_custom_colors', 'il_label_color', 'il_button_color', 'il_button_hover_color', 'il_link_color',
        'il_enable_transitions', 'il_transition_type', 'il_transition_duration',
        'il_logo_transition_duration', 'il_logo_transition_delay',
        'il_form_transition_duration', 'il_form_transition_delay'
    ];

    // Valores por defecto
    $defaults = [
        'il_login_template' => 'classic',
        'il_body_bg_type' => 'color',
        'il_body_bg_color' => '#009bde',
        'il_body_background_image' => '',
        'il_body_background_video' => '',
        'il_body_gradient_type' => 'linear',
        'il_body_gradient_direction' => 'vertical',
        'il_body_gradient_color1' => '#009bde',
        'il_body_gradient_color2' => '#0056b3',
        'il_video_overlay_color' => 'rgba(0,0,0,0.3)',
        'il_logo_bg_type' => 'color',
        'il_logo_bg_color' => '#f9f9f9',
        'il_logo_background_image' => '',
        'il_logo_gradient_type' => 'linear',
        'il_logo_gradient_direction' => 'vertical',
        'il_logo_gradient_color1' => '#f9f9f9',
        'il_logo_gradient_color2' => '#e9ecef',
        'il_logo_overlay_color' => 'transparent',
        'il_logo_max_size' => '200',
        'il_custom_logo' => '',
        'il_logo_area_height' => '0',
        'il_logo_area_border_color' => '',
        'il_use_custom_colors' => false,
        'il_label_color' => '#009bde',
        'il_button_color' => '#009bde',
        'il_button_hover_color' => '#007ab8',
        'il_link_color' => '#009bde',
        'il_enable_transitions' => true,
        'il_transition_type' => 'fade',
        'il_transition_duration' => '0.5',
        'il_logo_transition_duration' => '0.4',
        'il_logo_transition_delay' => '0.05',
        'il_form_transition_duration' => '0.4',
        'il_form_transition_delay' => '0.15'
    ];

    // Obtener todas las opciones de una vez
    $options = [];
    foreach ($option_keys as $key) {
        $value = get_option($key);
        $options[$key] = ($value !== false) ? $value : $defaults[$key];
    }

    $cached_options = $options;
    return $options;
}

/**
 * Función para generar CSS de degradado - CORREGIDA
 */
function il_generate_gradient_css($type, $direction, $color1, $color2) {
    if (!$color1 || !$color2) return '';

    $direction_map = [
        'horizontal' => 'to right',
        'vertical' => 'to bottom',
        'diagonal1' => '45deg',
        'diagonal2' => '-45deg'
    ];

    if ($type === 'radial') {
        return "radial-gradient(circle, {$color1}, {$color2})";
    } else {
        $css_direction = isset($direction_map[$direction]) ? $direction_map[$direction] : 'to bottom';
        return "linear-gradient({$css_direction}, {$color1}, {$color2})";
    }
}

/**
 * Cache para los estilos dinámicos (OPTIMIZADO)
 */
function il_get_cached_dynamic_styles() {
    $cache_key = 'imagina_login_dynamic_styles_v10';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    // Generar estilos dinámicos
    $styles = il_generate_dynamic_styles();

    // Cache por 1 hora
    set_transient($cache_key, $styles, HOUR_IN_SECONDS);

    return $styles;
}

/**
 * Generar estilos dinámicos - OPTIMIZADA
 */
function il_generate_dynamic_styles() {
    // Obtener todas las opciones de una vez (OPTIMIZACIÓN)
    $opts = il_get_all_options();

    // Configuración del fondo del body
    $body_bg_type = $opts['il_body_bg_type'];
    $body_bg_color = $opts['il_body_bg_color'];
    $body_bg_image = $opts['il_body_background_image'];
    $body_gradient_type = $opts['il_body_gradient_type'];
    $body_gradient_direction = $opts['il_body_gradient_direction'];
    $body_gradient_color1 = $opts['il_body_gradient_color1'];
    $body_gradient_color2 = $opts['il_body_gradient_color2'];
    $video_overlay_color = $opts['il_video_overlay_color'];

    // Configuración del fondo del logo
    $logo_bg_type = $opts['il_logo_bg_type'];
    $logo_bg_color = $opts['il_logo_bg_color'];
    $logo_bg_image = $opts['il_logo_background_image'];
    $logo_gradient_type = $opts['il_logo_gradient_type'];
    $logo_gradient_direction = $opts['il_logo_gradient_direction'];
    $logo_gradient_color1 = $opts['il_logo_gradient_color1'];
    $logo_gradient_color2 = $opts['il_logo_gradient_color2'];
    $logo_overlay_color = $opts['il_logo_overlay_color'];

    $css = ':root {';
    
    // Variables CSS para fondos del body
    switch ($body_bg_type) {
        case 'color':
            $css .= '--background-fallback: ' . esc_attr($body_bg_color) . ';';
            break;
        case 'gradient':
            $gradient_css = il_generate_gradient_css($body_gradient_type, $body_gradient_direction, $body_gradient_color1, $body_gradient_color2);
            if ($gradient_css) {
                $css .= '--background-fallback: ' . $gradient_css . ';';
            }
            break;
        case 'image':
            if ($body_bg_image) {
                $image_url = wp_get_attachment_image_url($body_bg_image, 'full');
                if ($image_url) {
                    $css .= '--background-fallback: url(' . esc_url($image_url) . ');';
                    $css .= '--bg-size: cover;';
                    $css .= '--bg-repeat: no-repeat;';
                }
            }
            break;
        case 'video':
            $css .= '--background-fallback: ' . esc_attr($body_bg_color) . ';';
            if ($video_overlay_color) {
                $css .= '--video-overlay: ' . esc_attr($video_overlay_color) . ';';
            }
            break;
    }

    // Variables CSS para fondos del logo - CORREGIDAS
    $has_logo_image = false;
    switch ($logo_bg_type) {
        case 'color':
            $css .= '--logo-background-fallback: ' . esc_attr($logo_bg_color) . ';';
            break;
        case 'gradient':
            $gradient_css = il_generate_gradient_css($logo_gradient_type, $logo_gradient_direction, $logo_gradient_color1, $logo_gradient_color2);
            if ($gradient_css) {
                $css .= '--logo-background-fallback: ' . $gradient_css . ';';
            }
            break;
        case 'image':
            if ($logo_bg_image) {
                $image_url = wp_get_attachment_image_url($logo_bg_image, 'full');
                if ($image_url) {
                    $css .= '--logo-background-fallback: url(' . esc_url($image_url) . ');';
                    $css .= '--logo-bg-size: cover;';
                    $css .= '--logo-bg-repeat: no-repeat;';
                    $has_logo_image = true;
                }
            }
            break;
    }

    // Solo aplicar overlay si hay imagen Y si el overlay no es transparente
    if ($has_logo_image && $logo_overlay_color && $logo_overlay_color !== 'transparent') {
        $css .= '--logo-overlay: ' . esc_attr($logo_overlay_color) . ';';
    }

    $css .= '}';

    return [
        'css' => $css,
        'has_logo_image' => $has_logo_image,
        'logo_overlay_color' => $logo_overlay_color,
        'body_bg_type' => $body_bg_type,
        'logo_bg_type' => $logo_bg_type,
        'body_bg_video' => $opts['il_body_background_video'],
        'template' => $opts['il_login_template']
    ];
}

/**
 * Limpiar cache cuando se guardan opciones - OPTIMIZADO CON BATCH CLEARING
 */
function il_clear_styles_cache() {
    static $already_cleared = false;
    if ($already_cleared) {
        return;
    }
    $already_cleared = true;
    delete_transient('imagina_login_dynamic_styles_v10');
    il_get_all_options(true);
}

/**
 * Hook que escucha cambios en opciones del plugin - solo en admin
 */
function il_handle_option_update($option_name) {
    if (strpos($option_name, 'il_') === 0) {
        il_clear_styles_cache();
    }
}

/**
 * Registrar hooks de cache solo en admin para no afectar el frontend
 */
function il_register_cache_hooks() {
    add_action('updated_option', 'il_handle_option_update', 10, 1);
    add_action('added_option', 'il_handle_option_update', 10, 1);
}
add_action('admin_init', 'il_register_cache_hooks');

/**
 * Inyectar estilos críticos en el head TEMPRANO
 */
function il_inject_critical_styles() {
    $cached_styles = il_get_cached_dynamic_styles();

    echo '<style id="imagina-login-critical-css">' . $cached_styles['css'] . '</style>';

    // Preload del CSS principal
    echo '<link rel="preload" href="' . plugin_dir_url(__FILE__) . 'css/styles.css?v=2.3.7" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
    echo '<noscript><link rel="stylesheet" href="' . plugin_dir_url(__FILE__) . 'css/styles.css?v=2.3.7"></noscript>';

    // NO precargar imágenes para evitar que se vean antes de cargar todo
}
add_action('login_head', 'il_inject_critical_styles', 1);

/**
 * Función para añadir los estilos y scripts a la página de login - OPTIMIZADA
 */
function my_custom_login_assets() {
    // Obtener todas las opciones de una vez (OPTIMIZACIÓN)
    $opts = il_get_all_options();
    $cached_styles = il_get_cached_dynamic_styles();

    // Cargar CSS base
    wp_enqueue_style(
        'imagina-login',
        plugin_dir_url( __FILE__ ) . 'css/styles.css',
        array(),
        '2.3.7'
    );

    // Cargar CSS del template seleccionado
    $template = $cached_styles['template'];
    $template_file = '';
    switch ($template) {
        case 'split':
            $template_file = 'template-split.css';
            break;
        case 'fullscreen':
            $template_file = 'template-fullscreen.css';
            break;
        case 'glass':
            $template_file = 'template-glass.css';
            break;
        case 'sidebar':
            $template_file = 'template-sidebar.css';
            break;
        case 'sidebar-left':
            $template_file = 'template-sidebar-left.css';
            break;
        case 'sidebar-half':
            $template_file = 'template-sidebar-half.css';
            break;
        case 'sidebar-half-left':
            $template_file = 'template-sidebar-half-left.css';
            break;
        case 'boxed':
            $template_file = 'template-boxed.css';
            break;
        case 'classic':
        default:
            $template_file = 'template-classic.css';
            break;
    }

    wp_enqueue_style(
        'imagina-login-template',
        plugin_dir_url( __FILE__ ) . 'css/templates/' . $template_file,
        array('imagina-login'),
        '2.3.7'
    );

    // Agregar clase del template al body y clase de transiciones
    add_filter('login_body_class', function($classes) use ($template, $opts) {
        $classes[] = 'template-' . $template;

        // Agregar clase si las transiciones están activas
        if ($opts['il_enable_transitions']) {
            $classes[] = 'has-transitions';
        }

        return $classes;
    });

    // --- Lógica de logo: prioridad custom > tema > icono ---
    $logo_url = '';
    $plugin_logo_id = $opts['il_custom_logo'];
    if ($plugin_logo_id) {
        $logo_data = wp_get_attachment_image_src($plugin_logo_id, 'full');
        if ($logo_data && isset($logo_data[0])) {
            $logo_url = $logo_data[0];
        }
    }
    if (empty($logo_url)) {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo_data && isset($logo_data[0])) {
                $logo_url = $logo_data[0];
            }
        }
    }
    if (empty($logo_url)) {
        $site_icon_url = get_site_icon_url();
        if (!empty($site_icon_url)) {
            $logo_url = $site_icon_url;
        }
    }

    // *** SISTEMA DE COLORES OPTIMIZADO ***
    $use_custom_colors = $opts['il_use_custom_colors'];

    if ($use_custom_colors) {
        // Usar colores personalizados del plugin
        $primary_color = $opts['il_label_color'];
        $button_color = $opts['il_button_color'];
        $button_hover_color = $opts['il_button_hover_color'];
        $link_color = $opts['il_link_color'];
    } else {
        // Usar colores del tema (comportamiento original)
        $primary_color = get_theme_mod('primary_color', '#009bde');
        if (!$primary_color) {
            $primary_color = '#009bde';
        }
        $button_color = $primary_color;
        $button_hover_color = adjustBrightness($primary_color, -30);
        $link_color = $primary_color;
    }

    // *** TAMAÑO DEL LOGO ***
    $logo_max_size = $opts['il_logo_max_size'];
    $logo_area_height = $opts['il_logo_area_height'];
    $logo_area_border_color = $opts['il_logo_area_border_color'];

    // *** CONFIGURACIÓN DE TRANSICIONES ***
    $enable_transitions = $opts['il_enable_transitions'];
    $transition_type = $opts['il_transition_type'];
    $transition_duration = $opts['il_transition_duration'];
    $logo_duration = $opts['il_logo_transition_duration'];
    $logo_delay = $opts['il_logo_transition_delay'];
    $form_duration = $opts['il_form_transition_duration'];
    $form_delay = $opts['il_form_transition_delay'];

    // --- SVGs para los iconos (URL-encoded) ---
    $svg_eye_visible = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'%3E%3C/path%3E%3Ccircle cx='12' cy='12' r='3'%3E%3C/circle%3E%3C/svg%3E";
    $svg_eye_hidden = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24'%3E%3C/path%3E%3Cline x1='1' y1='1' x2='23' y2='23'%3E%3C/line%3E%3C/svg%3E";

    // CSS dinámico con colores personalizados y transiciones
    $custom_css = "
        :root {
            --primary-color: " . esc_attr($primary_color) . ";
            --button-color: " . esc_attr($button_color) . ";
            --button-hover-color: " . esc_attr($button_hover_color) . ";
            --link-color: " . esc_attr($link_color) . ";
            --logo-url: url('" . esc_url($logo_url) . "');
            --svg-eye-visible: url(\"" . $svg_eye_visible . "\");
            --svg-eye-hidden: url(\"" . $svg_eye_hidden . "\");
            --fallback-font: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            --transition-duration: " . esc_attr($transition_duration) . "s;
            --logo-duration: " . esc_attr($logo_duration) . "s;
            --logo-delay: " . esc_attr($logo_delay) . "s;
            --form-duration: " . esc_attr($form_duration) . "s;
            --form-delay: " . esc_attr($form_delay) . "s;
        }
        
        /* Aplicar colores personalizados */
        .login label {
            color: var(--primary-color) !important;
        }
        
        body.login div#login form .button-primary {
            background-color: var(--button-color) !important;
        }
        
        body.login div#login form .button-primary:hover,
        body.login div#login form .button-primary:focus {
            background-color: var(--button-hover-color) !important;
        }
        
        .login #nav a, 
        .login #backtoblog a {
            color: #6c757d;
        }
        
        .login #nav a:hover, 
        .login #backtoblog a:hover {
            color: var(--link-color) !important;
        }
        
        .login input[type=\"text\"]:focus,
        .login input[type=\"password\"]:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(0, 155, 222, 0.1) !important;
        }
        
        body.login div#login form input[type=checkbox]:checked {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        /* Forzar tamaño del logo solo si el usuario lo configuró */
        " . (intval($logo_max_size) !== 200 ? "
        body.login div#login h1 a {
            max-width: " . intval($logo_max_size) . "px !important;
            max-height: " . intval($logo_max_size) . "px !important;
        }" : "") . "

        /* Altura del área del logo solo si se configuró */
        " . (intval($logo_area_height) > 0 ? "
        body.login div#login h1 {
            min-height: " . intval($logo_area_height) . "px !important;
        }" : "") . "

        /* Borde del área del logo solo si se configuró */
        " . (!empty($logo_area_border_color) ? "
        body.login div#login h1 {
            border-bottom: 1px solid " . esc_attr($logo_area_border_color) . " !important;
        }" : "") . "
    ";

    // *** CSS DE TRANSICIONES CORREGIDO CON TRANSICIÓN DEL LOGO ***
    if ($enable_transitions) {
        $transition_css = "";
        
        switch ($transition_type) {
            case 'fade':
                $transition_css = "
                    /* Fondo del body cuando hay transiciones - aparece inmediatamente */
                    body.login.has-transitions::after {
                        content: '';
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: var(--background-fallback, #009bde);
                        background-size: cover;
                        background-position: center center;
                        background-repeat: no-repeat;
                        background-attachment: fixed;
                        opacity: 1;
                        z-index: -3;
                        pointer-events: none;
                    }

                    /* Transición específica para videos */
                    body.login #login-background-video {
                        opacity: 0;
                        transition: opacity var(--transition-duration) ease-in-out;
                        z-index: -2;
                    }

                    body.login #login-background-video.loaded {
                        opacity: 1;
                    }

                    /* Transición del logo - aparece primero con movimiento sutil */
                    body.login div#login h1 {
                        opacity: 0;
                        transform: translateY(20px) scale(0.95);
                        transition: opacity var(--logo-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--logo-delay),
                                    transform var(--logo-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--logo-delay);
                    }

                    body.login.logo-loaded div#login h1 {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }

                    /* Transición del formulario - aparece después con slide suave */
                    body.login div#login form {
                        opacity: 0;
                        transform: translateX(20px);
                        transition: opacity var(--form-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--form-delay),
                                    transform var(--form-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--form-delay);
                    }

                    body.login.form-loaded div#login form {
                        opacity: 1;
                        transform: translateX(0);
                    }
                ";
                break;
                
            case 'slidedown':
                $transition_css = "
                    /* Fondo del body cuando hay transiciones - aparece inmediatamente */
                    body.login.has-transitions::after {
                        content: '';
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: var(--background-fallback, #009bde);
                        background-size: cover;
                        background-position: center center;
                        background-repeat: no-repeat;
                        background-attachment: fixed;
                        opacity: 1;
                        z-index: -3;
                        pointer-events: none;
                    }

                    body.login #login-background-video {
                        opacity: 0;
                        transition: opacity var(--transition-duration) ease-in-out;
                        z-index: -2;
                    }

                    body.login #login-background-video.loaded {
                        opacity: 1;
                    }

                    /* Transición del logo deslizando desde arriba - más natural */
                    body.login div#login h1 {
                        opacity: 0;
                        transform: translateY(-60px);
                        transition: opacity var(--logo-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--logo-delay),
                                    transform var(--logo-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--logo-delay);
                    }

                    body.login.logo-loaded div#login h1 {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    /* Transición del formulario deslizando desde arriba - sincronizado */
                    body.login div#login form {
                        opacity: 0;
                        transform: translateY(-40px);
                        transition: opacity var(--form-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--form-delay),
                                    transform var(--form-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--form-delay);
                    }

                    body.login.form-loaded div#login form {
                        opacity: 1;
                        transform: translateY(0);
                    }
                ";
                break;
                
            case 'zoom':
                $transition_css = "
                    /* Fondo del body cuando hay transiciones - aparece inmediatamente */
                    body.login.has-transitions::after {
                        content: '';
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: var(--background-fallback, #009bde);
                        background-size: cover;
                        background-position: center center;
                        background-repeat: no-repeat;
                        background-attachment: fixed;
                        opacity: 1;
                        z-index: -3;
                        pointer-events: none;
                    }

                    body.login #login-background-video {
                        opacity: 0;
                        transition: opacity var(--transition-duration) ease-in-out;
                        z-index: -2;
                    }

                    body.login #login-background-video.loaded {
                        opacity: 1;
                    }

                    /* Transición del logo con zoom - más sutil y suave */
                    body.login div#login h1 {
                        opacity: 0;
                        transform: scale(0.85);
                        transition: opacity var(--logo-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--logo-delay),
                                    transform var(--logo-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--logo-delay);
                    }

                    body.login.logo-loaded div#login h1 {
                        opacity: 1;
                        transform: scale(1);
                    }

                    /* Transición del formulario con zoom - sincronizado */
                    body.login div#login form {
                        opacity: 0;
                        transform: scale(0.92);
                        transition: opacity var(--form-duration) cubic-bezier(0.4, 0, 0.2, 1) var(--form-delay),
                                    transform var(--form-duration) cubic-bezier(0.34, 1.56, 0.64, 1) var(--form-delay);
                    }

                    body.login.form-loaded div#login form {
                        opacity: 1;
                        transform: scale(1);
                    }
                ";
                break;
        }
        
        $custom_css .= $transition_css;
    }

    // Si no hay logo, CSS para mostrar título
    if (empty($logo_url)) {
        $custom_css .= "
            body.login div#login h1 a {
                background-image: none !important;
                text-indent: 0;
                height: auto;
                width: auto;
                color: var(--primary-color, #009bde);
                font-weight: bold;
                font-size: 24px;
                text-align: center;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            body.login div#login h1 a::before {
                content: '" . esc_js(get_bloginfo('name')) . "';
            }
        ";
    }

    wp_add_inline_style('imagina-login', $custom_css);

    // JavaScript MEJORADO con transiciones del logo CORREGIDAS
    $script = "
    document.addEventListener('DOMContentLoaded', function() {
        // *** OCULTAR CAPS-WARNING DE EXTENSIONES DEL NAVEGADOR ***
        function hideCapsWarning() {
            var capsElements = document.querySelectorAll('.caps-warning, #caps-warning');
            capsElements.forEach(function(el) {
                el.style.cssText = 'display:none!important;visibility:hidden!important;height:0!important;width:0!important;overflow:hidden!important;position:absolute!important;';
            });
        }

        // Ejecutar inmediatamente y observar cambios
        hideCapsWarning();

        var capsObserver = new MutationObserver(function(mutations) {
            hideCapsWarning();
        });

        capsObserver.observe(document.body, { childList: true, subtree: true });

        // *** SISTEMA DE TRANSICIONES OPTIMIZADO ***
        const enableTransitions = " . ($enable_transitions ? 'true' : 'false') . ";
        const transitionDuration = " . floatval($transition_duration) . " * 1000;

        if (enableTransitions) {
            // Fondo ya visible (::after opacity: 1)
            // Activar ambas animaciones casi inmediatamente - el CSS maneja los delays internos
            setTimeout(function() {
                document.body.classList.add('logo-loaded', 'form-loaded');
            }, 50);

            // Aplicar transición a videos si existen
            const video = document.querySelector('#login-background-video');
            if (video) {
                video.addEventListener('loadeddata', function() {
                    video.classList.add('loaded');
                });

                video.addEventListener('canplay', function() {
                    video.classList.add('loaded');
                });
            }
        } else {
            // Si las transiciones están desactivadas, mostrar todo inmediatamente
            document.body.classList.add('logo-loaded', 'form-loaded');
        }

        // *** TOGGLE DE CONTRASEÑA (mantener funcional) ***
        function initPasswordToggle() {
            const passwordInputs = document.querySelectorAll('input[type=\"password\"]');
            
            passwordInputs.forEach(function(passwordInput, index) {
                const wrapper = passwordInput.closest('.wp-pwd') || passwordInput.closest('p');
                if (!wrapper) return;

                let toggleButton = wrapper.querySelector('button.wp-hide-pw');
                
                if (!toggleButton) {
                    toggleButton = document.createElement('button');
                    toggleButton.type = 'button';
                    toggleButton.className = 'wp-hide-pw';
                    toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
                    
                    const iconSpan = document.createElement('span');
                    iconSpan.className = 'dashicons dashicons-visibility';
                    toggleButton.appendChild(iconSpan);
                    
                    wrapper.style.position = 'relative';
                    wrapper.classList.add('wp-pwd');
                    wrapper.appendChild(toggleButton);
                }

                const iconSpan = toggleButton.querySelector('span');
                if (!iconSpan) return;

                const newToggleButton = toggleButton.cloneNode(true);
                toggleButton.parentNode.replaceChild(newToggleButton, toggleButton);
                toggleButton = newToggleButton;
                const newIconSpan = toggleButton.querySelector('span');

                toggleButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const currentType = passwordInput.getAttribute('type');

                    if (currentType === 'password') {
                        passwordInput.setAttribute('type', 'text');
                        if (newIconSpan) {
                            newIconSpan.classList.remove('dashicons-visibility');
                            newIconSpan.classList.add('dashicons-hidden');
                        }
                        toggleButton.setAttribute('aria-label', 'Ocultar contraseña');
                    } else {
                        passwordInput.setAttribute('type', 'password');
                        if (newIconSpan) {
                            newIconSpan.classList.remove('dashicons-hidden');
                            newIconSpan.classList.add('dashicons-visibility');
                        }
                        toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
                    }
                });
            });
            
            const rememberCheckbox = document.querySelector('input[name=\"rememberme\"]');
            if (rememberCheckbox) {
                rememberCheckbox.setAttribute('aria-describedby', 'remember-description');
            }
        }

        initPasswordToggle();
        setTimeout(initPasswordToggle, 100);

        // MutationObserver optimizado con auto-disconnect (OPTIMIZACIÓN)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.querySelector && node.querySelector('input[type=\"password\"]'))) {
                            setTimeout(initPasswordToggle, 50);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Auto-disconnect después de 3 segundos para evitar consumo innecesario de recursos
        setTimeout(function() {
            observer.disconnect();
        }, 3000);
    });
    ";

    wp_register_script('imagina-login-toggle', '', array('jquery'), '2.3.7', true);
    wp_enqueue_script('imagina-login-toggle');
    wp_add_inline_script('imagina-login-toggle', $script);
}
add_action('login_enqueue_scripts', 'my_custom_login_assets', 10);

/**
 * Función para quitar los estilos CSS por defecto de la página de login.
 */
function dequeue_default_login_styles() {
    wp_dequeue_style('l10n');
    wp_dequeue_style('forms');
    wp_dequeue_style('login');
}
add_action('login_enqueue_scripts', 'dequeue_default_login_styles', 11);

/**
 * Modo preview: cargar un template falso de login en el frontend.
 * Usa template_include para servir una replica del login sin pasar por wp-login.php.
 */
function il_login_preview_template($template) {
    if (!isset($_GET['il_login_preview'])) {
        return $template;
    }
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return $template;
    }

    // Servir la pagina de preview
    il_render_login_preview();
    exit;
}
add_filter('template_include', 'il_login_preview_template', 999);

/**
 * Renderizar la replica del login para preview
 */
function il_render_login_preview() {
    // Disparar login_init para que nuestro plugin y otros carguen lo necesario
    do_action('login_init');

    // Simular las clases del body de wp-login.php
    $opts = il_get_all_options();
    $template = $opts['il_login_template'];
    $body_classes = ['login', 'wp-core-ui', 'login-action-login'];
    $body_classes[] = 'template-' . $template;
    if ($opts['il_enable_transitions']) {
        $body_classes[] = 'has-transitions';
    }

    // Obtener el logo (misma lógica que my_custom_login_assets)
    $logo_url = '';
    $plugin_logo_id = $opts['il_custom_logo'];
    if ($plugin_logo_id) {
        $logo_data = wp_get_attachment_image_src($plugin_logo_id, 'full');
        if ($logo_data && isset($logo_data[0])) {
            $logo_url = $logo_data[0];
        }
    }
    if (empty($logo_url)) {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo_data && isset($logo_data[0])) {
                $logo_url = $logo_data[0];
            }
        }
    }
    if (empty($logo_url)) {
        $site_icon_url = get_site_icon_url();
        if (!empty($site_icon_url)) {
            $logo_url = $site_icon_url;
        }
    }
    $logo_text = get_bloginfo('name');

    ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title><?php echo esc_html(get_bloginfo('name')); ?> &mdash; Login Preview</title>
<?php
    // Cargar estilos base de login de WordPress
    wp_enqueue_style('login');
    // Disparar hooks en el mismo orden que wp-login.php
    do_action('login_enqueue_scripts');
    wp_print_styles();
    wp_print_head_scripts();
    do_action('login_head');
?>
</head>
<body class="<?php echo esc_attr(implode(' ', $body_classes)); ?>">
<div id="login">
    <h1>
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($logo_text); ?></a>
    </h1>
    <form name="loginform" id="loginform" action="#" method="post">
        <p>
            <label for="user_login"><?php esc_html_e('Username or Email Address'); ?></label>
            <input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" autocomplete="off" readonly />
        </p>
        <p>
            <label for="user_pass"><?php esc_html_e('Password'); ?></label>
            <div class="wp-pwd">
                <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="off" readonly />
            </div>
        </p>
        <p class="forgetmenot">
            <input name="rememberme" type="checkbox" id="rememberme" value="forever" />
            <label for="rememberme"><?php esc_html_e('Remember Me'); ?></label>
        </p>
        <p class="submit">
            <input type="button" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Log In'); ?>" />
        </p>
    </form>
    <p id="nav">
        <a href="#"><?php esc_html_e('Lost your password?'); ?></a>
    </p>
    <p id="backtoblog">
        <a href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php echo esc_html(sprintf(__('Go to %s'), get_bloginfo('name'))); ?></a>
    </p>
</div>
<?php
    do_action('login_footer');
    wp_print_footer_scripts();
?>
</body>
</html>
<?php
}

// --- Filtros para el comportamiento del logo y el formulario ---
add_filter('login_display_language_dropdown', '__return_false');

function custom_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'custom_login_logo_url');

function custom_login_logo_url_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'custom_login_logo_url_title');

/**
 * Inyectar elementos dinámicos (videos, overlays, etc.)
 */
function il_inject_dynamic_elements() {
    $cached_styles = il_get_cached_dynamic_styles();
    
    // Script para overlay del logo (solo si es necesario)
    if ($cached_styles['has_logo_image'] && $cached_styles['logo_overlay_color'] && $cached_styles['logo_overlay_color'] !== 'transparent') {
        echo '<script>
            (function() {
                function addLogoOverlay() {
                    const logoContainer = document.querySelector("body.login div#login h1");
                    if (logoContainer) {
                        logoContainer.classList.add("has-image-background");
                    }
                }
                if (document.readyState === "loading") {
                    document.addEventListener("DOMContentLoaded", addLogoOverlay);
                } else {
                    addLogoOverlay();
                }
            })();
        </script>';
    }

    // Video de fondo (lazy load)
    if ($cached_styles['body_bg_type'] === 'video' && $cached_styles['body_bg_video']) {
        $video_url = wp_get_attachment_url($cached_styles['body_bg_video']);
        if ($video_url) {
            echo '<script>
                (function() {
                    function loadVideo() {
                        // Solo cargar video si no está en móvil (para performance)
                        if (window.innerWidth > 768) {
                            const video = document.createElement("video");
                            video.id = "login-background-video";
                            video.src = "' . esc_url($video_url) . '";
                            video.autoplay = true;
                            video.loop = true;
                            video.muted = true;
                            video.playsInline = true;
                            video.style.pointerEvents = "none";
                            video.setAttribute("preload", "metadata");
                            document.body.appendChild(video);
                        }
                    }
                    
                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", loadVideo);
                    } else {
                        loadVideo();
                    }
                })();
            </script>';
        }
    }
}
add_action('login_footer', 'il_inject_dynamic_elements', 5);

/**
 * Crear la página dentro del menú de Ajustes
 */
function il_create_admin_menu() {
    add_options_page(
        'Imagina Login',
        'Imagina Login',
        'manage_options',
        'imagina-login-settings',
        'il_settings_page_html'
    );
}
add_action('admin_menu', 'il_create_admin_menu');

/**
 * Registrar todos los ajustes
 */
function il_register_settings() {
    // Template seleccionado
    register_setting('imagina_login_options', 'il_login_template', ['type' => 'string', 'default' => 'classic']);

    // Configuración del fondo del body
    register_setting('imagina_login_options', 'il_body_bg_type', ['type' => 'string', 'default' => 'color']);
    register_setting('imagina_login_options', 'il_body_bg_color', ['type' => 'string', 'default' => '#009bde']);
    register_setting('imagina_login_options', 'il_body_background_image', ['type' => 'integer', 'sanitize_callback' => 'absint']);
    register_setting('imagina_login_options', 'il_body_background_video', ['type' => 'integer', 'sanitize_callback' => 'absint']);
    register_setting('imagina_login_options', 'il_body_gradient_type', ['type' => 'string', 'default' => 'linear']);
    register_setting('imagina_login_options', 'il_body_gradient_direction', ['type' => 'string', 'default' => 'vertical']);
    register_setting('imagina_login_options', 'il_body_gradient_color1', ['type' => 'string', 'default' => '#009bde']);
    register_setting('imagina_login_options', 'il_body_gradient_color2', ['type' => 'string', 'default' => '#0056b3']);
    register_setting('imagina_login_options', 'il_video_overlay_color', ['type' => 'string', 'default' => 'rgba(0,0,0,0.3)']);

    // Configuración del fondo del logo
    register_setting('imagina_login_options', 'il_logo_bg_type', ['type' => 'string', 'default' => 'color']);
    register_setting('imagina_login_options', 'il_logo_bg_color', ['type' => 'string', 'default' => '#f9f9f9']);
    register_setting('imagina_login_options', 'il_logo_background_image', ['type' => 'integer', 'sanitize_callback' => 'absint']);
    register_setting('imagina_login_options', 'il_logo_gradient_type', ['type' => 'string', 'default' => 'linear']);
    register_setting('imagina_login_options', 'il_logo_gradient_direction', ['type' => 'string', 'default' => 'vertical']);
    register_setting('imagina_login_options', 'il_logo_gradient_color1', ['type' => 'string', 'default' => '#f9f9f9']);
    register_setting('imagina_login_options', 'il_logo_gradient_color2', ['type' => 'string', 'default' => '#e9ecef']);
    register_setting('imagina_login_options', 'il_logo_overlay_color', ['type' => 'string', 'default' => 'transparent']);
    register_setting('imagina_login_options', 'il_logo_max_size', ['type' => 'string', 'default' => '200']);
    register_setting('imagina_login_options', 'il_custom_logo', ['type' => 'integer', 'sanitize_callback' => 'absint']);
    register_setting('imagina_login_options', 'il_logo_area_height', ['type' => 'string', 'default' => '0']);
    register_setting('imagina_login_options', 'il_logo_area_border_color', ['type' => 'string', 'default' => '']);

    // *** NUEVOS AJUSTES DE COLORES ***
    register_setting('imagina_login_options', 'il_use_custom_colors', ['type' => 'boolean', 'default' => false]);
    register_setting('imagina_login_options', 'il_label_color', ['type' => 'string', 'default' => '#009bde']);
    register_setting('imagina_login_options', 'il_button_color', ['type' => 'string', 'default' => '#009bde']);
    register_setting('imagina_login_options', 'il_button_hover_color', ['type' => 'string', 'default' => '#007ab8']);
    register_setting('imagina_login_options', 'il_link_color', ['type' => 'string', 'default' => '#009bde']);

    // *** NUEVOS AJUSTES DE TRANSICIONES ***
    register_setting('imagina_login_options', 'il_enable_transitions', ['type' => 'boolean', 'default' => true]);
    register_setting('imagina_login_options', 'il_transition_type', ['type' => 'string', 'default' => 'fade']);
    register_setting('imagina_login_options', 'il_transition_duration', ['type' => 'string', 'default' => '0.5']);
    register_setting('imagina_login_options', 'il_logo_transition_duration', ['type' => 'string', 'default' => '0.4']);
    register_setting('imagina_login_options', 'il_logo_transition_delay', ['type' => 'string', 'default' => '0.05']);
    register_setting('imagina_login_options', 'il_form_transition_duration', ['type' => 'string', 'default' => '0.4']);
    register_setting('imagina_login_options', 'il_form_transition_delay', ['type' => 'string', 'default' => '0.15']);

    // Secciones
    add_settings_section('il_template_section', 'Diseño del Login', null, 'imagina-login-settings');
    add_settings_section('il_body_section', 'Configuración del Fondo de la Página', null, 'imagina-login-settings');
    add_settings_section('il_logo_section', 'Configuración del Fondo del Logo', null, 'imagina-login-settings');
    add_settings_section('il_colors_section', 'Colores del Formulario', null, 'imagina-login-settings');
    add_settings_section('il_transitions_section', 'Transiciones y Animaciones', null, 'imagina-login-settings');
}
add_action('admin_init', 'il_register_settings');

/**
 * HTML de la página de configuración moderna - ACTUALIZADA CON TEMPLATES
 */
function il_settings_page_html() {
    // Obtener valores actuales
    $selected_template = get_option('il_login_template', 'classic');
    $body_bg_type = get_option('il_body_bg_type', 'color');
    $logo_bg_type = get_option('il_logo_bg_type', 'color');
    $use_custom_colors = get_option('il_use_custom_colors', false);
    $enable_transitions = get_option('il_enable_transitions', true);
    ?>
    <div class="wrap imagina-login-admin">
        <div class="imagina-header">
            <div class="imagina-header-content">
                <div class="imagina-logo-section">
                    <h1><span class="imagina-icon">🎨</span> Imagina Login</h1>
                    <p class="imagina-subtitle">Personaliza tu página de login de WordPress</p>
                </div>
                <div class="imagina-preview-section">
                    <a href="<?php echo esc_url(add_query_arg('il_login_preview', '1', home_url())); ?>" target="_blank" class="imagina-preview-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        Ver Login
                    </a>
                </div>
            </div>
        </div>

        <form action="options.php" method="post" class="imagina-form">
            <?php settings_fields('imagina_login_options'); ?>

            <div class="imagina-sections">
                <!-- NUEVA SECCIÓN: SELECTOR DE TEMPLATES -->
                <div class="imagina-section imagina-template-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🎭</span> Diseño del Login</h2>
                        <p>Elige el diseño que mejor se adapte a tu marca</p>
                    </div>

                    <div class="imagina-section-content">
                        <div class="imagina-template-grid">
                            <!-- Template 1: Clásico -->
                            <label class="imagina-template-card <?php echo $selected_template === 'classic' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="classic" <?php checked($selected_template, 'classic'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup classic-mockup">
                                        <div class="mockup-left"></div>
                                        <div class="mockup-right">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>🏢 Clásico Dos Columnas</h3>
                                    <p>Logo a la izquierda, formulario a la derecha. Ideal para empresas corporativas.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 2: Sidebar Izquierda -->
                            <label class="imagina-template-card <?php echo $selected_template === 'sidebar-left' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="sidebar-left" <?php checked($selected_template, 'sidebar-left'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup sidebar-mockup" style="grid-template-columns: 1fr 2fr;">
                                        <div class="mockup-sidebar-panel" style="box-shadow: 2px 0 8px rgba(0,0,0,0.1); order: 1;">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                        <div class="mockup-sidebar-bg" style="order: 2;"></div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>◀️ Sidebar Izquierda</h3>
                                    <p>Panel lateral izquierdo full height. Variante invertida del sidebar.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 3: Sidebar 50/50 Derecha -->
                            <label class="imagina-template-card <?php echo $selected_template === 'sidebar-half' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="sidebar-half" <?php checked($selected_template, 'sidebar-half'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup sidebar-mockup" style="grid-template-columns: 1fr 1fr;">
                                        <div class="mockup-sidebar-bg"></div>
                                        <div class="mockup-sidebar-panel">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>⚖️ Sidebar 50/50 Derecha</h3>
                                    <p>Diseño equilibrado 50/50 con sidebar derecho. Perfecto para balance visual.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 4: Sidebar 50/50 Izquierda -->
                            <label class="imagina-template-card <?php echo $selected_template === 'sidebar-half-left' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="sidebar-half-left" <?php checked($selected_template, 'sidebar-half-left'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup sidebar-mockup" style="grid-template-columns: 1fr 1fr;">
                                        <div class="mockup-sidebar-panel" style="box-shadow: 2px 0 8px rgba(0,0,0,0.1); order: 1;">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                        <div class="mockup-sidebar-bg" style="order: 2;"></div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>⚖️ Sidebar 50/50 Izquierda</h3>
                                    <p>Diseño equilibrado 50/50 con sidebar izquierdo. Balance con énfasis izquierdo.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 5: Pantalla Dividida -->
                            <label class="imagina-template-card <?php echo $selected_template === 'split' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="split" <?php checked($selected_template, 'split'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup split-mockup">
                                        <div class="mockup-left-big"></div>
                                        <div class="mockup-right-small">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>🎨 Pantalla Dividida</h3>
                                    <p>Split 60/40 con efecto visual impactante. Para agencias creativas.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 6: Full Screen -->
                            <label class="imagina-template-card <?php echo $selected_template === 'fullscreen' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="fullscreen" <?php checked($selected_template, 'fullscreen'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup fullscreen-mockup">
                                        <div class="mockup-full-header"></div>
                                        <div class="mockup-full-content">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                        <div class="mockup-full-footer"></div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>🖥️ Pantalla Completa</h3>
                                    <p>Login cubre toda la pantalla. Ideal para aplicaciones web y SaaS.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 7: Glassmorphism -->
                            <label class="imagina-template-card <?php echo $selected_template === 'glass' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="glass" <?php checked($selected_template, 'glass'); ?>>
                                <div class="template-preview glass-preview">
                                    <div class="template-mockup glass-mockup">
                                        <div class="mockup-glass-header"></div>
                                        <div class="mockup-glass-content">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>💎 Glassmorphism</h3>
                                    <p>Efecto cristal moderno sobre fondo. Para startups tech y apps premium.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 8: Sidebar Derecha -->
                            <label class="imagina-template-card <?php echo $selected_template === 'sidebar' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="sidebar" <?php checked($selected_template, 'sidebar'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup sidebar-mockup">
                                        <div class="mockup-sidebar-bg"></div>
                                        <div class="mockup-sidebar-panel">
                                            <div class="mock-input"></div>
                                            <div class="mock-input"></div>
                                            <div class="mock-button"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>▶️ Sidebar Derecha</h3>
                                    <p>Panel lateral derecho full height. Perfecto para intranets y sistemas de gestión.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>

                            <!-- Template 9: Boxed -->
                            <label class="imagina-template-card <?php echo $selected_template === 'boxed' ? 'active' : ''; ?>">
                                <input type="radio" name="il_login_template" value="boxed" <?php checked($selected_template, 'boxed'); ?>>
                                <div class="template-preview">
                                    <div class="template-mockup boxed-mockup">
                                        <div class="mockup-boxed-container">
                                            <div class="mockup-boxed-header"></div>
                                            <div class="mockup-boxed-content">
                                                <div class="mock-input"></div>
                                                <div class="mock-input"></div>
                                                <div class="mock-button"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-info">
                                    <h3>📦 Centrado Compacto</h3>
                                    <p>Caja pequeña flotante sobre fondo. Ideal para sitios minimalistas.</p>
                                </div>
                                <span class="template-check">✓</span>
                            </label>
                        </div>
                    </div>
                </div>

            <div class="imagina-sections">
                <!-- Sección Fondo de Página -->
                <div class="imagina-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🖼️</span> Fondo de la Página</h2>
                        <p>Personaliza el fondo completo de tu página de login</p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-type-selector">
                            <label class="imagina-main-label">Tipo de fondo:</label>
                            <div class="imagina-type-tabs">
                                <input type="radio" name="il_body_bg_type" value="color" id="body-color" <?php checked($body_bg_type, 'color'); ?>>
                                <label for="body-color" class="imagina-tab">
                                    <span class="tab-icon">🎨</span>
                                    <span class="tab-text">Color</span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="gradient" id="body-gradient" <?php checked($body_bg_type, 'gradient'); ?>>
                                <label for="body-gradient" class="imagina-tab">
                                    <span class="tab-icon">🌈</span>
                                    <span class="tab-text">Degradado</span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="image" id="body-image" <?php checked($body_bg_type, 'image'); ?>>
                                <label for="body-image" class="imagina-tab">
                                    <span class="tab-icon">🖼️</span>
                                    <span class="tab-text">Imagen</span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="video" id="body-video" <?php checked($body_bg_type, 'video'); ?>>
                                <label for="body-video" class="imagina-tab">
                                    <span class="tab-icon">🎬</span>
                                    <span class="tab-text">Video</span>
                                </label>
                            </div>
                        </div>

                        <div class="imagina-options-container">
                            <?php il_render_modern_body_options(); ?>
                        </div>
                    </div>
                </div>

                <!-- Sección Logo e Imagen -->
                <div class="imagina-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🏢</span> Logo y Área del Logo</h2>
                        <p>Sube tu logo y personaliza el fondo del área donde aparece</p>
                    </div>

                    <div class="imagina-section-content">
                        <?php
                        $custom_logo_id = get_option('il_custom_logo', '');
                        $current_logo_url = '';
                        if ($custom_logo_id) {
                            $current_logo_url = wp_get_attachment_image_url($custom_logo_id, 'medium');
                        }
                        ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="imagina-control-group">
                                <label class="imagina-control-label">Imagen del logo</label>
                                <div class="imagina-media-uploader">
                                    <input type="hidden" name="il_custom_logo" value="<?php echo esc_attr($custom_logo_id); ?>">
                                    <button type="button" class="imagina-upload-btn upload-image-button" style="margin-bottom: 8px;">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php echo $custom_logo_id ? 'Cambiar Logo' : 'Subir Logo'; ?>
                                    </button>
                                    <?php if ($custom_logo_id): ?>
                                    <button type="button" class="imagina-upload-btn remove-image-button" style="background: #ef4444; margin-bottom: 8px;">
                                        <span class="dashicons dashicons-trash"></span>
                                        Quitar
                                    </button>
                                    <?php endif; ?>
                                    <div class="imagina-media-preview">
                                        <?php if ($current_logo_url): ?>
                                            <img src="<?php echo esc_url($current_logo_url); ?>" style="max-width: 100%; max-height: 120px; border-radius: 6px; display: block;">
                                        <?php else: ?>
                                            <div style="padding: 20px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 6px;">
                                                <span class="dashicons dashicons-format-image" style="font-size: 24px; display: block; margin-bottom: 4px;"></span>
                                                <p style="margin: 0; font-size: 12px;">Usa el logo del tema</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;">Si no subes uno, se usa el logo del tema o el icono del sitio.</p>
                                </div>
                            </div>

                            <div class="imagina-control-group">
                                <label class="imagina-control-label">Tamaño del logo</label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_max_size" class="imagina-slider" min="60" max="400" step="10" value="<?php echo esc_attr(get_option('il_logo_max_size', '200')); ?>" oninput="this.parentNode.querySelector('.imagina-slider-value').textContent = this.value + 'px'">
                                    <span class="imagina-slider-value"><?php echo esc_html(get_option('il_logo_max_size', '200')); ?>px</span>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;">Ajusta entre 60px y 400px.</p>
                            </div>
                        </div>

                        <?php
                        $logo_area_height = get_option('il_logo_area_height', '0');
                        $logo_area_border_color = get_option('il_logo_area_border_color', '');
                        ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="imagina-control-group">
                                <label class="imagina-control-label">Altura del área del logo</label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_area_height" class="imagina-slider" min="0" max="600" step="10" value="<?php echo esc_attr($logo_area_height); ?>" oninput="this.parentNode.querySelector('.imagina-slider-value').textContent = this.value == 0 ? 'Auto' : this.value + 'px'">
                                    <span class="imagina-slider-value"><?php echo intval($logo_area_height) === 0 ? 'Auto' : esc_html($logo_area_height) . 'px'; ?></span>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;">0 = altura automática según contenido.</p>
                            </div>

                            <div class="imagina-control-group">
                                <label class="imagina-control-label">Borde separador</label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="<?php echo empty($logo_area_border_color) ? 'hidden' : 'color'; ?>" name="il_logo_area_border_color" value="<?php echo esc_attr(!empty($logo_area_border_color) ? $logo_area_border_color : ''); ?>" class="imagina-color-input" style="width: 36px; height: 36px;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #374151; cursor: pointer;">
                                        <input type="checkbox" id="il_border_toggle" <?php echo !empty($logo_area_border_color) ? 'checked' : ''; ?> onchange="toggleBorderColor(this)" style="width: 16px; height: 16px;">
                                        Mostrar borde
                                    </label>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;">Línea entre el logo y el formulario.</p>
                            </div>
                        </div>

                        <div class="imagina-type-selector">
                            <label class="imagina-main-label">Fondo del área del logo:</label>
                            <div class="imagina-type-tabs">
                                <input type="radio" name="il_logo_bg_type" value="color" id="logo-color" <?php checked($logo_bg_type, 'color'); ?>>
                                <label for="logo-color" class="imagina-tab">
                                    <span class="tab-icon">🎨</span>
                                    <span class="tab-text">Color</span>
                                </label>

                                <input type="radio" name="il_logo_bg_type" value="gradient" id="logo-gradient" <?php checked($logo_bg_type, 'gradient'); ?>>
                                <label for="logo-gradient" class="imagina-tab">
                                    <span class="tab-icon">🌈</span>
                                    <span class="tab-text">Degradado</span>
                                </label>

                                <input type="radio" name="il_logo_bg_type" value="image" id="logo-image" <?php checked($logo_bg_type, 'image'); ?>>
                                <label for="logo-image" class="imagina-tab">
                                    <span class="tab-icon">🖼️</span>
                                    <span class="tab-text">Imagen</span>
                                </label>
                            </div>
                        </div>

                        <div class="imagina-options-container">
                            <?php il_render_modern_logo_options(); ?>
                        </div>
                    </div>
                </div>

                <!-- *** NUEVA SECCIÓN: COLORES DEL FORMULARIO *** -->
                <div class="imagina-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🎨</span> Colores del Formulario</h2>
                        <p>Personaliza los colores de labels, botones y enlaces</p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-toggle-section">
                            <label class="imagina-toggle-container">
                                <input type="checkbox" name="il_use_custom_colors" value="1" <?php checked($use_custom_colors); ?> onchange="toggleColorOptions(this)">
                                <span class="imagina-toggle-slider"></span>
                                <span class="imagina-toggle-label">Usar colores personalizados</span>
                            </label>
                            <p class="imagina-toggle-description">
                                Cuando está desactivado, heredará los colores del tema. Cuando está activado, podrás personalizar cada color individualmente.
                            </p>
                        </div>

                        <div class="imagina-color-controls" id="custom-color-controls" style="<?php echo $use_custom_colors ? '' : 'display: none;'; ?>">
                            <?php il_render_color_controls(); ?>
                        </div>
                    </div>
                </div>

                <!-- *** NUEVA SECCIÓN: TRANSICIONES Y ANIMACIONES *** -->
                <div class="imagina-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">✨</span> Transiciones y Animaciones</h2>
                        <p>Configura cómo aparecen las imágenes, videos, logo y formulario</p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-toggle-section">
                            <label class="imagina-toggle-container">
                                <input type="checkbox" name="il_enable_transitions" value="1" <?php checked($enable_transitions); ?> onchange="toggleTransitionOptions(this)">
                                <span class="imagina-toggle-slider"></span>
                                <span class="imagina-toggle-label">Activar transiciones suaves</span>
                            </label>
                            <p class="imagina-toggle-description">
                                Las transiciones hacen que todos los elementos aparezcan suavemente en secuencia: fondo → logo → formulario.
                            </p>
                        </div>

                        <div class="imagina-transition-controls" id="transition-controls" style="<?php echo $enable_transitions ? '' : 'display: none;'; ?>">
                            <?php il_render_transition_controls(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="imagina-actions">
                <button type="submit" class="imagina-save-btn">
                    <span class="dashicons dashicons-yes-alt"></span>
                    Guardar Cambios
                </button>
                <button type="button" class="imagina-reset-btn" onclick="confirmReset()">
                    <span class="dashicons dashicons-image-rotate"></span>
                    Restaurar por Defecto
                </button>
            </div>
        </form>

        <!-- Tips y ayuda -->
        <div class="imagina-help-section">
            <h3><span class="dashicons dashicons-lightbulb"></span> Consejos útiles</h3>
            <div class="imagina-tips">
                <div class="imagina-tip">
                    <strong>🎭 Templates:</strong> Cada diseño se adapta automáticamente a móviles. Prueba varios antes de decidir.
                </div>
                <div class="imagina-tip">
                    <strong>🎨 Colores:</strong> Usa colores que combinen con tu marca para mantener consistencia visual.
                </div>
                <div class="imagina-tip">
                    <strong>🖼️ Imágenes:</strong> Para mejores resultados, usa imágenes de al menos 1920x1080px.
                </div>
                <div class="imagina-tip">
                    <strong>🎬 Videos:</strong> Los videos se reproducen automáticamente sin sonido. Formato recomendado: MP4.
                </div>
                <div class="imagina-tip">
                    <strong>📱 Móviles:</strong> Todos los fondos y templates se adaptan automáticamente a dispositivos móviles.
                </div>
                <div class="imagina-tip">
                    <strong>✨ Transiciones:</strong> Las animaciones crean una secuencia: fondo (0.1s) → logo (0.3s) → formulario (0.5s).
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Estilos del panel compacto */
    .imagina-login-admin {
        max-width: none;
        margin: 0;
        padding: 0;
        background: #f0f2f5;
    }

    .imagina-login-admin .wrap { margin: 0; padding: 0; }

    .imagina-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 30px;
        margin: 0 0 20px 0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    }

    .imagina-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .imagina-logo-section h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
    }

    .imagina-icon { font-size: 26px; }

    .imagina-subtitle {
        margin: 2px 0 0 36px;
        opacity: 0.9;
        font-size: 13px;
    }

    .imagina-preview-btn {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 8px 18px;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .imagina-preview-btn:hover {
        background: rgba(255,255,255,0.3);
        color: white;
        text-decoration: none;
    }

    .imagina-form {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 30px;
    }

    .imagina-sections {
        display: grid;
        gap: 16px;
    }

    .imagina-section {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .imagina-section-header {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
        padding: 14px 20px;
        border-bottom: 1px solid #e3e8ee;
    }

    .imagina-section-header h2 {
        margin: 0 0 2px 0;
        font-size: 17px;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-icon { font-size: 20px; }

    .imagina-section-header p {
        margin: 0;
        color: #64748b;
        font-size: 12px;
    }

    .imagina-section-content { padding: 16px 20px; }

    .imagina-main-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }

    .imagina-type-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    .imagina-type-tabs input[type="radio"] { display: none; }

    .imagina-tab {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 13px;
        color: #6b7280;
    }

    .imagina-tab:hover {
        border-color: #d1d5db;
        background: #f3f4f6;
    }

    .imagina-type-tabs input[type="radio"]:checked + .imagina-tab {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea15, #764ba215);
        color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }

    .tab-icon { font-size: 16px; }
    .tab-text { font-size: 13px; font-weight: 600; }

    .imagina-options-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        border: 1px dashed #dee2e6;
    }

    .imagina-option-panel { display: none; }
    .imagina-option-panel.active { display: block; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Toggles */
    .imagina-toggle-section {
        margin-bottom: 14px;
        padding: 12px 16px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .imagina-toggle-container {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .imagina-toggle-container input[type="checkbox"] { display: none; }

    .imagina-toggle-slider {
        position: relative;
        width: 42px;
        height: 22px;
        background: #ccc;
        border-radius: 22px;
        transition: 0.3s;
        cursor: pointer;
        flex-shrink: 0;
    }

    .imagina-toggle-slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .imagina-toggle-container input:checked + .imagina-toggle-slider { background: #667eea; }
    .imagina-toggle-container input:checked + .imagina-toggle-slider:before { transform: translateX(20px); }

    .imagina-toggle-description {
        margin: 6px 0 0 0;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.4;
    }

    /* Controles de color */
    .imagina-color-controls { animation: fadeInUp 0.2s ease; }

    .imagina-color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .imagina-color-picker {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 10px;
        align-items: center;
        background: white;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .imagina-color-input {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }

    .imagina-color-input:hover { transform: scale(1.1); }

    .imagina-color-info { display: flex; flex-direction: column; gap: 2px; }

    .imagina-color-label { font-weight: 600; color: #374151; font-size: 13px; }

    .imagina-color-value {
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 3px;
    }

    /* Controles de transicion */
    .imagina-transition-controls { animation: fadeInUp 0.2s ease; }

    .imagina-transition-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .imagina-control-group {
        background: white;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .imagina-control-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .imagina-select {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: white;
        color: #374151;
        font-size: 13px;
    }

    .imagina-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }

    .imagina-slider-container { display: flex; align-items: center; gap: 8px; }

    .imagina-slider {
        flex: 1;
        height: 5px;
        border-radius: 3px;
        background: #e5e7eb;
        outline: none;
    }

    .imagina-slider::-webkit-slider-thumb {
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #667eea;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .imagina-slider::-moz-range-thumb {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #667eea;
        cursor: pointer;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .imagina-slider-value {
        font-weight: 600;
        color: #374151;
        min-width: 32px;
        text-align: center;
        font-size: 13px;
    }

    .imagina-actions {
        display: flex;
        justify-content: center;
        gap: 14px;
        padding: 24px 0;
        margin-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .imagina-save-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .imagina-save-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .imagina-reset-btn {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .imagina-reset-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .imagina-help-section {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 30px;
    }

    .imagina-help-section h3 {
        color: #374151;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .imagina-tips {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }

    .imagina-tip {
        background: white;
        padding: 10px 14px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        font-size: 12px;
        line-height: 1.4;
    }

    /* Selector de templates */
    .imagina-template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }

    .imagina-template-card {
        position: relative;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 8px;
        overflow: hidden;
    }

    .imagina-template-card:hover {
        border-color: #d1d5db;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .imagina-template-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.25);
    }

    .imagina-template-card input[type="radio"] { display: none; }

    .template-preview {
        background: #f3f4f6;
        border-radius: 8px;
        padding: 12px;
        min-height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .template-mockup {
        width: 100%;
        max-width: 160px;
        height: 80px;
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: grid;
        overflow: hidden;
    }

    /* Mockups */
    .classic-mockup { grid-template-columns: 1fr 1fr; }
    .mockup-left { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .mockup-right {
        padding: 10px 6px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
    }

    .minimal-mockup { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
    .mockup-logo-mini {
        background: #f3f4f6;
        height: 25px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mockup-form-centered {
        padding: 8px 6px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
    }

    .toplogo-mockup { grid-template-columns: 1fr; grid-template-rows: 30px 1fr; }
    .mockup-logo-top { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

    .split-mockup { grid-template-columns: 1.5fr 1fr; }
    .mockup-left-big { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .mockup-right-small {
        padding: 8px 5px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        justify-content: center;
    }

    .mock-input { background: #e5e7eb; height: 8px; border-radius: 3px; }
    .mock-button { background: #667eea; height: 10px; border-radius: 3px; margin-top: 2px; }

    .fullscreen-mockup {
        grid-template-columns: 1fr;
        grid-template-rows: 22px 1fr 14px;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }
    .mockup-full-header { background: rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.1); }
    .mockup-full-content {
        padding: 8px 6px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
    }
    .fullscreen-mockup .mock-input { background: rgba(255,255,255,0.9); }
    .fullscreen-mockup .mock-button { background: rgba(255,255,255,0.95); }
    .mockup-full-footer { background: rgba(0,0,0,0.05); border-top: 1px solid rgba(255,255,255,0.1); }

    .glass-preview { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); }
    .glass-mockup {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    .mockup-glass-header {
        background: rgba(255,255,255,0.05);
        height: 22px;
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }
    .mockup-glass-content {
        padding: 8px 6px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
    }
    .glass-mockup .mock-input { background: rgba(255,255,255,0.6); }
    .glass-mockup .mock-button { background: rgba(255,255,255,0.8); }

    .sidebar-mockup { grid-template-columns: 2fr 1fr; position: relative; }
    .mockup-sidebar-bg { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); }
    .mockup-sidebar-panel {
        background: white;
        padding: 8px 5px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
        box-shadow: -2px 0 6px rgba(0,0,0,0.08);
    }

    .boxed-mockup {
        grid-template-columns: 1fr;
        background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mockup-boxed-container {
        width: 75%;
        background: white;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        overflow: hidden;
        display: grid;
        grid-template-rows: 20px 1fr;
    }
    .mockup-boxed-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .mockup-boxed-content {
        padding: 6px 5px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        justify-content: center;
    }

    .template-info h3 {
        margin: 0 0 2px 0;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
    }

    .template-info p {
        margin: 0;
        font-size: 11px;
        color: #6b7280;
        line-height: 1.3;
    }

    .template-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 24px;
        height: 24px;
        background: #667eea;
        color: white;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    }

    .imagina-template-card.active .template-check {
        display: flex;
        animation: checkPop 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes checkPop {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .imagina-template-section {
        border: 2px solid #667eea;
        box-shadow: 0 2px 12px rgba(102, 126, 234, 0.12);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .imagina-header { padding: 12px 16px; }
        .imagina-header-content { flex-direction: column; text-align: center; gap: 12px; }
        .imagina-logo-section h1 { font-size: 20px; }
        .imagina-subtitle { margin-left: 0; }
        .imagina-form, .imagina-help-section { padding: 0 12px; }
        .imagina-section-content { padding: 12px; }
        .imagina-type-tabs { flex-wrap: wrap; }
        .imagina-color-grid, .imagina-transition-grid { grid-template-columns: 1fr; }
        .imagina-template-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
        .imagina-actions { flex-direction: column; align-items: center; }
        .imagina-tips { grid-template-columns: 1fr; }
    }
    </style>

    <script>
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
        
        // Sliders de transición
        const sliders = document.querySelectorAll('.imagina-slider');
        sliders.forEach(slider => {
            const valueDisplay = slider.parentNode.querySelector('.imagina-slider-value');
            if (valueDisplay) {
                slider.addEventListener('input', function() {
                    valueDisplay.textContent = this.value + 's';
                });
            }
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
            alert('Función de reset en desarrollo');
        }
    }
    </script>
    <?php
}

/**
 * Renderizar opciones modernas para el fondo del body
 */
function il_render_modern_body_options() {
    // Color sólido
    $body_color = get_option('il_body_bg_color', '#009bde');
    echo '<div class="imagina-option-panel" data-type="color" data-target="body">
        <div class="imagina-color-picker">
            <input type="color" name="il_body_bg_color" value="' . esc_attr($body_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color de fondo de la página</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($body_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($body_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            Elige un color que represente tu marca y combine con el resto de tu sitio web.
        </p>
    </div>';

    // Degradado
    $gradient_type = get_option('il_body_gradient_type', 'linear');
    $gradient_direction = get_option('il_body_gradient_direction', 'vertical');
    $gradient_color1 = get_option('il_body_gradient_color1', '#009bde');
    $gradient_color2 = get_option('il_body_gradient_color2', '#0056b3');
    
    echo '<div class="imagina-option-panel" data-type="gradient" data-target="body">
        <div class="imagina-gradient-controls">
            <div class="imagina-control-group">
                <label class="imagina-control-label">Tipo de degradado</label>
                <select name="il_body_gradient_type" class="imagina-select">
                    <option value="linear"' . selected($gradient_type, 'linear', false) . '>🔄 Lineal</option>
                    <option value="radial"' . selected($gradient_type, 'radial', false) . '>⭕ Radial</option>
                </select>
            </div>
            
            <div class="imagina-control-group">
                <label class="imagina-control-label">Dirección</label>
                <select name="il_body_gradient_direction" class="imagina-select">
                    <option value="vertical"' . selected($gradient_direction, 'vertical', false) . '>⬇️ Vertical (arriba → abajo)</option>
                    <option value="horizontal"' . selected($gradient_direction, 'horizontal', false) . '>➡️ Horizontal (izq → der)</option>
                    <option value="diagonal1"' . selected($gradient_direction, 'diagonal1', false) . '>↗️ Diagonal ↗</option>
                    <option value="diagonal2"' . selected($gradient_direction, 'diagonal2', false) . '>↙️ Diagonal ↙</option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="imagina-color-picker">
                <input type="color" name="il_body_gradient_color1" value="' . esc_attr($gradient_color1) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">Color inicial</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($gradient_color1)) . '</span>
                </div>
            </div>
            
            <div class="imagina-color-picker">
                <input type="color" name="il_body_gradient_color2" value="' . esc_attr($gradient_color2) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">Color final</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($gradient_color2)) . '</span>
                </div>
            </div>
        </div>
        
        <div class="imagina-gradient-preview" style="margin-top: 20px; height: 60px; border-radius: 8px; border: 2px solid #e5e7eb; background: ' . il_generate_gradient_css($gradient_type, $gradient_direction, $gradient_color1, $gradient_color2) . ';"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            Los degradados crean fondos más dinámicos y modernos. Ajusta la dirección según tu diseño.
        </p>
    </div>';

    // Imagen
    $body_image_id = get_option('il_body_background_image');
    echo '<div class="imagina-option-panel" data-type="image" data-target="body">
        <div class="imagina-media-uploader">
            <span class="imagina-upload-icon">🖼️</span>
            <input type="hidden" name="il_body_background_image" value="' . esc_attr($body_image_id) . '">
            <button type="button" class="imagina-upload-btn upload-image-button">
                <span class="dashicons dashicons-upload"></span>
                ' . ($body_image_id ? 'Cambiar Imagen' : 'Subir Imagen') . '
            </button>';
            
    if ($body_image_id) {
        echo '<button type="button" class="imagina-upload-btn remove-image-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            Quitar
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                Formatos: JPG, PNG, GIF, WebP | Tamaño recomendado: 1920×1080px
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($body_image_id) {
        echo wp_get_attachment_image($body_image_id, 'medium', false, ['style' => 'width: 100%; height: auto; border-radius: 8px;']);
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-format-image" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>No hay imagen seleccionada</p>
        </div>';
    }
    
    echo '</div>
        </div>
    </div>';

    // Video
    $body_video_id = get_option('il_body_background_video');
    $video_overlay = get_option('il_video_overlay_color', 'rgba(0,0,0,0.3)');
    
    echo '<div class="imagina-option-panel" data-type="video" data-target="body">
        <div class="imagina-media-uploader">
            <span class="imagina-upload-icon">🎬</span>
            <input type="hidden" name="il_body_background_video" value="' . esc_attr($body_video_id) . '">
            <button type="button" class="imagina-upload-btn upload-video-button">
                <span class="dashicons dashicons-video-alt3"></span>
                ' . ($body_video_id ? 'Cambiar Video' : 'Subir Video') . '
            </button>';
            
    if ($body_video_id) {
        echo '<button type="button" class="imagina-upload-btn remove-video-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            Quitar
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                Formatos: MP4, WebM, OGG | Tamaño máximo: 50MB
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($body_video_id) {
        $video_url = wp_get_attachment_url($body_video_id);
        echo '<video controls style="width: 100%; height: auto; border-radius: 8px;">
            <source src="' . esc_url($video_url) . '" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>';
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-video-alt3" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>No hay video seleccionado</p>
        </div>';
    }
    
    echo '</div>
        </div>
        
        <div class="imagina-control-group" style="margin-top: 20px;">
            <label class="imagina-control-label">Overlay del video (para mejorar legibilidad)</label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="text" name="il_video_overlay_color" value="' . esc_attr($video_overlay) . '" 
                       placeholder="rgba(0,0,0,0.3)" 
                       style="flex: 1; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                <div style="width: 40px; height: 40px; background: ' . esc_attr($video_overlay) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
            </div>
            <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 12px;">
                Usa formato rgba() para controlar transparencia. Ejemplo: rgba(0,0,0,0.5) = negro 50% transparente
            </p>
        </div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            Los videos se reproducen automáticamente sin sonido. Recomendamos videos cortos (10-30 segundos) en loop.
        </p>
    </div>';
}

/**
 * Renderizar opciones modernas para el fondo del logo
 */
function il_render_modern_logo_options() {
    // Color sólido
    $logo_color = get_option('il_logo_bg_color', '#f9f9f9');
    echo '<div class="imagina-option-panel" data-type="color" data-target="logo">
        <div class="imagina-color-picker">
            <input type="color" name="il_logo_bg_color" value="' . esc_attr($logo_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color de fondo del logo</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($logo_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($logo_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            Elige un color que contraste bien con tu logo para que sea fácil de ver.
        </p>
    </div>';

    // Degradado - CORREGIDO
    $logo_gradient_type = get_option('il_logo_gradient_type', 'linear');
    $logo_gradient_direction = get_option('il_logo_gradient_direction', 'vertical');
    $logo_gradient_color1 = get_option('il_logo_gradient_color1', '#f9f9f9');
    $logo_gradient_color2 = get_option('il_logo_gradient_color2', '#e9ecef');
    
    echo '<div class="imagina-option-panel" data-type="gradient" data-target="logo">
        <div class="imagina-gradient-controls">
            <div class="imagina-control-group">
                <label class="imagina-control-label">Tipo de degradado</label>
                <select name="il_logo_gradient_type" class="imagina-select">
                    <option value="linear"' . selected($logo_gradient_type, 'linear', false) . '>🔄 Lineal</option>
                    <option value="radial"' . selected($logo_gradient_type, 'radial', false) . '>⭕ Radial</option>
                </select>
            </div>
            
            <div class="imagina-control-group">
                <label class="imagina-control-label">Dirección</label>
                <select name="il_logo_gradient_direction" class="imagina-select">
                    <option value="vertical"' . selected($logo_gradient_direction, 'vertical', false) . '>⬇️ Vertical</option>
                    <option value="horizontal"' . selected($logo_gradient_direction, 'horizontal', false) . '>➡️ Horizontal</option>
                    <option value="diagonal1"' . selected($logo_gradient_direction, 'diagonal1', false) . '>↗️ Diagonal ↗</option>
                    <option value="diagonal2"' . selected($logo_gradient_direction, 'diagonal2', false) . '>↙️ Diagonal ↙</option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="imagina-color-picker">
                <input type="color" name="il_logo_gradient_color1" value="' . esc_attr($logo_gradient_color1) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">Color inicial</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($logo_gradient_color1)) . '</span>
                </div>
            </div>
            
            <div class="imagina-color-picker">
                <input type="color" name="il_logo_gradient_color2" value="' . esc_attr($logo_gradient_color2) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">Color final</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($logo_gradient_color2)) . '</span>
                </div>
            </div>
        </div>
        
        <div class="imagina-gradient-preview" style="margin-top: 20px; height: 60px; border-radius: 8px; border: 2px solid #e5e7eb; background: ' . il_generate_gradient_css($logo_gradient_type, $logo_gradient_direction, $logo_gradient_color1, $logo_gradient_color2) . ';"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            Los degradados del logo crean efectos visuales únicos que complementan tu diseño.
        </p>
    </div>';

    // Imagen
    $logo_image_id = get_option('il_logo_background_image');
    $logo_overlay = get_option('il_logo_overlay_color', 'transparent');
    
    echo '<div class="imagina-option-panel" data-type="image" data-target="logo">
        <div class="imagina-media-uploader">
            <span class="imagina-upload-icon">🖼️</span>
            <input type="hidden" name="il_logo_background_image" value="' . esc_attr($logo_image_id) . '">
            <button type="button" class="imagina-upload-btn upload-image-button">
                <span class="dashicons dashicons-upload"></span>
                ' . ($logo_image_id ? 'Cambiar Imagen' : 'Subir Imagen') . '
            </button>';
            
    if ($logo_image_id) {
        echo '<button type="button" class="imagina-upload-btn remove-image-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            Quitar
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                Para el área del logo, recomendamos imágenes cuadradas (ej: 500×500px)
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($logo_image_id) {
        echo wp_get_attachment_image($logo_image_id, 'medium', false, ['style' => 'width: 100%; height: auto; border-radius: 8px;']);
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-format-image" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>No hay imagen seleccionada</p>
        </div>';
    }
    
    echo '</div>
        </div>
        
        <div class="imagina-control-group" style="margin-top: 20px;">
            <label class="imagina-control-label">
                <span class="dashicons dashicons-visibility"></span>
                Overlay para mejorar visibilidad del logo
            </label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="text" name="il_logo_overlay_color" value="' . esc_attr($logo_overlay) . '" 
                       placeholder="rgba(0,0,0,0.2)" 
                       style="flex: 1; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                <div style="width: 40px; height: 40px; background: ' . esc_attr($logo_overlay !== 'transparent' ? $logo_overlay : '#f0f0f0') . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
            </div>
            <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 12px;">
                Agrega una capa semitransparente sobre la imagen para que tu logo sea más visible. 
                Usa "transparent" para quitar el overlay.
            </p>
        </div>
    </div>';
}

/**
 * Renderizar controles de colores personalizados
 */
function il_render_color_controls() {
    $label_color = get_option('il_label_color', '#009bde');
    $button_color = get_option('il_button_color', '#009bde');
    $button_hover_color = get_option('il_button_hover_color', '#007ab8');
    $link_color = get_option('il_link_color', '#009bde');
    
    echo '<div class="imagina-color-grid">
        <div class="imagina-color-picker">
            <input type="color" name="il_label_color" value="' . esc_attr($label_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color de etiquetas</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($label_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($label_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_button_color" value="' . esc_attr($button_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color del botón</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($button_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($button_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_button_hover_color" value="' . esc_attr($button_hover_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color botón (hover)</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($button_hover_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($button_hover_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_link_color" value="' . esc_attr($link_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">Color de enlaces</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($link_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($link_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #e8f4fd; border-radius: 8px; border-left: 4px solid #0ea5e9;">
        <h4 style="margin: 0 0 10px 0; color: #0c4a6e; font-size: 16px;">
            <span class="dashicons dashicons-info"></span>
            Vista previa de colores
        </h4>
        <div style="display: grid; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: ' . esc_attr($label_color) . '; font-weight: 600;">Etiqueta de ejemplo</span>
                <span style="font-size: 12px; color: #64748b;">← Así se verán las etiquetas</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" style="background: ' . esc_attr($button_color) . '; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600;">Botón de ejemplo</button>
                <span style="font-size: 12px; color: #64748b;">← Color normal del botón</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" style="background: ' . esc_attr($button_hover_color) . '; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600;">Botón hover</button>
                <span style="font-size: 12px; color: #64748b;">← Color al pasar el mouse</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="#" style="color: ' . esc_attr($link_color) . '; text-decoration: none; font-weight: 500;">Enlace de ejemplo</a>
                <span style="font-size: 12px; color: #64748b;">← Color de los enlaces</span>
            </div>
        </div>
    </div>';
}

/**
 * Renderizar controles de transiciones
 */
function il_render_transition_controls() {
    $transition_type = get_option('il_transition_type', 'fade');
    $transition_duration = get_option('il_transition_duration', '0.5');
    $logo_duration = get_option('il_logo_transition_duration', '0.4');
    $logo_delay = get_option('il_logo_transition_delay', '0.05');
    $form_duration = get_option('il_form_transition_duration', '0.4');
    $form_delay = get_option('il_form_transition_delay', '0.15');

    echo '<div class="imagina-transition-grid">
        <div class="imagina-control-group">
            <label class="imagina-control-label">Tipo de transición</label>
            <select name="il_transition_type" class="imagina-select" onchange="updateTransitionPreview()">
                <option value="fade"' . selected($transition_type, 'fade', false) . '>Aparición gradual (Fade)</option>
                <option value="slidedown"' . selected($transition_type, 'slidedown', false) . '>Deslizar desde arriba</option>
                <option value="zoom"' . selected($transition_type, 'zoom', false) . '>Zoom suave</option>
            </select>
        </div>
    </div>

    <div style="margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div style="padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 10px 0; color: #374151; font-size: 13px; font-weight: 700;">Logo</h4>
            <div class="imagina-control-group" style="margin-bottom: 10px;">
                <label class="imagina-control-label">Velocidad</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_logo_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($logo_duration) . '" oninput="updateDurationDisplay(this)">
                    <span class="imagina-slider-value">' . esc_html($logo_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">Delay</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_logo_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($logo_delay) . '" oninput="updateDurationDisplay(this)">
                    <span class="imagina-slider-value">' . esc_html($logo_delay) . 's</span>
                </div>
            </div>
        </div>

        <div style="padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 10px 0; color: #374151; font-size: 13px; font-weight: 700;">Formulario</h4>
            <div class="imagina-control-group" style="margin-bottom: 10px;">
                <label class="imagina-control-label">Velocidad</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($form_duration) . '" oninput="updateDurationDisplay(this)">
                    <span class="imagina-slider-value">' . esc_html($form_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">Delay</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($form_delay) . '" oninput="updateDurationDisplay(this)">
                    <span class="imagina-slider-value">' . esc_html($form_delay) . 's</span>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 14px; padding: 12px; background: #f3f4f6; border-radius: 8px; border: 1px dashed #d1d5db;">
        <h4 style="margin: 0 0 8px 0; color: #374151; font-size: 13px;">Secuencia de animación</h4>
        <div style="display: flex; gap: 8px; font-size: 12px; color: #6b7280; flex-wrap: wrap;">
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #667eea;">Fondo (0.1s)</span>
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #10b981;">Logo (0.3s)</span>
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #f59e0b;">Formulario (0.5s)</span>
        </div>

        <div id="transition-preview" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 10px;">
            <div class="transition-demo" data-type="fade">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: fadeDemo 3s infinite;">Fade</div>
            </div>
            <div class="transition-demo" data-type="slidedown">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #10b981, #059669); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: slideDemo 3s infinite;">Slide</div>
            </div>
            <div class="transition-demo" data-type="zoom">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #f59e0b, #d97706); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: zoomDemo 3s infinite;">Zoom</div>
            </div>
        </div>

        <style>
        @keyframes fadeDemo { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        @keyframes slideDemo { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes zoomDemo { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.85); } }
        .transition-demo.active { border: 2px solid #667eea; padding: 3px; border-radius: 8px; }
        </style>
    </div>
    
    <script>
    function updateDurationDisplay(slider) {
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
    </script>';
}

/**
 * Enqueue scripts para el admin
 */
function il_enqueue_admin_scripts($hook_suffix) {
    if ($hook_suffix != 'settings_page_imagina-login-settings') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_script(
        'imagina-login-admin-script',
        plugin_dir_url(__FILE__) . 'js/media-uploader.js',
        ['jquery'],
        '2.2.2',
        true
    );
}
add_action('admin_enqueue_scripts', 'il_enqueue_admin_scripts');