<?php
/**
 * Plugin Name: Imagina Login
 * Plugin URI:  https://imaginawp.com
 * Description: Customized wp login with 9 professional templates and advanced background options
 * Version:     2.4.0
 * Author:      IMAGINA WP
 * Author URI:  https://imaginawp.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: imagina-login
 */

if(!defined('ABSPATH')) {
    die;
}

define('IL_VERSION', '2.4.0');

/**
 * Función para ajustar el brillo de un color hexadecimal.
 */
if (!function_exists('il_adjust_brightness')) {
    function il_adjust_brightness($hex, $steps) {
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
 * Esquema de opciones: valor por defecto y tipo de cada ajuste.
 * El tipo define cómo se limpia el valor al guardarlo y al leerlo.
 */
function il_option_schema() {
    $templates = 'classic|sidebar-left|sidebar-half|sidebar-half-left|split|fullscreen|glass|sidebar|boxed';
    $directions = 'vertical|horizontal|diagonal1|diagonal2';
    return [
        'il_login_template'           => ['classic', 'choice:' . $templates],
        'il_body_bg_type'             => ['color', 'choice:color|gradient|image|video|slideshow'],
        'il_body_slides'              => ['', 'ids'],
        'il_slide_interval'           => ['6', 'int:3:30'],
        'il_body_bg_color'            => ['#009bde', 'hex'],
        'il_body_background_image'    => ['', 'id'],
        'il_body_background_video'    => ['', 'id'],
        'il_body_video_url'           => ['', 'url'],
        'il_body_gradient_type'       => ['linear', 'choice:linear|radial'],
        'il_body_gradient_direction'  => ['vertical', 'choice:' . $directions],
        'il_body_gradient_color1'     => ['#009bde', 'hex'],
        'il_body_gradient_color2'     => ['#0056b3', 'hex'],
        'il_video_overlay_color'      => ['rgba(0,0,0,0.3)', 'css_color'],
        'il_logo_bg_type'             => ['color', 'choice:color|gradient|image'],
        'il_logo_bg_color'            => ['#f9f9f9', 'hex'],
        'il_logo_background_image'    => ['', 'id'],
        'il_logo_gradient_type'       => ['linear', 'choice:linear|radial'],
        'il_logo_gradient_direction'  => ['vertical', 'choice:' . $directions],
        'il_logo_gradient_color1'     => ['#f9f9f9', 'hex'],
        'il_logo_gradient_color2'     => ['#e9ecef', 'hex'],
        'il_logo_overlay_color'       => ['transparent', 'css_color'],
        'il_logo_max_size'            => ['200', 'int:40:400'],
        'il_logo_height'              => ['80', 'int:30:300'],
        'il_custom_logo'              => ['', 'id'],
        'il_logo_area_height'         => ['0', 'int:0:80'],
        'il_logo_area_border_color'   => ['', 'hex_or_empty'],
        'il_use_custom_colors'        => [false, 'bool'],
        'il_label_color'              => ['#009bde', 'hex'],
        'il_button_color'             => ['#009bde', 'hex'],
        'il_button_hover_color'       => ['#007ab8', 'hex'],
        'il_link_color'               => ['#009bde', 'hex'],
        'il_enable_transitions'       => [true, 'bool'],
        'il_transition_type'          => ['fade', 'choice:fade|slidedown|zoom'],
        'il_transition_duration'      => ['0.5', 'float:0.1:2'],
        'il_logo_transition_duration' => ['0.4', 'float:0.1:1.5'],
        'il_logo_transition_delay'    => ['0.05', 'float:0:1'],
        'il_form_transition_duration' => ['0.4', 'float:0.1:1.5'],
        'il_form_transition_delay'    => ['0.15', 'float:0:1'],

        // Textos del formulario (vacío = texto de WordPress)
        'il_text_username'            => ['', 'text'],
        'il_text_password'            => ['', 'text'],
        'il_text_button'              => ['', 'text'],
        'il_text_remember'            => ['', 'text'],
        'il_placeholder_username'     => ['', 'text'],
        'il_placeholder_password'     => ['', 'text'],
        'il_login_message'            => ['', 'html'],
        'il_generic_errors'           => [false, 'bool'],
        'il_error_message'            => ['', 'text'],

        // Elementos visibles y comportamiento
        'il_hide_remember'            => [false, 'bool'],
        'il_hide_lostpassword'        => [false, 'bool'],
        'il_hide_backtoblog'          => [false, 'bool'],
        'il_hide_privacy'             => [false, 'bool'],
        'il_show_language'            => [false, 'bool'],
        'il_logo_link_url'            => ['', 'url'],
        'il_disable_shake'            => [false, 'bool'],
        'il_disable_autofocus'        => [false, 'bool'],

        // Apariencia avanzada
        'il_dark_mode'                => ['off', 'choice:off|auto|always'],
        'il_font'                     => ['', 'choice:' . implode('|', array_keys(il_font_choices()))],
        'il_custom_css'               => ['', 'css'],
    ];
}

/**
 * Fuentes disponibles (Google Fonts). Se descargan y se sirven desde el propio sitio:
 * los visitantes no se conectan a Google (privacidad / RGPD).
 * Clave vacía = tipografía del tema o del sistema.
 */
function il_font_choices() {
    return [
        ''                 => ['Del tema / sistema', ''],
        'inter'            => ['Inter', 'Inter'],
        'poppins'          => ['Poppins', 'Poppins'],
        'montserrat'       => ['Montserrat', 'Montserrat'],
        'roboto'           => ['Roboto', 'Roboto'],
        'open-sans'        => ['Open Sans', 'Open Sans'],
        'lato'             => ['Lato', 'Lato'],
        'nunito'           => ['Nunito', 'Nunito'],
        'raleway'          => ['Raleway', 'Raleway'],
        'dm-sans'          => ['DM Sans', 'DM Sans'],
        'playfair-display' => ['Playfair Display', 'Playfair Display'],
    ];
}

/**
 * Limpiar un valor según su tipo. Si no es válido, devuelve el valor por defecto.
 * Evita que un valor manipulado se imprima dentro de <style> (inyección de CSS/HTML).
 */
function il_sanitize_value($type, $value, $default) {
    $parts = explode(':', $type);
    $value = is_string($value) ? trim(wp_unslash($value)) : $value;

    switch ($parts[0]) {
        case 'choice':
            return in_array($value, explode('|', $parts[1]), true) ? $value : $default;
        case 'hex':
            $hex = sanitize_hex_color($value);
            return $hex ? $hex : $default;
        case 'hex_or_empty':
            if ($value === '' || $value === null) return '';
            $hex = sanitize_hex_color($value);
            return $hex ? $hex : '';
        case 'css_color':
            // Hex, rgb(), rgba(), hsl(), hsla() o "transparent" — nada más
            if (is_string($value) && preg_match('/^(#[0-9a-f]{3,8}|(rgba?|hsla?)\(\s*[0-9.\s,%deg]+\)|transparent)$/i', $value)) {
                return $value;
            }
            return $default;
        case 'id':
            $id = absint($value);
            return $id ? $id : '';
        case 'ids':
            // Lista de IDs de adjuntos separados por coma ("12,15,20")
            $ids = array_filter(array_map('absint', explode(',', (string) $value)));
            return implode(',', array_slice(array_unique($ids), 0, 20));
        case 'text':
            return sanitize_text_field((string) $value);
        case 'html':
            return wp_kses_post((string) $value);
        case 'css':
            return il_sanitize_css((string) $value);
        case 'url':
            return $value ? esc_url_raw($value, ['http', 'https']) : '';
        case 'bool':
            return !empty($value) && $value !== 'false';
        case 'int':
            if (!is_numeric($value)) return $default;
            return (string) max((int) $parts[1], min((int) $parts[2], (int) $value));
        case 'float':
            if (!is_numeric($value)) return $default;
            return (string) max((float) $parts[1], min((float) $parts[2], round((float) $value, 2)));
    }
    return $default;
}

/**
 * Limpiar el CSS personalizado: solo CSS, nada que cierre la etiqueta <style>
 * ni que cargue o ejecute código (@import, javascript:, expression(), etc.).
 */
function il_sanitize_css($css) {
    $css = wp_strip_all_tags($css);
    $css = substr($css, 0, 20000);
    // Repetir hasta que no quede nada peligroso (evita trucos como "@imp@importort")
    $patterns = ['/@import/i', '/javascript\s*:/i', '/vbscript\s*:/i', '/expression\s*\(/i', '/behavior\s*:/i', '/-moz-binding/i', '/<\/?\s*style/i'];
    do {
        $before = $css;
        $css = preg_replace($patterns, '', $css);
    } while ($css !== $before);
    return trim($css);
}

/**
 * Obtener todas las opciones de Imagina Login, ya limpias (memoria estática por petición)
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

    $options = [];
    foreach (il_option_schema() as $key => list($default, $type)) {
        $value = get_option($key);
        // false = la opción nunca se guardó: usar el valor por defecto
        $options[$key] = ($value === false) ? $default : il_sanitize_value($type, $value, $default);
    }

    $cached_options = $options;
    return $options;
}

/**
 * Función para generar CSS de degradado - CORREGIDA
 */
function il_generate_gradient_css($type, $direction, $color1, $color2) {
    // Solo colores hex válidos: este texto se imprime dentro de <style> y de atributos style
    $color1 = sanitize_hex_color($color1);
    $color2 = sanitize_hex_color($color2);
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
    // La clave incluye un hash de los ajustes: cualquier cambio (desde el admin, WP-CLI o REST)
    // genera una clave nueva, así que la caché nunca queda desactualizada y no hace falta limpiarla.
    $cache_key = 'il_dyn_styles_' . substr(md5(serialize(il_get_all_options()) . IL_VERSION), 0, 16);
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
        'body_video_url' => $opts['il_body_video_url'],
        'template' => $opts['il_login_template']
    ];
}

/**
 * Inyectar estilos críticos en el head TEMPRANO
 * (styles.css ya se encola en il_enqueue_login_assets; precargarlo aquí lo descargaba dos veces)
 */
function il_inject_critical_styles() {
    $cached_styles = il_get_cached_dynamic_styles();

    echo '<style id="imagina-login-critical-css">' . $cached_styles['css'] . '</style>';

    // Sin JavaScript las animaciones nunca terminan y el logo y el formulario quedaban invisibles:
    // nadie podía iniciar sesión. Este respaldo los muestra siempre.
    echo '<noscript><style>body.login div#login h1, body.login div#login form { opacity: 1 !important; transform: none !important; }</style></noscript>';
}
add_action('login_head', 'il_inject_critical_styles', 1);

/**
 * Función para añadir los estilos y scripts a la página de login - OPTIMIZADA
 */
function il_enqueue_login_assets() {
    // Obtener todas las opciones de una vez (OPTIMIZACIÓN)
    $opts = il_get_all_options();
    $cached_styles = il_get_cached_dynamic_styles();

    // Dependencias de core que otros plugins (2FA, seguridad) y WordPress 7+ esperan en el login.
    // Se quita login.css (lo reemplaza el diseño del plugin) pero se mantienen los botones,
    // los iconos y el tooltip de "Recuérdame"; sin ellos esos elementos salían sin estilo.
    $core_deps = array('dashicons', 'buttons');
    if (wp_style_is('wp-tooltip', 'registered')) {
        $core_deps[] = 'wp-tooltip';
    }

    // Cargar CSS base
    wp_enqueue_style(
        'imagina-login',
        plugin_dir_url( __FILE__ ) . 'css/styles.css',
        $core_deps,
        IL_VERSION
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
        IL_VERSION
    );

    // Agregar clase del template al body y clase de transiciones
    add_filter('login_body_class', function($classes) use ($template, $opts) {
        $classes[] = 'template-' . $template;

        // Agregar clase si las transiciones están activas
        if ($opts['il_enable_transitions']) {
            $classes[] = 'has-transitions';
        }

        // Elementos ocultos desde los ajustes
        foreach (['remember', 'backtoblog', 'privacy'] as $part) {
            if (!empty($opts['il_hide_' . $part])) {
                $classes[] = 'il-hide-' . $part;
            }
        }

        // Modo oscuro: "auto" sigue la preferencia del sistema del visitante
        if ($opts['il_dark_mode'] === 'auto') {
            $classes[] = 'il-dark-auto';
        } elseif ($opts['il_dark_mode'] === 'always') {
            $classes[] = 'il-dark';
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
        $button_hover_color = il_adjust_brightness($primary_color, -30);
        $link_color = $primary_color;
    }

    // *** TAMAÑO DEL LOGO ***
    $logo_max_size = $opts['il_logo_max_size'];
    $logo_height = $opts['il_logo_height'];
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
        /* Tamaño del logo */
        body.login div#login h1 a {
            width: " . intval($logo_max_size) . "px !important;
            height: " . intval($logo_height) . "px !important;
            max-width: 100% !important;
        }

        /* Padding del área del logo solo si se configuró */
        " . (intval($logo_area_height) > 0 ? "
        body.login div#login h1 {
            padding-top: " . intval($logo_area_height) . "px !important;
            padding-bottom: " . intval($logo_area_height) . "px !important;
            min-height: auto !important;
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

    // Si no hay logo de imagen, mostrar el nombre del sitio como texto.
    // (Antes también se añadía con ::before y el nombre salía duplicado.)
    if (empty($logo_url)) {
        $custom_css .= "
            body.login div#login h1 a {
                background-image: none !important;
                text-indent: 0;
                height: auto !important;
                width: auto !important;
                max-width: 100% !important;
                color: var(--primary-color, #009bde);
                font-weight: bold;
                font-size: 24px;
                line-height: 1.25;
                text-align: center;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        ";
    }

    wp_add_inline_style('imagina-login', $custom_css);

    // Activar las animaciones de entrada.
    // El botón de mostrar/ocultar contraseña lo maneja WordPress (user-profile.js); antes el plugin
    // lo reemplazaba y además lo añadía a cualquier campo de contraseña, incluidos los de plugins
    // de 2FA, y forzaba a mostrarse campos que esos plugins tenían ocultos.
    // Placeholders opcionales (WordPress no ofrece un filtro para ellos)
    $placeholders = array_filter([
        'user_login' => $opts['il_placeholder_username'],
        'user_pass'  => $opts['il_placeholder_password'],
    ]);

    $script = "(function () {
        var placeholders = " . wp_json_encode($placeholders ? $placeholders : new stdClass()) . ";
        function start() {
            Object.keys(placeholders).forEach(function (id) {
                var field = document.getElementById(id);
                if (field) { field.setAttribute('placeholder', placeholders[id]); }
            });
            // El mensaje de bienvenida va al inicio del formulario (los diseños ubican los mensajes debajo)
            var welcome = document.querySelector('.il-welcome-message'), form = document.getElementById('loginform');
            if (welcome && form) { form.insertBefore(welcome, form.firstChild); }
            // El selector de idioma está fuera de #login y desplazaba la tarjeta: se integra al pie
            var lang = document.querySelector('.language-switcher'), box = document.getElementById('login');
            if (lang && box) { box.appendChild(lang); }
            " . ($enable_transitions
                ? "setTimeout(function () { document.body.classList.add('logo-loaded', 'form-loaded'); }, 50);"
                : "document.body.classList.add('logo-loaded', 'form-loaded');") . "
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
    })();";

    wp_register_script('imagina-login-toggle', '', array(), IL_VERSION, true);
    wp_enqueue_script('imagina-login-toggle');
    wp_add_inline_script('imagina-login-toggle', $script);
}
add_action('login_enqueue_scripts', 'il_enqueue_login_assets', 10);

/**
 * Función para quitar los estilos CSS por defecto de la página de login.
 */
function il_dequeue_default_login_styles() {
    wp_dequeue_style('l10n');
    wp_dequeue_style('forms');
    wp_dequeue_style('login');
}
add_action('login_enqueue_scripts', 'il_dequeue_default_login_styles', 11);

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

    // print_emoji_styles está obsoleta desde WP 6.4 y genera un aviso al llamar wp_print_styles() aquí
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Simular las clases del body de wp-login.php
    $opts = il_get_all_options();
    $template = $opts['il_login_template'];
    $body_classes = ['login', 'wp-core-ui', 'login-action-login'];
    $body_classes[] = 'template-' . $template;
    if ($opts['il_enable_transitions']) {
        $body_classes[] = 'has-transitions';
    }

    // Obtener el logo (misma lógica que il_enqueue_login_assets)
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
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(sprintf(_x('&larr; Go to %s', 'site'), get_bloginfo('name'))); ?></a>
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
// Selector de idioma: oculto salvo que se active en los ajustes
add_filter('login_display_language_dropdown', function () {
    $opts = il_get_all_options();
    return (bool) $opts['il_show_language'];
});

function il_login_logo_url() {
    $opts = il_get_all_options();
    return $opts['il_logo_link_url'] ? $opts['il_logo_link_url'] : home_url();
}
add_filter('login_headerurl', 'il_login_logo_url');

/**
 * Textos, elementos visibles y comportamiento del formulario de login.
 * Solo se activa en wp-login.php.
 */
function il_apply_login_customizations() {
    $opts = il_get_all_options();
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    $is_login_screen = in_array($action, ['login', ''], true);

    // Textos de etiquetas y botón. Solo se cambian los textos de WordPress (dominio "default"),
    // así no se tocan los de plugins de 2FA u otros que usan palabras iguales como "Password".
    $texts = array_filter([
        'Username or Email Address' => $opts['il_text_username'],
        'Password'                  => $opts['il_text_password'],
        'Log In'                    => $opts['il_text_button'],
        'Remember Me'               => $opts['il_text_remember'],
    ]);
    if ($texts && $is_login_screen) {
        add_filter('gettext', function ($translation, $text, $domain) use ($texts) {
            return ('default' === $domain && isset($texts[$text])) ? $texts[$text] : $translation;
        }, 20, 3);
    }

    // Mensaje de bienvenida sobre el formulario
    if ($opts['il_login_message'] && $is_login_screen) {
        add_filter('login_message', function ($message) use ($opts) {
            return $message . '<p class="message il-welcome-message">' . wp_kses_post($opts['il_login_message']) . '</p>';
        });
    }

    // Error genérico: no revela si el usuario existe (evita enumerar usuarios).
    // Solo reemplaza los errores de usuario/contraseña; los de 2FA u otros plugins se mantienen.
    if ($opts['il_generic_errors']) {
        add_filter('wp_login_errors', function ($errors) use ($opts) {
            $codes = ['invalid_username', 'incorrect_password', 'invalid_email'];
            if (!is_wp_error($errors) || !array_intersect($errors->get_error_codes(), $codes)) {
                return $errors;
            }
            $message = $opts['il_error_message'] ? $opts['il_error_message'] : __('Usuario o contraseña incorrectos.', 'imagina-login');
            $generic = new WP_Error();
            foreach ($errors->get_error_codes() as $code) {
                if (!in_array($code, $codes, true)) {
                    foreach ($errors->get_error_messages($code) as $m) {
                        $generic->add($code, $m);
                    }
                }
            }
            $generic->add('il_generic_error', '<strong>' . esc_html__('Error:', 'imagina-login') . '</strong> ' . esc_html($message));
            return $generic;
        });
    }

    // Ocultar "¿Olvidaste tu contraseña?" (y el separador que quedaría suelto)
    if ($opts['il_hide_lostpassword']) {
        add_filter('lost_password_html_link', '__return_empty_string');
        if ($is_login_screen) {
            add_filter('login_link_separator', '__return_empty_string');
        }
    }

    // Ocultar "← Ir a …"
    if ($opts['il_hide_backtoblog']) {
        add_filter('login_site_html_link', '__return_empty_string');
    }

    if ($opts['il_disable_shake']) {
        add_filter('shake_error_codes', '__return_empty_array');
    }
    if ($opts['il_disable_autofocus']) {
        add_filter('enable_login_autofocus', '__return_false');
    }
}
add_action('login_init', 'il_apply_login_customizations');

function il_login_logo_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'il_login_logo_title');

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

    // Video de fondo (lazy load): URL externa tiene prioridad sobre el video subido
    if ($cached_styles['body_bg_type'] !== 'video') {
        return;
    }

    $source = il_parse_video_url(isset($cached_styles['body_video_url']) ? $cached_styles['body_video_url'] : '');
    if (!$source && $cached_styles['body_bg_video']) {
        $uploaded_url = wp_get_attachment_url($cached_styles['body_bg_video']);
        if ($uploaded_url) {
            $source = ['type' => 'file', 'url' => $uploaded_url];
        }
    }
    if (!$source) {
        return;
    }

    if ($source['type'] === 'file') {
        $create_js = '
            var el = document.createElement("video");
            el.src = ' . wp_json_encode(esc_url_raw($source['url'])) . ';
            el.autoplay = true;
            el.loop = true;
            el.muted = true;
            el.playsInline = true;
            el.setAttribute("muted", "");
            el.setAttribute("playsinline", "");
            el.setAttribute("preload", "metadata");
            el.addEventListener("canplay", markLoaded);
            wrap.appendChild(el);';
    } else {
        $create_js = '
            var el = document.createElement("iframe");
            el.src = ' . wp_json_encode($source['embed']) . ';
            el.setAttribute("frameborder", "0");
            el.setAttribute("allow", "autoplay; encrypted-media; picture-in-picture");
            el.setAttribute("tabindex", "-1");
            el.setAttribute("aria-hidden", "true");
            el.setAttribute("title", "");
            // Retrasar la aparición para ocultar la interfaz de carga del reproductor
            el.addEventListener("load", function() { setTimeout(markLoaded, 1200); });
            wrap.appendChild(el);';
    }

    echo '<script>
        (function() {
            function loadVideo() {
                // Solo cargar video si no está en móvil (para performance)
                if (window.innerWidth <= 768) return;
                var wrap = document.createElement("div");
                wrap.id = "login-background-video";
                wrap.className = "il-video-' . esc_js($source['type']) . '";
                function markLoaded() { wrap.classList.add("loaded"); }
                ' . $create_js . '
                document.body.appendChild(wrap);
                // Respaldo por si el evento de carga nunca llega
                setTimeout(markLoaded, 4000);
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", loadVideo);
            } else {
                loadVideo();
            }
        })();
    </script>';
}

/**
 * Interpretar una URL de video: YouTube, Vimeo o archivo directo.
 * Devuelve null si la URL está vacía o no se reconoce.
 */
function il_parse_video_url($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $m)) {
        $id = $m[1];
        $params = http_build_query([
            'autoplay' => 1, 'mute' => 1, 'loop' => 1, 'playlist' => $id,
            'controls' => 0, 'rel' => 0, 'playsinline' => 1, 'modestbranding' => 1,
            'iv_load_policy' => 3, 'disablekb' => 1, 'fs' => 0,
        ]);
        return [
            'type' => 'youtube',
            'label' => 'YouTube',
            'embed' => 'https://www.youtube-nocookie.com/embed/' . $id . '?' . $params,
        ];
    }

    if (preg_match('~vimeo\.com/(?:[^?#]*/)?(\d+)(?:/([a-f0-9]+))?(?:[/?#]|$)~i', $url, $m)) {
        // Videos no listados necesitan su hash de privacidad (vimeo.com/ID/HASH o ?h=HASH)
        $hash = !empty($m[2]) ? $m[2] : '';
        if (!$hash) {
            parse_str((string) wp_parse_url($url, PHP_URL_QUERY), $query);
            $hash = isset($query['h']) ? preg_replace('~[^a-f0-9]~i', '', $query['h']) : '';
        }
        return [
            'type' => 'vimeo',
            'label' => 'Vimeo',
            'embed' => 'https://player.vimeo.com/video/' . $m[1] . '?' . ($hash ? 'h=' . $hash . '&' : '') . 'background=1&autoplay=1&loop=1&muted=1&dnt=1',
        ];
    }

    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    if (preg_match('~\.(mp4|webm|ogg|ogv|mov|m4v)$~i', $path)) {
        return ['type' => 'file', 'label' => 'Archivo de video', 'url' => $url];
    }

    return null;
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
 * Restaurar todos los ajustes del plugin a sus valores por defecto
 */
function il_reset_settings() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('No tienes permisos para hacer esto.', 'imagina-login'), 403);
    }
    check_admin_referer('il_reset_settings');

    foreach (array_keys(il_option_schema()) as $key) {
        delete_option($key);
    }

    wp_safe_redirect(add_query_arg('il_reset', '1', admin_url('options-general.php?page=imagina-login-settings')));
    exit;
}
add_action('admin_post_il_reset_settings', 'il_reset_settings');

/**
 * Avisos en la página de ajustes (restauración completada)
 */
function il_admin_notices() {
    if (isset($_GET['page'], $_GET['il_reset']) && $_GET['page'] === 'imagina-login-settings') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Ajustes restaurados a sus valores por defecto.', 'imagina-login') . '</p></div>';
    }
}
add_action('admin_notices', 'il_admin_notices');

/**
 * Registrar todos los ajustes
 */
function il_register_settings() {
    // Cada ajuste se limpia al guardarse según su tipo (ver il_option_schema)
    foreach (il_option_schema() as $key => list($default, $type)) {
        register_setting('imagina_login_options', $key, [
            'type' => 'string',
            'default' => $default,
            'sanitize_callback' => function ($value) use ($type, $default) {
                return il_sanitize_value($type, $value, $default);
            },
        ]);
    }

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
                                <label class="imagina-control-label">Ancho del logo</label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_max_size" class="imagina-slider" min="40" max="400" step="10" value="<?php echo esc_attr(get_option('il_logo_max_size', '200')); ?>" data-unit="px">
                                    <span class="imagina-slider-value"><?php echo esc_html(get_option('il_logo_max_size', '200')); ?>px</span>
                                </div>
                                <label class="imagina-control-label" style="margin-top: 14px;">Alto del logo</label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_height" class="imagina-slider" min="30" max="300" step="10" value="<?php echo esc_attr(get_option('il_logo_height', '80')); ?>" data-unit="px">
                                    <span class="imagina-slider-value"><?php echo esc_html(get_option('il_logo_height', '80')); ?>px</span>
                                </div>
                            </div>
                        </div>

                        <?php
                        $logo_area_height = get_option('il_logo_area_height', '0');
                        $logo_area_border_color = get_option('il_logo_area_border_color', '');
                        ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="imagina-control-group">
                                <label class="imagina-control-label">Espacio vertical del logo</label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_area_height" class="imagina-slider" min="0" max="80" step="5" value="<?php echo esc_attr($logo_area_height); ?>" data-unit="px" data-zero-label="Auto">
                                    <span class="imagina-slider-value"><?php echo intval($logo_area_height) === 0 ? 'Auto' : esc_html($logo_area_height) . 'px'; ?></span>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;">0 = usa el padding del template. Ajusta el espacio arriba y abajo del logo.</p>
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

                <!-- Sección: textos del formulario -->
                <div class="imagina-section" id="il-section-texts">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">✍️</span> <?php esc_html_e('Textos del formulario', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Cambia etiquetas, botón y mensajes. Déjalos vacíos para usar los textos de WordPress.', 'imagina-login'); ?></p>
                    </div>
                    <div class="imagina-section-content">
                        <div class="imagina-fields-grid">
                            <?php
                            il_field_text('il_text_username', __('Etiqueta del usuario', 'imagina-login'), __('Username or Email Address'));
                            il_field_text('il_text_password', __('Etiqueta de la contraseña', 'imagina-login'), __('Password'));
                            il_field_text('il_text_remember', __('Texto de "Recuérdame"', 'imagina-login'), __('Remember Me'));
                            il_field_text('il_text_button', __('Texto del botón', 'imagina-login'), __('Log In'));
                            il_field_text('il_placeholder_username', __('Texto de ejemplo en el campo usuario', 'imagina-login'), __('ej: nombre@empresa.com', 'imagina-login'));
                            il_field_text('il_placeholder_password', __('Texto de ejemplo en el campo contraseña', 'imagina-login'), '');
                            ?>
                        </div>
                        <?php
                        il_field_textarea('il_login_message', __('Mensaje de bienvenida', 'imagina-login'), __('ej: ¡Hola de nuevo! Ingresa con tu cuenta.', 'imagina-login'), __('Aparece arriba del formulario. Admite HTML básico (negrita, enlaces).', 'imagina-login'), 2);
                        ?>
                        <div class="imagina-fields-grid">
                            <?php
                            il_field_switch('il_generic_errors', __('Mensaje de error genérico', 'imagina-login'), __('Muestra el mismo error si el usuario no existe o la contraseña es incorrecta, para que nadie pueda adivinar qué usuarios existen. No afecta los mensajes de 2FA.', 'imagina-login'));
                            il_field_text('il_error_message', __('Texto del error genérico', 'imagina-login'), __('Usuario o contraseña incorrectos.', 'imagina-login'));
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Sección: elementos y comportamiento -->
                <div class="imagina-section" id="il-section-elements">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">👁️</span> <?php esc_html_e('Elementos y comportamiento', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Elige qué se muestra en la página de login y cómo se comporta.', 'imagina-login'); ?></p>
                    </div>
                    <div class="imagina-section-content">
                        <div class="imagina-fields-grid">
                            <?php
                            il_field_switch('il_hide_remember', __('Ocultar "Recuérdame"', 'imagina-login'));
                            il_field_switch('il_hide_lostpassword', __('Ocultar "¿Olvidaste tu contraseña?"', 'imagina-login'));
                            il_field_switch('il_hide_backtoblog', __('Ocultar "Ir al sitio"', 'imagina-login'));
                            il_field_switch('il_hide_privacy', __('Ocultar enlace de privacidad', 'imagina-login'));
                            il_field_switch('il_show_language', __('Mostrar selector de idioma', 'imagina-login'), __('Solo aparece si el sitio tiene más de un idioma instalado.', 'imagina-login'));
                            il_field_switch('il_disable_shake', __('Sin sacudida al fallar', 'imagina-login'), __('Desactiva la animación de "temblor" cuando la contraseña es incorrecta.', 'imagina-login'));
                            il_field_switch('il_disable_autofocus', __('Sin foco automático', 'imagina-login'), __('El cursor no salta solo al campo de usuario al cargar la página.', 'imagina-login'));
                            il_field_text('il_logo_link_url', __('Enlace del logo', 'imagina-login'), home_url('/'), __('A dónde lleva el clic en el logo. Vacío = página de inicio.', 'imagina-login'), 'url');
                            ?>
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
    
    $video_ext_url = get_option('il_body_video_url', '');
    $video_ext_info = il_parse_video_url($video_ext_url);
    if ($video_ext_url && $video_ext_info) {
        $video_ext_status = '<span style="color: #059669; font-weight: 600;">✓ Detectado: ' . esc_html($video_ext_info['label']) . '. Se usará en lugar del video subido.</span>';
    } elseif ($video_ext_url) {
        $video_ext_status = '<span style="color: #dc2626; font-weight: 600;">✗ Enlace no reconocido. Usa un link de YouTube, Vimeo o un archivo .mp4/.webm.</span>';
    } else {
        $video_ext_status = 'Pega un enlace de YouTube, Vimeo o un archivo directo (.mp4, .webm). Si lo llenas, tiene prioridad sobre el video subido.';
    }

    echo '</div>
        </div>

        <div class="imagina-control-group" style="margin-top: 16px;">
            <label class="imagina-control-label">O usa un video externo (enlace)</label>
            <input type="url" name="il_body_video_url" value="' . esc_attr($video_ext_url) . '"
                   placeholder="https://www.youtube.com/watch?v=..."
                   style="width: 100%; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 6px;">
            <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 12px;">' . $video_ext_status . '</p>
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
/**
 * Campos reutilizables del panel de ajustes
 */
function il_field_text($key, $label, $placeholder = '', $help = '', $type = 'text') {
    $opts = il_get_all_options();
    printf(
        '<div class="imagina-control-group"><label class="imagina-control-label" for="%1$s">%2$s</label><input type="%5$s" id="%1$s" name="%1$s" value="%3$s" placeholder="%4$s" class="imagina-text-input">%6$s</div>',
        esc_attr($key),
        esc_html($label),
        esc_attr($opts[$key]),
        esc_attr($placeholder),
        esc_attr($type),
        $help ? '<p class="imagina-help">' . esc_html($help) . '</p>' : ''
    );
}

function il_field_textarea($key, $label, $placeholder = '', $help = '', $rows = 3, $class = '') {
    $opts = il_get_all_options();
    printf(
        '<div class="imagina-control-group imagina-control-wide"><label class="imagina-control-label" for="%1$s">%2$s</label><textarea id="%1$s" name="%1$s" rows="%5$d" placeholder="%4$s" class="imagina-text-input %6$s">%3$s</textarea>%7$s</div>',
        esc_attr($key),
        esc_html($label),
        esc_textarea($opts[$key]),
        esc_attr($placeholder),
        (int) $rows,
        esc_attr($class),
        $help ? '<p class="imagina-help">' . esc_html($help) . '</p>' : ''
    );
}

function il_field_switch($key, $label, $help = '') {
    $opts = il_get_all_options();
    printf(
        '<div class="imagina-control-group"><label class="imagina-toggle-container imagina-toggle-small"><input type="checkbox" name="%1$s" value="1" %2$s><span class="imagina-toggle-slider"></span><span class="imagina-toggle-label">%3$s</span></label>%4$s</div>',
        esc_attr($key),
        checked(!empty($opts[$key]), true, false),
        esc_html($label),
        $help ? '<p class="imagina-help">' . esc_html($help) . '</p>' : ''
    );
}

function il_field_select($key, $label, $choices, $help = '') {
    $opts = il_get_all_options();
    $options_html = '';
    foreach ($choices as $value => $text) {
        $options_html .= sprintf('<option value="%s"%s>%s</option>', esc_attr($value), selected($opts[$key], $value, false), esc_html($text));
    }
    printf(
        '<div class="imagina-control-group"><label class="imagina-control-label" for="%1$s">%2$s</label><select id="%1$s" name="%1$s" class="imagina-select">%3$s</select>%4$s</div>',
        esc_attr($key),
        esc_html($label),
        $options_html,
        $help ? '<p class="imagina-help">' . esc_html($help) . '</p>' : ''
    );
}

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
                    <input type="range" name="il_logo_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($logo_duration) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($logo_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">Delay</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_logo_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($logo_delay) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($logo_delay) . 's</span>
                </div>
            </div>
        </div>

        <div style="padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 10px 0; color: #374151; font-size: 13px; font-weight: 700;">Formulario</h4>
            <div class="imagina-control-group" style="margin-bottom: 10px;">
                <label class="imagina-control-label">Velocidad</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($form_duration) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($form_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">Delay</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($form_delay) . '" data-unit="s">
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

    </div>
    
';
}

/**
 * Enqueue scripts para el admin
 */
function il_enqueue_admin_scripts($hook_suffix) {
    if ($hook_suffix != 'settings_page_imagina-login-settings') {
        return;
    }
    
    $base = plugin_dir_url(__FILE__);
    wp_enqueue_media();
    wp_enqueue_style('imagina-login-admin', $base . 'css/admin.css', [], IL_VERSION);
    wp_enqueue_script('imagina-login-admin-script', $base . 'js/media-uploader.js', ['jquery'], IL_VERSION, true);
    wp_enqueue_script('imagina-login-admin', $base . 'js/admin.js', ['jquery', 'imagina-login-admin-script'], IL_VERSION, true);
    wp_localize_script('imagina-login-admin', 'ilAdmin', [
        'resetUrl' => add_query_arg(['action' => 'il_reset_settings', '_wpnonce' => wp_create_nonce('il_reset_settings')], admin_url('admin-post.php')),
    ]);
}
add_action('admin_enqueue_scripts', 'il_enqueue_admin_scripts');