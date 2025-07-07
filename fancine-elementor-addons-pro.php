<?php
/**
 * Plugin Name:     Fancine Elementor Addons Pro
 * Plugin URI:      https://tu-sitio.com/fancine-elementor
 * Description:     Conjunto profesional de widgets y herramientas para Elementor
 * Version:         1.0.0
 * Author:          Tu Nombre
 * Text Domain:     fancine-elementor
 * Domain Path:     /languages
 */

defined( 'ABSPATH' ) || exit;

// --------------------------------------------------
// Constantes
// --------------------------------------------------
define( 'FANCINE_PRO_VERSION', '1.0.0' );
define( 'FANCINE_PRO_PATH',    plugin_dir_path( __FILE__ ) );
define( 'FANCINE_PRO_URL',     plugin_dir_url( __FILE__ ) );
define( 'FANCINE_PRO_FILE',    __FILE__ );

// --------------------------------------------------
// Autoloader
// --------------------------------------------------
require_once FANCINE_PRO_PATH . 'core/class-autoloader.php';

// --------------------------------------------------
// Prueba de Autoloader (solo en DEBUG)
// --------------------------------------------------
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( '🟢 Fancine_Module_Manager: '   . ( class_exists( 'Fancine_Module_Manager' )   ? 'OK' : 'FAIL' ) );
    error_log( '🟢 Fancine_Dynamic_Engine: '   . ( class_exists( 'Fancine_Dynamic_Engine' )   ? 'OK' : 'FAIL' ) );
    error_log( '🟢 Fancine_API_Interface: '    . ( class_exists( 'Fancine_API_Interface' )    ? 'OK' : 'FAIL' ) );
    error_log( '🟢 Fancine_Skin_Validator: '  . ( class_exists( 'Fancine_Skin_Validator' )  ? 'OK' : 'FAIL' ) );
    error_log( '🟢 Fancine_Template_Cache: '  . ( class_exists( 'Fancine_Template_Cache' )  ? 'OK' : 'FAIL' ) );
    error_log( '🟢 Fancine_Template_Engine: ' . ( class_exists( 'Fancine_Template_Engine' ) ? 'OK' : 'FAIL' ) );
}

// --------------------------------------------------
// Encolado de assets en el frontend
// --------------------------------------------------
add_action( 'wp_enqueue_scripts', 'fancine_enqueue_assets' );
function fancine_enqueue_assets() {
    wp_enqueue_style(  'fancine-global', FANCINE_PRO_URL . 'assets/css/global.css', [], FANCINE_PRO_VERSION );
    wp_enqueue_script( 'fancine-global', FANCINE_PRO_URL . 'assets/js/global.js', [ 'jquery' ], FANCINE_PRO_VERSION, true );
}

// --------------------------------------------------
// Encolado de assets en el admin
// --------------------------------------------------
add_action( 'admin_enqueue_scripts', 'fancine_admin_enqueue_assets' );
function fancine_admin_enqueue_assets() {
    wp_enqueue_style( 'fancine-admin', FANCINE_PRO_URL . 'assets/css/admin.css', [], FANCINE_PRO_VERSION );
}

// --------------------------------------------------
// Inicialización del plugin
// --------------------------------------------------
add_action( 'plugins_loaded', 'fancine_init_plugin' );
function fancine_init_plugin() {
    // 1. Internacionalización
    load_plugin_textdomain(
        'fancine-elementor',
        false,
        dirname( plugin_basename( FANCINE_PRO_FILE ) ) . '/languages/'
    );

    // 2. Punto central de gestión de módulos
    $manager      = Fancine_Module_Manager::instance();
    $all_modules  = [
        'blog-posts',
        'basic-cards',
        'wysiwyg-widgets',
        'dynamic-content',
    ];

    // 2.1. Registramos todos los módulos disponibles
    foreach ( $all_modules as $slug ) {
        $manager->register_module( $slug, [
            'path' => FANCINE_PRO_PATH . "modules/{$slug}",
            'url'  => FANCINE_PRO_URL  . "modules/{$slug}",
        ] );
    }

    // 3. Registrar endpoints de la REST API
    $api = new Fancine_API_Interface();
    $api->register_endpoints();

    // 4. Cargar únicamente los módulos activos
    foreach ( $manager->get_modules() as $slug => $config ) {
        $module_file = $config['path'] . '/module.php';
        if ( file_exists( $module_file ) ) {
            require_once $module_file;
        }
    }

    // 5. Validación de skins antes de guardar
    $validator = new Fancine_Skin_Validator();
    add_filter( 'fancine_skin_before_save', function( $code ) use ( $validator ) {
        if ( ! $validator->validate_php_code( $code ) ) {
            wp_die( __( 'Código de skin no válido.', 'fancine-elementor' ) );
        }
        return $code;
    } );

    // 6. Inicializar motor dinámico
    Fancine_Dynamic_Engine::init();
}

// --------------------------------------------------
// Registro de widgets de Elementor
// --------------------------------------------------
add_action( 'elementor/widgets/register', function( $widgets_manager ) {
    $modules = Fancine_Module_Manager::instance()->get_modules();
    foreach ( $modules as $config ) {
        $widgets_file = $config['path'] . '/widgets.php';
        if ( file_exists( $widgets_file ) ) {
            require_once $widgets_file;
        }
    }
} );

// --------------------------------------------------
// Registro de skins de Elementor
// --------------------------------------------------
add_action( 'elementor/skins/register', function( $widget ) {
    $modules = Fancine_Module_Manager::instance()->get_modules();
    foreach ( $modules as $config ) {
        $skins_file = $config['path'] . '/skins.php';
        if ( file_exists( $skins_file ) ) {
            require_once $skins_file;
        }
    }
} );

// --------------------------------------------------
// Carga del admin panel
// --------------------------------------------------
if ( is_admin() ) {
    require_once FANCINE_PRO_PATH . 'admin/class-admin-core.php';
    new Fancine_Admin_Core();
}
// ——— TEST MÓDULOS ACTIVOS EN ADMIN ———
add_action( 'admin_notices', function() {
    $modules = Fancine_Module_Manager::instance()->get_modules();
    $list    = ! empty( $modules ) ? implode( ', ', array_keys( $modules ) ) : '— ninguno —';
    echo '<div class="notice notice-info is-dismissible"><p>';
    echo '<strong>Fancine</strong> módulos activos: ' . esc_html( $list );
    echo '</p></div>';
}, 100 );
