<?php
/**
 * Class Autoloader for Fancine Elementor Addons Pro
 * 
 * Registra las clases con prefijo Fancine_ y las carga
 * sustituyendo "_" por directorios y buscando .php.
 */

if ( ! defined( 'FANCINE_PRO_PATH' ) ) {
    define( 'FANCINE_PRO_PATH', plugin_dir_path( __FILE__ ) . '..' );
}

spl_autoload_register( function ( $class ) {
    // Prefijo de nuestras clases
    $prefix = 'Fancine_';
    $len    = strlen( $prefix );

    // Si la clase no empieza por nuestro prefijo, ignoramos
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    // Nombre relativo de la clase (sin prefijo)
    $relative_class = substr( $class, $len );

    // Convertimos "_" en separador de directorio
    $relative_path = str_replace( '_', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

    // Rutas base donde buscar:
    $base_dirs = [
        FANCINE_PRO_PATH . 'core/',
        FANCINE_PRO_PATH . 'admin/',
        FANCINE_PRO_PATH . 'modules/',
        FANCINE_PRO_PATH . 'dynamic-engine/',
        FANCINE_PRO_PATH . 'templates/',
    ];

    // Intentamos cargar desde cada ruta base
    foreach ( $base_dirs as $base_dir ) {
        $file = $base_dir . $relative_path;
        if ( file_exists( $file ) ) {
            require_once $file;
            return;
        }
    }
} );
