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

// Fuentes descargadas en uploads/imagina-login
$uploads = wp_upload_dir();
$fonts_dir = trailingslashit($uploads['basedir']) . 'imagina-login';
if (is_dir($fonts_dir)) {
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fonts_dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($fonts_dir);
}
