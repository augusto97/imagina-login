<?php
/**
 * Plugin Name: Imagina Login
 * Plugin URI:  https://imaginawp.com
 * Description: Personaliza la página de login de WordPress: 9 diseños, fondos con imagen, video o presentación, vista previa en vivo, modo oscuro, tipografías y textos propios.
 * Version:     2.4.0
 * Author:      IMAGINA WP
 * Author URI:  https://imaginawp.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: imagina-login
 * Domain Path: /languages
 * Requires at least: 6.1
 * Requires PHP: 7.4
 */

if(!defined('ABSPATH')) {
    die;
}

define('IL_VERSION', '2.4.0');

/**
 * Traducciones: el idioma base del plugin es el español; en /languages hay traducción al inglés
 * y la plantilla .pot para crear otras.
 */
function il_load_textdomain() {
    load_plugin_textdomain('imagina-login', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'il_load_textdomain');

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
        'il_custom_logo_dark'         => ['', 'id'],
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

    // Vista previa en vivo: aplicar los ajustes sin guardar del panel (ya limpios)
    if (!empty($_GET['il_live']) && il_is_preview()) {
        $draft = get_transient('il_preview_' . get_current_user_id());
        if (is_array($draft)) {
            foreach ($draft as $key => $value) {
                if (isset($options[$key])) {
                    $options[$key] = $value;
                }
            }
        }
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
    // La vista previa usa ajustes temporales: no se guardan en caché
    if (il_is_preview()) {
        return il_generate_dynamic_styles();
    }

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
        case 'slideshow':
            // La primera imagen es el fondo inmediato (sin parpadeo mientras carga el script)
            $slide_ids = array_filter(explode(',', (string) $opts['il_body_slides']));
            $first = $slide_ids ? wp_get_attachment_image_url((int) reset($slide_ids), 'full') : '';
            $css .= '--background-fallback: ' . ($first ? 'url(' . esc_url($first) . ')' : esc_attr($body_bg_color)) . ';';
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
        'body_slides' => $opts['il_body_slides'],
        'slide_interval' => $opts['il_slide_interval'],
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

    // Capas que deben ir DESPUÉS del CSS del diseño (se adjuntan a su hoja): modo oscuro,
    // tipografía y, al final, el CSS personalizado del usuario para que siempre tenga prioridad.
    $late_css = il_dark_mode_css($opts) . il_font_css($opts);
    if ($opts['il_custom_css'] !== '') {
        // "</" nunca debe aparecer dentro de <style>
        $late_css .= "\n/* CSS personalizado */\n" . str_replace('</', '<\/', $opts['il_custom_css']);
    }
    if ($late_css !== '') {
        wp_add_inline_style('imagina-login-template', $late_css);
    }

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
 * CSS del modo oscuro. "always" lo aplica siempre; "auto" solo si el sistema del visitante
 * está en modo oscuro (prefers-color-scheme). El fondo de la página no cambia: es del usuario.
 */
function il_dark_mode_css($opts) {
    if ($opts['il_dark_mode'] === 'off') {
        return '';
    }
    $card = '#1c1d22';
    $soft = '#24262c';
    $line = '#363941';
    $text = '#e6e7ea';
    $muted = '#a5a9b1';

    // Si el área del logo usa el gris claro por defecto, también se oscurece
    $default_logo_bg = $opts['il_logo_bg_type'] === 'color' && in_array(strtolower($opts['il_logo_bg_color']), ['#f9f9f9', '#f8f9fa', '#ffffff', '#fff'], true);

    $rules = "
        {C} div#login { background-color: {$card} !important; box-shadow: 0 10px 40px rgba(0,0,0,.45); }
        {C} div#login form { background: {$card} !important; }
        {C} div#login form [id*='overlay'], {C} div#login form [class*='overlay'] { background-color: inherit !important; color: {$text}; }
        " . ($default_logo_bg ? "{C} div#login h1 { background: {$soft} !important; }" : '') . "
        {P}.template-glass div#login { background: rgba(20, 22, 28, 0.55) !important; border-color: rgba(255,255,255,.12) !important; }
        {C} div#login form p, {C} div#login form .description, {C} #reg_passmail, {C} .indicator-hint { color: {$muted}; }
        {C} div#login .forgetmenot label { color: {$muted} !important; }
        {P} div#login form .input, {P} div#login form input[type=text], {P} div#login form input[type=email], {P} div#login form input[type=password] {
            background: {$soft} !important; border-color: {$line} !important; color: {$text} !important; }
        {P} div#login form .input:focus, {P} div#login form input[type=text]:focus, {P} div#login form input[type=email]:focus, {P} div#login form input[type=password]:focus {
            border-color: var(--primary-color, #009bde) !important; }
        {P} div#login form input[type=checkbox] { background: {$soft}; border-color: {$line}; }
        {P} .wp-hide-pw span { filter: invert(1); }
        {P} #nav, {P} #backtoblog, {P} .privacy-policy-page-link, {P} div#login .language-switcher { background: {$soft} !important; border-color: {$line} !important; }
        {P} #nav a, {P} #backtoblog a, {P} .privacy-policy-page-link a { color: {$muted} !important; }
        {P} .language-switcher form { background: {$card}; color: {$text}; }
        {P} p.message:not(.il-welcome-message), {P} .notice.notice-info.message, {P} #login-message { background: #10324a !important; color: #cfe8ff !important; }
        {P} #loginform .il-welcome-message { color: {$text}; }
        {LOGO}
        {P} #login_error { background: #4a1d22 !important; color: #ffd7dc !important; }
        {P} #login_error a { color: #ff9aa8 !important; }
        {P} .wp-core-ui .button:not(.button-primary):not(.wp-hide-pw) { background: {$soft}; color: {$text}; border-color: {$line}; }
    ";

    // Logo alternativo para fondo oscuro (opcional)
    $dark_logo = $opts['il_custom_logo_dark'] ? wp_get_attachment_image_url((int) $opts['il_custom_logo_dark'], 'full') : '';
    $rules = str_replace('{LOGO}', $dark_logo ? "{P} div#login h1 a { background-image: url('" . esc_url($dark_logo) . "') !important; }" : '', $rules);

    $build = function ($prefix) use ($rules) {
        $card_prefix = $prefix . ':not(.template-fullscreen):not(.template-glass)';
        return str_replace(['{C}', '{P}'], [$card_prefix, $prefix], $rules);
    };

    if ($opts['il_dark_mode'] === 'always') {
        return "\n/* Modo oscuro */\n" . $build('body.login.il-dark');
    }
    return "\n/* Modo oscuro automático */\n@media (prefers-color-scheme: dark) {" . $build('body.login.il-dark-auto') . "}\n";
}

/**
 * Carpeta local de una fuente descargada (dentro de uploads)
 */
function il_font_dir($key) {
    $uploads = wp_upload_dir();
    return [
        'path' => trailingslashit($uploads['basedir']) . 'imagina-login/fonts/' . $key,
        'url'  => trailingslashit($uploads['baseurl']) . 'imagina-login/fonts/' . $key,
    ];
}

/**
 * Descargar una fuente de Google Fonts al propio sitio (solo latin y latin-ext, woff2).
 * Se ejecuta al guardar los ajustes, nunca al cargar el login.
 */
function il_download_font($key) {
    $choices = il_font_choices();
    if ($key === '' || !isset($choices[$key])) {
        return true;
    }
    $dir = il_font_dir($key);
    if (file_exists($dir['path'] . '/font.css')) {
        return true;
    }

    $family = $choices[$key][1];
    $weights = ($key === 'lato') ? '400;700' : '400;600;700';
    $api = 'https://fonts.googleapis.com/css2?family=' . rawurlencode($family) . ':wght@' . $weights . '&display=swap';
    // Con un navegador moderno como user-agent, Google entrega woff2
    $args = ['timeout' => 20, 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36'];

    $response = wp_remote_get($api, $args);
    if (is_wp_error($response)) {
        return $response;
    }
    if (200 !== (int) wp_remote_retrieve_response_code($response)) {
        return new WP_Error('il_font_http', sprintf(/* translators: %d: HTTP status code */ __('Google Fonts respondió %d', 'imagina-login'), wp_remote_retrieve_response_code($response)));
    }

    preg_match_all('#/\*\s*([a-z-]+)\s*\*/\s*(@font-face\s*\{[^}]+\})#i', wp_remote_retrieve_body($response), $blocks, PREG_SET_ORDER);
    wp_mkdir_p($dir['path']);

    $css = '';
    foreach ($blocks as $block) {
        if (!in_array($block[1], ['latin', 'latin-ext'], true)) {
            continue;
        }
        if (!preg_match('#url\((https://fonts\.gstatic\.com/[^)\s]+\.woff2)\)#', $block[2], $url)) {
            continue;
        }
        $file = md5($url[1]) . '.woff2';
        if (!file_exists($dir['path'] . '/' . $file)) {
            $font = wp_remote_get($url[1], ['timeout' => 30]);
            if (is_wp_error($font) || 200 !== (int) wp_remote_retrieve_response_code($font)) {
                return is_wp_error($font) ? $font : new WP_Error('il_font_file', sprintf(/* translators: %s: font file name */ __('No se pudo descargar %s', 'imagina-login'), $file));
            }
            file_put_contents($dir['path'] . '/' . $file, wp_remote_retrieve_body($font));
        }
        $css .= str_replace($url[1], $file, $block[2]) . "\n";
    }

    if ($css === '') {
        return new WP_Error('il_font_empty', __('Google Fonts no devolvió archivos para esta fuente', 'imagina-login'));
    }
    file_put_contents($dir['path'] . '/font.css', $css);
    return true;
}

/**
 * Al cambiar la fuente en los ajustes: descargarla y avisar si falla
 */
function il_handle_font_change($key) {
    $result = il_download_font($key);
    if (is_wp_error($result)) {
        set_transient('il_font_error', $result->get_error_message(), 10 * MINUTE_IN_SECONDS);
    } else {
        delete_transient('il_font_error');
    }
}
add_action('update_option_il_font', function ($old, $new) { il_handle_font_change($new); }, 10, 2);
add_action('add_option_il_font', function ($name, $value) { il_handle_font_change($value); }, 10, 2);

/**
 * Cargar la fuente local en el login y aplicarla
 */
function il_font_css($opts) {
    $choices = il_font_choices();
    if ($opts['il_font'] === '' || !isset($choices[$opts['il_font']])) {
        return '';
    }
    $dir = il_font_dir($opts['il_font']);
    $file = $dir['path'] . '/font.css';
    if (file_exists($file)) {
        wp_enqueue_style('imagina-login-font', $dir['url'] . '/font.css', [], (string) filemtime($file));
    }
    $family = $choices[$opts['il_font']][1];
    return "\n/* Tipografía */\nbody.login, body.login input, body.login button, body.login select, body.login textarea, body.login .button { font-family: '" . esc_attr($family) . "', var(--fallback-font) !important; }\n";
}

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
 * ¿Es esta petición una vista previa del login?
 * Solo en wp-login.php, para un administrador con sesión y con un nonce válido.
 */
function il_is_preview() {
    static $is_preview = null;
    if ($is_preview !== null) {
        return $is_preview;
    }
    $is_preview = false;
    if (empty($_GET['il_preview']) || !isset($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'wp-login.php') {
        return $is_preview;
    }
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return $is_preview;
    }
    $is_preview = (bool) wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['il_preview'])), 'il_preview');
    return $is_preview;
}

/**
 * URL de la vista previa. Con $live = true usa los ajustes sin guardar del panel.
 */
function il_preview_url($live = false) {
    $args = ['il_preview' => wp_create_nonce('il_preview')];
    if ($live) {
        $args['il_live'] = '1';
    }
    return add_query_arg($args, wp_login_url());
}

/**
 * Vista previa: mostrar el wp-login.php real como lo ve un visitante, sin tocar la sesión
 * del administrador. (Antes se usaba una copia hecha a mano que no coincidía con el login real.)
 */
function il_preview_setup() {
    if (!il_is_preview()) {
        return;
    }
    // Nunca crear ni borrar cookies de sesión desde la vista previa
    add_filter('send_auth_cookies', '__return_false', 999);

    // WordPress identifica al admin por su cookie y lo redirige al escritorio.
    // En la vista previa, un acceso sin usuario ni contraseña se trata como el de un visitante.
    add_filter('authenticate', function ($user, $username, $password) {
        if ($username === '' && $password === '') {
            $error = new WP_Error();
            $error->add('empty_username', '');
            $error->add('empty_password', '');
            return $error;
        }
        return $user;
    }, 100, 3);

    nocache_headers();

    // Es solo una vista previa: no enviar formularios ni salir de la página
    add_action('login_footer', function () {
        echo '<script>
            document.addEventListener("submit", function (e) { e.preventDefault(); }, true);
            document.addEventListener("click", function (e) { if (e.target.closest("a")) { e.preventDefault(); } }, true);
        </script>';
    }, 99);
}
add_action('login_init', 'il_preview_setup', 1);

/**
 * Guardar temporalmente los ajustes sin guardar del panel para la vista previa en vivo
 */
function il_ajax_preview_store() {
    check_ajax_referer('il_preview_store');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(null, 403);
    }
    $raw = isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
    $data = [];
    foreach (il_option_schema() as $key => list($default, $type)) {
        if ($type === 'bool') {
            // Las casillas desmarcadas no se envían
            $data[$key] = !empty($raw[$key]);
        } elseif (array_key_exists($key, $raw)) {
            $data[$key] = il_sanitize_value($type, $raw[$key], $default);
        }
    }
    set_transient('il_preview_' . get_current_user_id(), $data, HOUR_IN_SECONDS);
    wp_send_json_success(['url' => add_query_arg('t', time(), il_preview_url(true))]);
}
add_action('wp_ajax_il_preview_store', 'il_ajax_preview_store');

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

    // Presentación de imágenes de fondo
    if ($cached_styles['body_bg_type'] === 'slideshow') {
        il_render_slideshow($cached_styles);
        return;
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
 * Presentación de fondo: capas de imagen que se alternan con un fundido.
 * La primera imagen ya está puesta como fondo de la página desde el CSS crítico.
 * Con "reducir movimiento" activado en el sistema, se queda en la primera imagen.
 */
function il_render_slideshow($cached_styles) {
    $ids = array_filter(array_map('absint', explode(',', (string) $cached_styles['body_slides'])));
    $urls = [];
    foreach ($ids as $id) {
        $url = wp_get_attachment_image_url($id, 'full');
        if ($url) {
            $urls[] = $url;
        }
    }
    if (count($urls) < 2) {
        return; // Con una sola imagen basta el fondo del CSS crítico
    }
    $interval = max(3, min(30, (int) $cached_styles['slide_interval'])) * 1000;

    echo '<div id="il-slideshow" aria-hidden="true">';
    foreach ($urls as $i => $url) {
        printf('<div class="il-slide%s" style="background-image:url(%s)"></div>', $i === 0 ? ' is-active' : '', esc_url($url));
    }
    echo '</div>';
    echo '<script>
        (function () {
            var slides = document.querySelectorAll("#il-slideshow .il-slide");
            if (slides.length < 2 || (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches)) return;
            var current = 0;
            setInterval(function () {
                slides[current].classList.remove("is-active");
                current = (current + 1) % slides.length;
                slides[current].classList.add("is-active");
            }, ' . (int) $interval . ');
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
        return ['type' => 'file', 'label' => __('Archivo de video', 'imagina-login'), 'url' => $url];
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
 * Diseños completos de un clic: diseño + fondo + colores + tipografía.
 * No tocan el logo ni los textos del usuario.
 */
function il_presets() {
    $gradient = function ($type, $dir, $c1, $c2) {
        return ['il_body_bg_type' => 'gradient', 'il_body_gradient_type' => $type, 'il_body_gradient_direction' => $dir, 'il_body_gradient_color1' => $c1, 'il_body_gradient_color2' => $c2];
    };
    $colors = function ($label, $button, $hover, $link) {
        return ['il_use_custom_colors' => true, 'il_label_color' => $label, 'il_button_color' => $button, 'il_button_hover_color' => $hover, 'il_link_color' => $link];
    };
    return [
        'corporativo' => [
            'name' => __('Corporativo', 'imagina-login'),
            'desc' => __('Dos columnas, azul sobrio, tipografía Inter.', 'imagina-login'),
            'swatch' => 'linear-gradient(135deg,#0f4c81,#1e88e5)',
            'settings' => ['il_login_template' => 'classic', 'il_logo_bg_type' => 'color', 'il_logo_bg_color' => '#f5f7fa', 'il_font' => 'inter', 'il_dark_mode' => 'off']
                + $gradient('linear', 'diagonal1', '#0f4c81', '#1e88e5') + $colors('#0f4c81', '#0f4c81', '#0b3a63', '#0f4c81'),
        ],
        'medianoche' => [
            'name' => __('Medianoche', 'imagina-login'),
            'desc' => __('Caja compacta en modo oscuro con acentos violeta.', 'imagina-login'),
            'swatch' => 'radial-gradient(circle,#334155,#0f172a)',
            'settings' => ['il_login_template' => 'boxed', 'il_logo_bg_type' => 'color', 'il_logo_bg_color' => '#f9f9f9', 'il_font' => 'dm-sans', 'il_dark_mode' => 'always']
                + $gradient('radial', 'vertical', '#334155', '#0f172a') + $colors('#a78bfa', '#7c3aed', '#6d28d9', '#a78bfa'),
        ],
        'minimal' => [
            'name' => __('Minimalista', 'imagina-login'),
            'desc' => __('Fondo gris claro, todo en negro, sin distracciones.', 'imagina-login'),
            'swatch' => 'linear-gradient(135deg,#f4f4f5,#d4d4d8)',
            'settings' => ['il_login_template' => 'boxed', 'il_body_bg_type' => 'color', 'il_body_bg_color' => '#f4f4f5', 'il_logo_bg_type' => 'color', 'il_logo_bg_color' => '#ffffff', 'il_font' => 'inter', 'il_dark_mode' => 'off']
                + $colors('#18181b', '#18181b', '#000000', '#18181b'),
        ],
        'startup' => [
            'name' => __('Startup', 'imagina-login'),
            'desc' => __('Efecto cristal sobre degradado vibrante, tipografía Poppins.', 'imagina-login'),
            'swatch' => 'linear-gradient(45deg,#6366f1,#ec4899)',
            'settings' => ['il_login_template' => 'glass', 'il_font' => 'poppins', 'il_dark_mode' => 'off']
                + $gradient('linear', 'diagonal1', '#6366f1', '#ec4899') + $colors('#ffffff', '#4f46e5', '#4338ca', '#4f46e5'),
        ],
        'naturaleza' => [
            'name' => __('Naturaleza', 'imagina-login'),
            'desc' => __('Panel lateral 50/50 en verdes, tipografía Nunito.', 'imagina-login'),
            'swatch' => 'linear-gradient(135deg,#134e4a,#14b8a6)',
            'settings' => ['il_login_template' => 'sidebar-half-left', 'il_logo_bg_type' => 'color', 'il_logo_bg_color' => '#ecfdf5', 'il_font' => 'nunito', 'il_dark_mode' => 'off']
                + $gradient('linear', 'diagonal2', '#134e4a', '#14b8a6') + $colors('#0f766e', '#0f766e', '#115e59', '#0f766e'),
        ],
        'atardecer' => [
            'name' => __('Atardecer', 'imagina-login'),
            'desc' => __('Pantalla dividida en naranjas y rosas, tipografía Montserrat.', 'imagina-login'),
            'swatch' => 'linear-gradient(-45deg,#f97316,#db2777)',
            'settings' => ['il_login_template' => 'split', 'il_logo_bg_type' => 'gradient', 'il_logo_gradient_type' => 'linear', 'il_logo_gradient_direction' => 'vertical', 'il_logo_gradient_color1' => '#fff7ed', 'il_logo_gradient_color2' => '#ffe4e6', 'il_font' => 'montserrat', 'il_dark_mode' => 'off']
                + $gradient('linear', 'diagonal2', '#f97316', '#db2777') + $colors('#c2410c', '#ea580c', '#c2410c', '#c2410c'),
        ],
    ];
}

/**
 * Exportar los ajustes guardados como JSON
 */
function il_export_settings() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('No tienes permisos para hacer esto.', 'imagina-login'), 403);
    }
    check_admin_referer('il_export_settings');

    $settings = il_get_all_options();
    // Las imágenes se identifican por ID, que cambia entre sitios: se guarda también su dirección
    $media = [];
    foreach (il_media_option_keys() as $key) {
        $ids = array_filter(explode(',', (string) $settings[$key]));
        $media[$key] = array_values(array_filter(array_map('wp_get_attachment_url', array_map('intval', $ids))));
    }

    $payload = [
        'plugin'   => 'imagina-login',
        'version'  => IL_VERSION,
        'exported' => gmdate('c'),
        'site'     => home_url(),
        'settings' => $settings,
        'media'    => $media,
    ];

    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="imagina-login-' . gmdate('Y-m-d') . '.json"');
    echo wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
add_action('admin_post_il_export_settings', 'il_export_settings');

/**
 * Ajustes que guardan IDs de la biblioteca de medios
 */
function il_media_option_keys() {
    return ['il_custom_logo', 'il_custom_logo_dark', 'il_body_background_image', 'il_logo_background_image', 'il_body_background_video', 'il_body_slides'];
}

/**
 * Importar ajustes desde un JSON exportado. Solo acepta claves conocidas y limpia cada valor.
 */
function il_import_settings() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('No tienes permisos para hacer esto.', 'imagina-login'), 403);
    }
    check_admin_referer('il_import_settings');
    $back = admin_url('options-general.php?page=imagina-login-settings');

    $file = isset($_FILES['il_import_file']) ? $_FILES['il_import_file'] : null;
    if (!$file || !empty($file['error']) || empty($file['tmp_name']) || $file['size'] > 512000) {
        wp_safe_redirect(add_query_arg('il_import', 'file', $back));
        exit;
    }
    $data = json_decode(file_get_contents($file['tmp_name']), true);
    if (!is_array($data) || ($data['plugin'] ?? '') !== 'imagina-login' || !is_array($data['settings'] ?? null)) {
        wp_safe_redirect(add_query_arg('il_import', 'invalid', $back));
        exit;
    }

    $media_keys = il_media_option_keys();
    foreach (il_option_schema() as $key => list($default, $type)) {
        if (!array_key_exists($key, $data['settings'])) {
            continue;
        }
        $value = $data['settings'][$key];
        if (in_array($key, $media_keys, true)) {
            // Usar el ID solo si es un adjunto de este sitio con la misma dirección; si no, buscar por dirección
            $urls = isset($data['media'][$key]) && is_array($data['media'][$key]) ? $data['media'][$key] : [];
            $ids = [];
            foreach (array_values(array_filter(explode(',', (string) $value))) as $i => $id) {
                $url = isset($urls[$i]) ? esc_url_raw($urls[$i]) : '';
                if (get_post_type((int) $id) === 'attachment' && (!$url || wp_get_attachment_url((int) $id) === $url)) {
                    $ids[] = (int) $id;
                } elseif ($url && ($found = attachment_url_to_postid($url))) {
                    $ids[] = $found;
                }
            }
            $value = implode(',', $ids);
        }
        update_option($key, il_sanitize_value($type, $value, $default));
    }

    wp_safe_redirect(add_query_arg('il_import', 'ok', $back));
    exit;
}
add_action('admin_post_il_import_settings', 'il_import_settings');

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
    if (!isset($_GET['page']) || $_GET['page'] !== 'imagina-login-settings') {
        return;
    }
    if (isset($_GET['il_reset'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Ajustes restaurados a sus valores por defecto.', 'imagina-login') . '</p></div>';
    }
    if (isset($_GET['il_import'])) {
        $import_messages = [
            'ok'      => ['success', __('Ajustes importados correctamente.', 'imagina-login')],
            'file'    => ['error', __('No se pudo leer el archivo. Sube un .json exportado desde Imagina Login (máximo 500 KB).', 'imagina-login')],
            'invalid' => ['error', __('El archivo no es una exportación válida de Imagina Login.', 'imagina-login')],
        ];
        $code = sanitize_key(wp_unslash($_GET['il_import']));
        if (isset($import_messages[$code])) {
            printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr($import_messages[$code][0]), esc_html($import_messages[$code][1]));
        }
    }
    $font_error = get_transient('il_font_error');
    if ($font_error) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('No se pudo descargar la fuente elegida; el login usará la tipografía del sistema hasta que se descargue.', 'imagina-login') . ' (' . esc_html($font_error) . ')</p></div>';
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
    add_settings_section('il_template_section', __('Diseño del Login', 'imagina-login'), null, 'imagina-login-settings');
    add_settings_section('il_body_section', __('Configuración del Fondo de la Página', 'imagina-login'), null, 'imagina-login-settings');
    add_settings_section('il_logo_section', __('Configuración del Fondo del Logo', 'imagina-login'), null, 'imagina-login-settings');
    add_settings_section('il_colors_section', __('Colores del Formulario', 'imagina-login'), null, 'imagina-login-settings');
    add_settings_section('il_transitions_section', __('Transiciones y Animaciones', 'imagina-login'), null, 'imagina-login-settings');
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
                    <p class="imagina-subtitle"><?php esc_html_e('Personaliza tu página de login de WordPress', 'imagina-login'); ?></p>
                </div>
                <div class="imagina-preview-section">
                    <button type="button" class="imagina-preview-btn" id="il-live-preview-toggle">
                        <span class="dashicons dashicons-desktop"></span>
                        <?php esc_html_e('Vista previa en vivo', 'imagina-login'); ?>
                    </button>
                    <a href="<?php echo esc_url(il_preview_url()); ?>" target="_blank" rel="noopener" class="imagina-preview-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('Ver Login', 'imagina-login'); ?>
                    </a>
                </div>
            </div>
        </div>

        <form action="options.php" method="post" class="imagina-form">
            <?php settings_fields('imagina_login_options'); ?>

            <div class="imagina-sections">
                <!-- Diseños rápidos -->
                <div class="imagina-section" id="il-section-presets">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">⚡</span> <?php esc_html_e('Diseños rápidos', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Aplica un diseño completo (plantilla, fondo, colores y tipografía) y luego ajústalo a tu gusto. No cambia tu logo ni tus textos.', 'imagina-login'); ?></p>
                    </div>
                    <div class="imagina-section-content">
                        <div class="il-presets">
                            <?php foreach (il_presets() as $preset_key => $preset) : ?>
                                <button type="button" class="il-preset" data-preset="<?php echo esc_attr($preset_key); ?>">
                                    <span class="il-preset-swatch" style="background: <?php echo esc_attr($preset['swatch']); ?>"></span>
                                    <strong><?php echo esc_html($preset['name']); ?></strong>
                                    <span><?php echo esc_html($preset['desc']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <p class="il-preset-notice imagina-help" aria-live="polite"></p>
                    </div>
                </div>

                <!-- NUEVA SECCIÓN: SELECTOR DE TEMPLATES -->
                <div class="imagina-section imagina-template-section">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🎭</span> <?php esc_html_e('Diseño del Login', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Elige el diseño que mejor se adapte a tu marca', 'imagina-login'); ?></p>
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
                                    <h3>🏢 <?php esc_html_e('Clásico Dos Columnas', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Logo a la izquierda, formulario a la derecha. Ideal para empresas corporativas.', 'imagina-login'); ?></p>
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
                                    <h3>◀️ <?php esc_html_e('Sidebar Izquierda', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Panel lateral izquierdo full height. Variante invertida del sidebar.', 'imagina-login'); ?></p>
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
                                    <h3>⚖️ <?php esc_html_e('Sidebar 50/50 Derecha', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Diseño equilibrado 50/50 con sidebar derecho. Perfecto para balance visual.', 'imagina-login'); ?></p>
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
                                    <h3>⚖️ <?php esc_html_e('Sidebar 50/50 Izquierda', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Diseño equilibrado 50/50 con sidebar izquierdo. Balance con énfasis izquierdo.', 'imagina-login'); ?></p>
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
                                    <h3>🎨 <?php esc_html_e('Pantalla Dividida', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Split 60/40 con efecto visual impactante. Para agencias creativas.', 'imagina-login'); ?></p>
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
                                    <h3>🖥️ <?php esc_html_e('Pantalla Completa', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Login cubre toda la pantalla. Ideal para aplicaciones web y SaaS.', 'imagina-login'); ?></p>
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
                                    <h3>💎 <?php esc_html_e('Glassmorphism', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Efecto cristal moderno sobre fondo. Para startups tech y apps premium.', 'imagina-login'); ?></p>
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
                                    <h3>▶️ <?php esc_html_e('Sidebar Derecha', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Panel lateral derecho full height. Perfecto para intranets y sistemas de gestión.', 'imagina-login'); ?></p>
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
                                    <h3>📦 <?php esc_html_e('Centrado Compacto', 'imagina-login'); ?></h3>
                                    <p><?php esc_html_e('Caja pequeña flotante sobre fondo. Ideal para sitios minimalistas.', 'imagina-login'); ?></p>
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
                        <h2><span class="section-icon">🖼️</span> <?php esc_html_e('Fondo de la Página', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Personaliza el fondo completo de tu página de login', 'imagina-login'); ?></p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-type-selector">
                            <label class="imagina-main-label"><?php esc_html_e('Tipo de fondo:', 'imagina-login'); ?></label>
                            <div class="imagina-type-tabs">
                                <input type="radio" name="il_body_bg_type" value="color" id="body-color" <?php checked($body_bg_type, 'color'); ?>>
                                <label for="body-color" class="imagina-tab">
                                    <span class="tab-icon">🎨</span>
                                    <span class="tab-text"><?php esc_html_e('Color', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="gradient" id="body-gradient" <?php checked($body_bg_type, 'gradient'); ?>>
                                <label for="body-gradient" class="imagina-tab">
                                    <span class="tab-icon">🌈</span>
                                    <span class="tab-text"><?php esc_html_e('Degradado', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="image" id="body-image" <?php checked($body_bg_type, 'image'); ?>>
                                <label for="body-image" class="imagina-tab">
                                    <span class="tab-icon">🖼️</span>
                                    <span class="tab-text"><?php esc_html_e('Imagen', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="video" id="body-video" <?php checked($body_bg_type, 'video'); ?>>
                                <label for="body-video" class="imagina-tab">
                                    <span class="tab-icon">🎬</span>
                                    <span class="tab-text"><?php esc_html_e('Video', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_body_bg_type" value="slideshow" id="body-slideshow" <?php checked($body_bg_type, 'slideshow'); ?>>
                                <label for="body-slideshow" class="imagina-tab">
                                    <span class="tab-icon">🖼️</span>
                                    <span class="tab-text"><?php esc_html_e('Presentación', 'imagina-login'); ?></span>
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
                        <h2><span class="section-icon">🏢</span> <?php esc_html_e('Logo y Área del Logo', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Sube tu logo y personaliza el fondo del área donde aparece', 'imagina-login'); ?></p>
                    </div>

                    <div class="imagina-section-content">
                        <?php
                        $custom_logo_id = get_option('il_custom_logo', '');
                        $current_logo_url = '';
                        if ($custom_logo_id) {
                            $current_logo_url = wp_get_attachment_image_url($custom_logo_id, 'medium');
                        }
                        ?>
                        <div class="imagina-fields-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                            <?php
                            il_field_image('il_custom_logo', __('Imagen del logo', 'imagina-login'), __('Usa el logo del tema', 'imagina-login'), __('Si no subes uno, se usa el logo del tema o el icono del sitio.', 'imagina-login'));
                            il_field_image('il_custom_logo_dark', __('Logo para modo oscuro (opcional)', 'imagina-login'), __('Usa el mismo logo', 'imagina-login'), __('Versión clara del logo para fondos oscuros. Solo se usa con el modo oscuro activo.', 'imagina-login'));
                            ?>
                            <div class="imagina-control-group">
                                <label class="imagina-control-label"><?php esc_html_e('Ancho del logo', 'imagina-login'); ?></label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_max_size" class="imagina-slider" min="40" max="400" step="10" value="<?php echo esc_attr(get_option('il_logo_max_size', '200')); ?>" data-unit="px">
                                    <span class="imagina-slider-value"><?php echo esc_html(get_option('il_logo_max_size', '200')); ?>px</span>
                                </div>
                                <label class="imagina-control-label" style="margin-top: 14px;"><?php esc_html_e('Alto del logo', 'imagina-login'); ?></label>
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
                                <label class="imagina-control-label"><?php esc_html_e('Espacio vertical del logo', 'imagina-login'); ?></label>
                                <div class="imagina-slider-container">
                                    <input type="range" name="il_logo_area_height" class="imagina-slider" min="0" max="80" step="5" value="<?php echo esc_attr($logo_area_height); ?>" data-unit="px" data-zero-label="Auto">
                                    <span class="imagina-slider-value"><?php echo intval($logo_area_height) === 0 ? 'Auto' : esc_html($logo_area_height) . 'px'; ?></span>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;"><?php esc_html_e('0 = usa el padding del template. Ajusta el espacio arriba y abajo del logo.', 'imagina-login'); ?></p>
                            </div>

                            <div class="imagina-control-group">
                                <label class="imagina-control-label"><?php esc_html_e('Borde separador', 'imagina-login'); ?></label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="<?php echo empty($logo_area_border_color) ? 'hidden' : 'color'; ?>" name="il_logo_area_border_color" value="<?php echo esc_attr(!empty($logo_area_border_color) ? $logo_area_border_color : ''); ?>" class="imagina-color-input" style="width: 36px; height: 36px;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #374151; cursor: pointer;">
                                        <input type="checkbox" id="il_border_toggle" <?php echo !empty($logo_area_border_color) ? 'checked' : ''; ?> onchange="toggleBorderColor(this)" style="width: 16px; height: 16px;">
                                        <?php esc_html_e('Mostrar borde', 'imagina-login'); ?>
                                    </label>
                                </div>
                                <p style="margin: 6px 0 0 0; color: #6b7280; font-size: 11px;"><?php esc_html_e('Línea entre el logo y el formulario.', 'imagina-login'); ?></p>
                            </div>
                        </div>

                        <div class="imagina-type-selector">
                            <label class="imagina-main-label"><?php esc_html_e('Fondo del área del logo:', 'imagina-login'); ?></label>
                            <div class="imagina-type-tabs">
                                <input type="radio" name="il_logo_bg_type" value="color" id="logo-color" <?php checked($logo_bg_type, 'color'); ?>>
                                <label for="logo-color" class="imagina-tab">
                                    <span class="tab-icon">🎨</span>
                                    <span class="tab-text"><?php esc_html_e('Color', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_logo_bg_type" value="gradient" id="logo-gradient" <?php checked($logo_bg_type, 'gradient'); ?>>
                                <label for="logo-gradient" class="imagina-tab">
                                    <span class="tab-icon">🌈</span>
                                    <span class="tab-text"><?php esc_html_e('Degradado', 'imagina-login'); ?></span>
                                </label>

                                <input type="radio" name="il_logo_bg_type" value="image" id="logo-image" <?php checked($logo_bg_type, 'image'); ?>>
                                <label for="logo-image" class="imagina-tab">
                                    <span class="tab-icon">🖼️</span>
                                    <span class="tab-text"><?php esc_html_e('Imagen', 'imagina-login'); ?></span>
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
                        <h2><span class="section-icon">🎨</span> <?php esc_html_e('Colores del Formulario', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Personaliza los colores de labels, botones y enlaces', 'imagina-login'); ?></p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-toggle-section">
                            <label class="imagina-toggle-container">
                                <input type="checkbox" name="il_use_custom_colors" value="1" <?php checked($use_custom_colors); ?> onchange="toggleColorOptions(this)">
                                <span class="imagina-toggle-slider"></span>
                                <span class="imagina-toggle-label"><?php esc_html_e('Usar colores personalizados', 'imagina-login'); ?></span>
                            </label>
                            <p class="imagina-toggle-description">
                                <?php esc_html_e('Cuando está desactivado, heredará los colores del tema. Cuando está activado, podrás personalizar cada color individualmente.', 'imagina-login'); ?>
                            </p>
                        </div>

                        <div class="imagina-color-controls" id="custom-color-controls" style="<?php echo $use_custom_colors ? '' : 'display: none;'; ?>">
                            <?php il_render_color_controls(); ?>
                        </div>
                    </div>
                </div>

                <!-- Sección: estilo (tipografía y modo oscuro) -->
                <div class="imagina-section" id="il-section-style">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🔤</span> <?php esc_html_e('Tipografía y modo oscuro', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Fuente del login y apariencia para quienes usan el modo oscuro.', 'imagina-login'); ?></p>
                    </div>
                    <div class="imagina-section-content">
                        <div class="imagina-fields-grid">
                            <?php
                            $font_choices = [];
                            foreach (il_font_choices() as $font_key => $font_info) {
                                $font_choices[$font_key] = $font_info[0];
                            }
                            il_field_select('il_font', __('Fuente', 'imagina-login'), $font_choices, __('Se descarga una vez y se sirve desde tu sitio: los visitantes no se conectan a Google (cumple RGPD).', 'imagina-login'));
                            il_field_select('il_dark_mode', __('Modo oscuro', 'imagina-login'), [
                                'off'    => __('Desactivado', 'imagina-login'),
                                'auto'   => __('Automático (según el sistema del visitante)', 'imagina-login'),
                                'always' => __('Siempre oscuro', 'imagina-login'),
                            ], __('Oscurece el formulario y el pie. El fondo de la página se mantiene. Sube un logo para modo oscuro en la sección del logo si el tuyo es oscuro.', 'imagina-login'));
                            ?>
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
                        <h2><span class="section-icon">✨</span> <?php esc_html_e('Transiciones y Animaciones', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('Configura cómo aparecen las imágenes, videos, logo y formulario', 'imagina-login'); ?></p>
                    </div>
                    
                    <div class="imagina-section-content">
                        <div class="imagina-toggle-section">
                            <label class="imagina-toggle-container">
                                <input type="checkbox" name="il_enable_transitions" value="1" <?php checked($enable_transitions); ?> onchange="toggleTransitionOptions(this)">
                                <span class="imagina-toggle-slider"></span>
                                <span class="imagina-toggle-label"><?php esc_html_e('Activar transiciones suaves', 'imagina-login'); ?></span>
                            </label>
                            <p class="imagina-toggle-description">
                                <?php esc_html_e('Las transiciones hacen que todos los elementos aparezcan suavemente en secuencia: fondo → logo → formulario.', 'imagina-login'); ?>
                            </p>
                        </div>

                        <div class="imagina-transition-controls" id="transition-controls" style="<?php echo $enable_transitions ? '' : 'display: none;'; ?>">
                            <?php il_render_transition_controls(); ?>
                        </div>
                    </div>
                </div>

                <!-- Sección: avanzado -->
                <div class="imagina-section" id="il-section-advanced">
                    <div class="imagina-section-header">
                        <h2><span class="section-icon">🛠️</span> <?php esc_html_e('Avanzado', 'imagina-login'); ?></h2>
                        <p><?php esc_html_e('CSS propio para ajustes finos.', 'imagina-login'); ?></p>
                    </div>
                    <div class="imagina-section-content">
                        <?php
                        il_field_textarea('il_custom_css', __('CSS personalizado', 'imagina-login'), "body.login div#login form .button-primary {\n    border-radius: 999px;\n}", __('Se aplica al final, por encima del diseño elegido. Por seguridad se eliminan etiquetas HTML, @import y javascript:.', 'imagina-login'), 8, 'imagina-code');
                        ?>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="imagina-actions">
                <button type="submit" class="imagina-save-btn">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Guardar Cambios', 'imagina-login'); ?>
                </button>
                <button type="button" class="imagina-reset-btn" onclick="confirmReset()">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e('Restaurar por Defecto', 'imagina-login'); ?>
                </button>
            </div>
        </form>

        <!-- Importar / exportar (formularios propios, fuera del formulario de ajustes) -->
        <div class="imagina-form">
            <div class="imagina-section" id="il-section-transfer">
                <div class="imagina-section-header">
                    <h2><span class="section-icon">📦</span> <?php esc_html_e('Importar y exportar', 'imagina-login'); ?></h2>
                    <p><?php esc_html_e('Copia tu diseño a otro sitio o guarda una copia de seguridad de los ajustes.', 'imagina-login'); ?></p>
                </div>
                <div class="imagina-section-content imagina-fields-grid">
                    <div class="imagina-control-group">
                        <label class="imagina-control-label"><?php esc_html_e('Exportar', 'imagina-login'); ?></label>
                        <a class="imagina-upload-btn" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=il_export_settings'), 'il_export_settings')); ?>"><span class="dashicons dashicons-download"></span> <?php esc_html_e('Descargar ajustes (.json)', 'imagina-login'); ?></a>
                        <p class="imagina-help"><?php esc_html_e('Incluye los ajustes guardados y las direcciones de las imágenes usadas.', 'imagina-login'); ?></p>
                    </div>
                    <form class="imagina-control-group" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <label class="imagina-control-label" for="il-import-file"><?php esc_html_e('Importar', 'imagina-login'); ?></label>
                        <input type="hidden" name="action" value="il_import_settings">
                        <?php wp_nonce_field('il_import_settings'); ?>
                        <input type="file" id="il-import-file" name="il_import_file" accept="application/json,.json" required>
                        <button type="submit" class="imagina-upload-btn" style="margin-top:8px;"><span class="dashicons dashicons-upload"></span> <?php esc_html_e('Importar ajustes', 'imagina-login'); ?></button>
                        <p class="imagina-help"><?php esc_html_e('Reemplaza los ajustes actuales. Las imágenes se buscan en tu biblioteca de medios por su dirección; si no existen, quedan vacías.', 'imagina-login'); ?></p>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tips y ayuda -->
        <div class="imagina-help-section">
            <h3><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Consejos útiles', 'imagina-login'); ?></h3>
            <div class="imagina-tips">
                <div class="imagina-tip">
                    <strong>🎭 <?php esc_html_e('Templates:', 'imagina-login'); ?></strong> <?php esc_html_e('Cada diseño se adapta automáticamente a móviles. Prueba varios antes de decidir.', 'imagina-login'); ?>
                </div>
                <div class="imagina-tip">
                    <strong>🎨 <?php esc_html_e('Colores:', 'imagina-login'); ?></strong> <?php esc_html_e('Usa colores que combinen con tu marca para mantener consistencia visual.', 'imagina-login'); ?>
                </div>
                <div class="imagina-tip">
                    <strong>🖼️ <?php esc_html_e('Imágenes:', 'imagina-login'); ?></strong> <?php esc_html_e('Para mejores resultados, usa imágenes de al menos 1920x1080px.', 'imagina-login'); ?>
                </div>
                <div class="imagina-tip">
                    <strong>🎬 <?php esc_html_e('Videos:', 'imagina-login'); ?></strong> <?php esc_html_e('Los videos se reproducen automáticamente sin sonido. Formato recomendado: MP4.', 'imagina-login'); ?>
                </div>
                <div class="imagina-tip">
                    <strong>📱 <?php esc_html_e('Móviles:', 'imagina-login'); ?></strong> <?php esc_html_e('Todos los fondos y templates se adaptan automáticamente a dispositivos móviles.', 'imagina-login'); ?>
                </div>
                <div class="imagina-tip">
                    <strong>✨ <?php esc_html_e('Transiciones:', 'imagina-login'); ?></strong> Ajusta velocidad y retraso del logo y del formulario; la secuencia se muestra en la sección de animaciones.
                </div>
            </div>
        </div>

        <!-- Panel de vista previa en vivo -->
        <aside id="il-preview-drawer" aria-label="<?php esc_attr_e('Vista previa en vivo', 'imagina-login'); ?>" hidden>
            <div class="il-preview-toolbar">
                <strong><?php esc_html_e('Vista previa en vivo', 'imagina-login'); ?></strong>
                <div class="il-preview-devices" role="group">
                    <button type="button" data-device="desktop" class="is-active" title="<?php esc_attr_e('Escritorio', 'imagina-login'); ?>"><span class="dashicons dashicons-desktop"></span></button>
                    <button type="button" data-device="tablet" title="<?php esc_attr_e('Tableta', 'imagina-login'); ?>"><span class="dashicons dashicons-tablet"></span></button>
                    <button type="button" data-device="mobile" title="<?php esc_attr_e('Móvil', 'imagina-login'); ?>"><span class="dashicons dashicons-smartphone"></span></button>
                </div>
                <span class="il-preview-status" aria-live="polite"></span>
                <button type="button" class="il-preview-close" title="<?php esc_attr_e('Cerrar', 'imagina-login'); ?>"><span class="dashicons dashicons-no-alt"></span></button>
            </div>
            <div class="il-preview-stage"><iframe title="<?php esc_attr_e('Vista previa del login', 'imagina-login'); ?>"></iframe></div>
            <p class="il-preview-note"><?php esc_html_e('Muestra el login real con los cambios sin guardar. Recuerda pulsar "Guardar Cambios".', 'imagina-login'); ?></p>
        </aside>
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
                <span class="imagina-color-label">' . esc_html__('Color de fondo de la página', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($body_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($body_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Elige un color que represente tu marca y combine con el resto de tu sitio web.', 'imagina-login') . '
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
                <label class="imagina-control-label">' . esc_html__('Tipo de degradado', 'imagina-login') . '</label>
                <select name="il_body_gradient_type" class="imagina-select">
                    <option value="linear"' . selected($gradient_type, 'linear', false) . '>🔄 ' . esc_html__('Lineal', 'imagina-login') . '</option>
                    <option value="radial"' . selected($gradient_type, 'radial', false) . '>⭕ ' . esc_html__('Radial', 'imagina-login') . '</option>
                </select>
            </div>
            
            <div class="imagina-control-group">
                <label class="imagina-control-label">' . esc_html__('Dirección', 'imagina-login') . '</label>
                <select name="il_body_gradient_direction" class="imagina-select">
                    <option value="vertical"' . selected($gradient_direction, 'vertical', false) . '>⬇️ ' . esc_html__('Vertical (arriba → abajo)', 'imagina-login') . '</option>
                    <option value="horizontal"' . selected($gradient_direction, 'horizontal', false) . '>➡️ ' . esc_html__('Horizontal (izq → der)', 'imagina-login') . '</option>
                    <option value="diagonal1"' . selected($gradient_direction, 'diagonal1', false) . '>↗️ ' . esc_html__('Diagonal ↗', 'imagina-login') . '</option>
                    <option value="diagonal2"' . selected($gradient_direction, 'diagonal2', false) . '>↙️ ' . esc_html__('Diagonal ↙', 'imagina-login') . '</option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="imagina-color-picker">
                <input type="color" name="il_body_gradient_color1" value="' . esc_attr($gradient_color1) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">' . esc_html__('Color inicial', 'imagina-login') . '</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($gradient_color1)) . '</span>
                </div>
            </div>
            
            <div class="imagina-color-picker">
                <input type="color" name="il_body_gradient_color2" value="' . esc_attr($gradient_color2) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">' . esc_html__('Color final', 'imagina-login') . '</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($gradient_color2)) . '</span>
                </div>
            </div>
        </div>
        
        <div class="imagina-gradient-preview" style="margin-top: 20px; height: 60px; border-radius: 8px; border: 2px solid #e5e7eb; background: ' . il_generate_gradient_css($gradient_type, $gradient_direction, $gradient_color1, $gradient_color2) . ';"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Los degradados crean fondos más dinámicos y modernos. Ajusta la dirección según tu diseño.', 'imagina-login') . '
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
                ' . ($body_image_id ? esc_html__('Cambiar Imagen', 'imagina-login') : esc_html__('Subir Imagen', 'imagina-login')) . '
            </button>';
            
    if ($body_image_id) {
        echo '<button type="button" class="imagina-upload-btn remove-image-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            ' . esc_html__('Quitar', 'imagina-login') . '
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                ' . esc_html__('Formatos: JPG, PNG, GIF, WebP | Tamaño recomendado: 1920×1080px', 'imagina-login') . '
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($body_image_id) {
        echo wp_get_attachment_image($body_image_id, 'medium', false, ['style' => 'width: 100%; height: auto; border-radius: 8px;']);
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-format-image" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>' . esc_html__('No hay imagen seleccionada', 'imagina-login') . '</p>
        </div>';
    }
    
    echo '</div>
        </div>
    </div>';

    // Presentación de imágenes
    $opts = il_get_all_options();
    $slide_ids = array_filter(explode(',', (string) $opts['il_body_slides']));
    $thumbs = '';
    foreach ($slide_ids as $slide_id) {
        $thumb = wp_get_attachment_image_url((int) $slide_id, 'thumbnail');
        if ($thumb) {
            $thumbs .= '<img src="' . esc_url($thumb) . '" alt="">';
        }
    }
    echo '<div class="imagina-option-panel" data-type="slideshow" data-target="body">
        <div class="imagina-control-group">
            <label class="imagina-control-label">' . esc_html__('Imágenes de la presentación', 'imagina-login') . '</label>
            <input type="hidden" name="il_body_slides" value="' . esc_attr($opts['il_body_slides']) . '">
            <button type="button" class="imagina-upload-btn il-slides-button"><span class="dashicons dashicons-images-alt2"></span> ' . esc_html__('Elegir imágenes', 'imagina-login') . '</button>
            <div class="il-slides-preview">' . ($thumbs ? $thumbs : '<p class="imagina-help">' . esc_html__('Elige 2 o más imágenes. Se alternan con un fundido suave.', 'imagina-login') . '</p>') . '</div>
        </div>
        <div class="imagina-control-group" style="margin-top: 12px;">
            <label class="imagina-control-label">' . esc_html__('Segundos por imagen', 'imagina-login') . '</label>
            <div class="imagina-slider-container">
                <input type="range" name="il_slide_interval" class="imagina-slider" min="3" max="30" step="1" value="' . esc_attr($opts['il_slide_interval']) . '" data-unit="s">
                <span class="imagina-slider-value">' . esc_html($opts['il_slide_interval']) . 's</span>
            </div>
            <p class="imagina-help">' . esc_html__('Si el visitante tiene activado "reducir movimiento" en su sistema, se queda en la primera imagen.', 'imagina-login') . '</p>
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
                ' . ($body_video_id ? esc_html__('Cambiar Video', 'imagina-login') : esc_html__('Subir Video', 'imagina-login')) . '
            </button>';
            
    if ($body_video_id) {
        echo '<button type="button" class="imagina-upload-btn remove-video-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            ' . esc_html__('Quitar', 'imagina-login') . '
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                ' . esc_html__('Formatos: MP4, WebM, OGG | Tamaño máximo: 50MB', 'imagina-login') . '
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($body_video_id) {
        $video_url = wp_get_attachment_url($body_video_id);
        echo '<video controls style="width: 100%; height: auto; border-radius: 8px;">
            <source src="' . esc_url($video_url) . '" type="video/mp4">
            ' . esc_html__('Tu navegador no soporta videos HTML5.', 'imagina-login') . '
        </video>';
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-video-alt3" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>' . esc_html__('No hay video seleccionado', 'imagina-login') . '</p>
        </div>';
    }
    
    $video_ext_url = get_option('il_body_video_url', '');
    $video_ext_info = il_parse_video_url($video_ext_url);
    if ($video_ext_url && $video_ext_info) {
        $video_ext_status = '<span style="color: #059669; font-weight: 600;">✓ ' . esc_html(sprintf(/* translators: %s: video service name (YouTube, Vimeo or video file) */ __('Detectado: %s. Se usará en lugar del video subido.', 'imagina-login'), $video_ext_info['label'])) . '</span>';
    } elseif ($video_ext_url) {
        $video_ext_status = '<span style="color: #dc2626; font-weight: 600;">✗ ' . esc_html__('Enlace no reconocido. Usa un link de YouTube, Vimeo o un archivo .mp4/.webm.', 'imagina-login') . '</span>';
    } else {
        $video_ext_status = esc_html__('Pega un enlace de YouTube, Vimeo o un archivo directo (.mp4, .webm). Si lo llenas, tiene prioridad sobre el video subido.', 'imagina-login');
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
            <label class="imagina-control-label">' . esc_html__('Overlay del video (para mejorar legibilidad)', 'imagina-login') . '</label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="text" name="il_video_overlay_color" value="' . esc_attr($video_overlay) . '" 
                       placeholder="rgba(0,0,0,0.3)" 
                       style="flex: 1; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                <div style="width: 40px; height: 40px; background: ' . esc_attr($video_overlay) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
            </div>
            <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 12px;">
                ' . esc_html__('Usa formato rgba() para controlar transparencia. Ejemplo: rgba(0,0,0,0.5) = negro 50% transparente', 'imagina-login') . '
            </p>
        </div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Los videos se reproducen automáticamente sin sonido. Recomendamos videos cortos (10-30 segundos) en loop.', 'imagina-login') . '
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
                <span class="imagina-color-label">' . esc_html__('Color de fondo del logo', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($logo_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($logo_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Elige un color que contraste bien con tu logo para que sea fácil de ver.', 'imagina-login') . '
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
                <label class="imagina-control-label">' . esc_html__('Tipo de degradado', 'imagina-login') . '</label>
                <select name="il_logo_gradient_type" class="imagina-select">
                    <option value="linear"' . selected($logo_gradient_type, 'linear', false) . '>🔄 ' . esc_html__('Lineal', 'imagina-login') . '</option>
                    <option value="radial"' . selected($logo_gradient_type, 'radial', false) . '>⭕ ' . esc_html__('Radial', 'imagina-login') . '</option>
                </select>
            </div>
            
            <div class="imagina-control-group">
                <label class="imagina-control-label">' . esc_html__('Dirección', 'imagina-login') . '</label>
                <select name="il_logo_gradient_direction" class="imagina-select">
                    <option value="vertical"' . selected($logo_gradient_direction, 'vertical', false) . '>⬇️ ' . esc_html__('Vertical', 'imagina-login') . '</option>
                    <option value="horizontal"' . selected($logo_gradient_direction, 'horizontal', false) . '>➡️ ' . esc_html__('Horizontal', 'imagina-login') . '</option>
                    <option value="diagonal1"' . selected($logo_gradient_direction, 'diagonal1', false) . '>↗️ ' . esc_html__('Diagonal ↗', 'imagina-login') . '</option>
                    <option value="diagonal2"' . selected($logo_gradient_direction, 'diagonal2', false) . '>↙️ ' . esc_html__('Diagonal ↙', 'imagina-login') . '</option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="imagina-color-picker">
                <input type="color" name="il_logo_gradient_color1" value="' . esc_attr($logo_gradient_color1) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">' . esc_html__('Color inicial', 'imagina-login') . '</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($logo_gradient_color1)) . '</span>
                </div>
            </div>
            
            <div class="imagina-color-picker">
                <input type="color" name="il_logo_gradient_color2" value="' . esc_attr($logo_gradient_color2) . '" class="imagina-color-input">
                <div class="imagina-color-info">
                    <span class="imagina-color-label">' . esc_html__('Color final', 'imagina-login') . '</span>
                    <span class="imagina-color-value">' . esc_html(strtoupper($logo_gradient_color2)) . '</span>
                </div>
            </div>
        </div>
        
        <div class="imagina-gradient-preview" style="margin-top: 20px; height: 60px; border-radius: 8px; border: 2px solid #e5e7eb; background: ' . il_generate_gradient_css($logo_gradient_type, $logo_gradient_direction, $logo_gradient_color1, $logo_gradient_color2) . ';"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Los degradados del logo crean efectos visuales únicos que complementan tu diseño.', 'imagina-login') . '
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
                ' . ($logo_image_id ? esc_html__('Cambiar Imagen', 'imagina-login') : esc_html__('Subir Imagen', 'imagina-login')) . '
            </button>';
            
    if ($logo_image_id) {
        echo '<button type="button" class="imagina-upload-btn remove-image-button" style="background: #ef4444; margin-left: 10px;">
            <span class="dashicons dashicons-trash"></span>
            ' . esc_html__('Quitar', 'imagina-login') . '
        </button>';
    }
    
    echo '<p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                ' . esc_html__('Para el área del logo, recomendamos imágenes cuadradas (ej: 500×500px)', 'imagina-login') . '
            </p>
            
            <div class="imagina-media-preview">';
            
    if ($logo_image_id) {
        echo wp_get_attachment_image($logo_image_id, 'medium', false, ['style' => 'width: 100%; height: auto; border-radius: 8px;']);
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #9ca3af; border: 2px dashed #d1d5db; border-radius: 8px; margin-top: 15px;">
            <span class="dashicons dashicons-format-image" style="font-size: 32px; margin-bottom: 10px; display: block;"></span>
            <p>' . esc_html__('No hay imagen seleccionada', 'imagina-login') . '</p>
        </div>';
    }
    
    echo '</div>
        </div>
        
        <div class="imagina-control-group" style="margin-top: 20px;">
            <label class="imagina-control-label">
                <span class="dashicons dashicons-visibility"></span>
                ' . esc_html__('Overlay para mejorar visibilidad del logo', 'imagina-login') . '
            </label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="text" name="il_logo_overlay_color" value="' . esc_attr($logo_overlay) . '" 
                       placeholder="rgba(0,0,0,0.2)" 
                       style="flex: 1; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                <div style="width: 40px; height: 40px; background: ' . esc_attr($logo_overlay !== 'transparent' ? $logo_overlay : '#f0f0f0') . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
            </div>
            <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 12px;">
                ' . esc_html__('Agrega una capa semitransparente sobre la imagen para que tu logo sea más visible. Usa "transparent" para quitar el overlay.', 'imagina-login') . '
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

function il_field_image($key, $label, $empty_text, $help = '') {
    $opts = il_get_all_options();
    $id = $opts[$key];
    $url = $id ? wp_get_attachment_image_url((int) $id, 'medium') : '';
    $preview = $url
        ? '<img src="' . esc_url($url) . '" alt="" style="max-width:100%;max-height:120px;border-radius:6px;display:block;">'
        : '<div class="imagina-media-empty"><span class="dashicons dashicons-format-image"></span><p>' . esc_html($empty_text) . '</p></div>';
    printf(
        '<div class="imagina-control-group"><label class="imagina-control-label">%1$s</label><div class="imagina-media-uploader">'
        . '<input type="hidden" name="%2$s" value="%3$s">'
        . '<button type="button" class="imagina-upload-btn upload-image-button"><span class="dashicons dashicons-upload"></span> %4$s</button> '
        . '<button type="button" class="imagina-upload-btn remove-image-button" style="background:#ef4444;%5$s"><span class="dashicons dashicons-trash"></span> %6$s</button>'
        . '<div class="imagina-media-preview">%7$s</div>%8$s</div></div>',
        esc_html($label),
        esc_attr($key),
        esc_attr($id),
        esc_html($id ? __('Cambiar', 'imagina-login') : __('Subir', 'imagina-login')),
        $id ? '' : 'display:none;',
        esc_html__('Quitar', 'imagina-login'),
        $preview,
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
                <span class="imagina-color-label">' . esc_html__('Color de etiquetas', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($label_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($label_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_button_color" value="' . esc_attr($button_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">' . esc_html__('Color del botón', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($button_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($button_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_button_hover_color" value="' . esc_attr($button_hover_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">' . esc_html__('Color botón (hover)', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($button_hover_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($button_hover_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
        
        <div class="imagina-color-picker">
            <input type="color" name="il_link_color" value="' . esc_attr($link_color) . '" class="imagina-color-input">
            <div class="imagina-color-info">
                <span class="imagina-color-label">' . esc_html__('Color de enlaces', 'imagina-login') . '</span>
                <span class="imagina-color-value">' . esc_html(strtoupper($link_color)) . '</span>
            </div>
            <div class="imagina-color-preview" style="width: 40px; height: 40px; background: ' . esc_attr($link_color) . '; border-radius: 8px; border: 2px solid #e5e7eb;"></div>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #e8f4fd; border-radius: 8px; border-left: 4px solid #0ea5e9;">
        <h4 style="margin: 0 0 10px 0; color: #0c4a6e; font-size: 16px;">
            <span class="dashicons dashicons-info"></span>
            ' . esc_html__('Vista previa de colores', 'imagina-login') . '
        </h4>
        <div style="display: grid; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span data-il-sample="il_label_color:color" style="color: ' . esc_attr($label_color) . '; font-weight: 600;">' . esc_html__('Etiqueta de ejemplo', 'imagina-login') . '</span>
                <span style="font-size: 12px; color: #64748b;">← ' . esc_html__('Así se verán las etiquetas', 'imagina-login') . '</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" data-il-sample="il_button_color:background" style="background: ' . esc_attr($button_color) . '; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600;">' . esc_html__('Botón de ejemplo', 'imagina-login') . '</button>
                <span style="font-size: 12px; color: #64748b;">← ' . esc_html__('Color normal del botón', 'imagina-login') . '</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" data-il-sample="il_button_hover_color:background" style="background: ' . esc_attr($button_hover_color) . '; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600;">' . esc_html__('Botón hover', 'imagina-login') . '</button>
                <span style="font-size: 12px; color: #64748b;">← ' . esc_html__('Color al pasar el mouse', 'imagina-login') . '</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="#" data-il-sample="il_link_color:color" style="color: ' . esc_attr($link_color) . '; text-decoration: none; font-weight: 500;">' . esc_html__('Enlace de ejemplo', 'imagina-login') . '</a>
                <span style="font-size: 12px; color: #64748b;">← ' . esc_html__('Color de los enlaces', 'imagina-login') . '</span>
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
            <label class="imagina-control-label">' . esc_html__('Tipo de transición', 'imagina-login') . '</label>
            <select name="il_transition_type" class="imagina-select" onchange="updateTransitionPreview()">
                <option value="fade"' . selected($transition_type, 'fade', false) . '>' . esc_html__('Aparición gradual (Fade)', 'imagina-login') . '</option>
                <option value="slidedown"' . selected($transition_type, 'slidedown', false) . '>' . esc_html__('Deslizar desde arriba', 'imagina-login') . '</option>
                <option value="zoom"' . selected($transition_type, 'zoom', false) . '>' . esc_html__('Zoom suave', 'imagina-login') . '</option>
            </select>
        </div>
    </div>

    <div style="margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div style="padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 10px 0; color: #374151; font-size: 13px; font-weight: 700;">' . esc_html__('Logo', 'imagina-login') . '</h4>
            <div class="imagina-control-group" style="margin-bottom: 10px;">
                <label class="imagina-control-label">' . esc_html__('Velocidad', 'imagina-login') . '</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_logo_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($logo_duration) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($logo_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">' . esc_html__('Delay', 'imagina-login') . '</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_logo_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($logo_delay) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($logo_delay) . 's</span>
                </div>
            </div>
        </div>

        <div style="padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 10px 0; color: #374151; font-size: 13px; font-weight: 700;">' . esc_html__('Formulario', 'imagina-login') . '</h4>
            <div class="imagina-control-group" style="margin-bottom: 10px;">
                <label class="imagina-control-label">' . esc_html__('Velocidad', 'imagina-login') . '</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_duration" class="imagina-slider" min="0.1" max="1.5" step="0.05" value="' . esc_attr($form_duration) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($form_duration) . 's</span>
                </div>
            </div>
            <div class="imagina-control-group">
                <label class="imagina-control-label">' . esc_html__('Delay', 'imagina-login') . '</label>
                <div class="imagina-slider-container">
                    <input type="range" name="il_form_transition_delay" class="imagina-slider" min="0" max="1.0" step="0.05" value="' . esc_attr($form_delay) . '" data-unit="s">
                    <span class="imagina-slider-value">' . esc_html($form_delay) . 's</span>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 14px; padding: 12px; background: #f3f4f6; border-radius: 8px; border: 1px dashed #d1d5db;">
        <h4 style="margin: 0 0 8px 0; color: #374151; font-size: 13px;">' . esc_html__('Secuencia de animación', 'imagina-login') . '</h4>
        <div style="display: flex; gap: 8px; font-size: 12px; color: #6b7280; flex-wrap: wrap;">
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #667eea;">' . esc_html__('Fondo: inmediato', 'imagina-login') . '</span>
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #10b981;" data-il-seq="logo"></span>
            <span style="padding: 4px 10px; background: white; border-radius: 4px; border-left: 3px solid #f59e0b;" data-il-seq="form"></span>
        </div>

        <div id="transition-preview" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 10px;">
            <div class="transition-demo" data-type="fade">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: fadeDemo 3s infinite;">' . esc_html__('Fade', 'imagina-login') . '</div>
            </div>
            <div class="transition-demo" data-type="slidedown">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #10b981, #059669); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: slideDemo 3s infinite;">' . esc_html__('Slide', 'imagina-login') . '</div>
            </div>
            <div class="transition-demo" data-type="zoom">
                <div style="width: 100%; height: 40px; background: linear-gradient(45deg, #f59e0b, #d97706); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 11px; animation: zoomDemo 3s infinite;">' . esc_html__('Zoom', 'imagina-login') . '</div>
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
        'previewNonce' => wp_create_nonce('il_preview_store'),
        'presets' => array_map(function ($preset) { return $preset['settings']; }, il_presets()),
        'i18n' => [
            'loading' => __('Cargando...', 'imagina-login'),
            'uploading' => __('Subiendo...', 'imagina-login'),
            'selectImage' => __('Seleccionar imagen', 'imagina-login'),
            'useImage' => __('Usar esta imagen', 'imagina-login'),
            'imageAlt' => __('Imagen de fondo', 'imagina-login'),
            'changeImage' => __('Cambiar Imagen', 'imagina-login'),
            'uploadImage' => __('Subir Imagen', 'imagina-login'),
            'imageUploaded' => __('Imagen subida correctamente', 'imagina-login'),
            'imageRemoved' => __('Imagen eliminada', 'imagina-login'),
            'confirmRemoveImage' => __('¿Estás seguro de que quieres quitar esta imagen?', 'imagina-login'),
            'noImage' => __('No hay imagen seleccionada', 'imagina-login'),
            'selectVideo' => __('Seleccionar video de fondo', 'imagina-login'),
            'useVideo' => __('Usar este video', 'imagina-login'),
            'changeVideo' => __('Cambiar Video', 'imagina-login'),
            'uploadVideo' => __('Subir Video', 'imagina-login'),
            'videoUploaded' => __('Video subido correctamente', 'imagina-login'),
            'videoRemoved' => __('Video eliminado', 'imagina-login'),
            'confirmRemoveVideo' => __('¿Estás seguro de que quieres quitar este video?', 'imagina-login'),
            'noVideo' => __('No hay video seleccionado', 'imagina-login'),
            'videoTooBig' => __('El video es demasiado grande. Máximo permitido: 50MB', 'imagina-login'),
            'videoNoSupport' => __('Tu navegador no soporta videos HTML5.', 'imagina-login'),
            'size' => __('Tamaño', 'imagina-login'),
            'videoAutoplay' => __('El video se reproducirá automáticamente sin sonido en el login', 'imagina-login'),
            'confirmReset' => __('¿Estás seguro de que quieres restaurar todas las configuraciones por defecto? Esta acción no se puede deshacer.', 'imagina-login'),
            'slidesTitle' => __('Imágenes de la presentación', 'imagina-login'),
            'slidesButton' => __('Usar estas imágenes', 'imagina-login'),
            'logo' => __('Logo', 'imagina-login'),
            'form' => __('Formulario', 'imagina-login'),
            'updating' => __('Actualizando…', 'imagina-login'),
            'updated' => __('Actualizado', 'imagina-login'),
            'updateFailed' => __('No se pudo actualizar', 'imagina-login'),
        ],
        'presetApplied' => __('Diseño aplicado. Revísalo en la vista previa y pulsa "Guardar Cambios" para conservarlo.', 'imagina-login'),
    ]);
}
add_action('admin_enqueue_scripts', 'il_enqueue_admin_scripts');