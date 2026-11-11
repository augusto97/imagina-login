<?php
/**
 * Limpieza al desinstalar Imagina Login: borra sus ajustes y la caché de estilos.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Ajustes del plugin (todos usan el prefijo il_)
$options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'il\\_%'");
foreach ($options as $option) {
    delete_option($option);
}

// Caché de estilos generada (clave actual y claves de versiones anteriores)
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '\\_transient\\_il\\_dyn\\_styles\\_%'
        OR option_name LIKE '\\_transient\\_timeout\\_il\\_dyn\\_styles\\_%'
        OR option_name LIKE '\\_transient\\_imagina\\_login\\_dynamic\\_styles\\_%'
        OR option_name LIKE '\\_transient\\_timeout\\_imagina\\_login\\_dynamic\\_styles\\_%'"
);
