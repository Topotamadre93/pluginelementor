<?php
/**
 * Plugin Name: Fancine Elementor Addons Pro
 * Plugin URI: https://tu-sitio.com/fancine-elementor
 * Description: Conjunto profesional de widgets y herramientas para Elementor
 * Version: 1.0.0
 * Author: Tu Nombre
 * Text Domain: fancine-elementor
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

// Definir constantes
define( 'FANCINE_PRO_VERSION', '1.0.0' );
define( 'FANCINE_PRO_PATH',    plugin_dir_path( __FILE__ ) );
define( 'FANCINE_PRO_URL',     plugin_dir_url( __FILE__ ) );
define( 'FANCINE_PRO_FILE',    __FILE__ );

// Cargar núcleo del plugin
require_once FANCINE_PRO_PATH . 'core/class-module-manager.php';   // Gestor de módulos :contentReference[oaicite:0]{index=0}
require_once FANCINE_PRO_PATH . 'core/class-dynamic-engine.php';  // Motor de contenido dinámico :contentReference[oaicite:1]{index=1}
require_once FANCINE_PRO_PATH . 'core/class-template-engine.php'; // Motor de plantillas :contentReference[oaicite:2]{index=2}
require_once FANCINE_PRO_PATH . 'core/class-template-cache.php';  // Caché de plantillas :contentReference[oaicite:3]{index=3}
require_once FANCINE_PRO_PATH . 'core/class-skin-validator.php';  // Validador de skins :contentReference[oaicite:4]{index=4}
require_once FANCINE_PRO_PATH . 'core/class-api-interface.php';   // Interfaz REST API :contentReference[oaicite:5]{index=5}

// Encolar assets globales
add_action( 'wp_enqueue_scripts', 'fancine_enqueue_assets' );
function fancine_enqueue_assets() {
    wp_enqueue_style(  'fancine-global', FANCINE_PRO_URL . 'assets/css/global.css', [], FANCINE_PRO_VERSION );
    wp_enqueue_script( 'fancine-global', FANCINE_PRO_URL . 'assets/js/global.js', ['jquery'], FANCINE_PRO_VERSION, true );
}

// Encolar assets en el admin
add_action( 'admin_enqueue_scripts', 'fancine_admin_enqueue_assets' );
function fancine_admin_enqueue_assets() {
    wp_enqueue_style( 'fancine-admin', FANCINE_PRO_URL . 'assets/css/admin.css', [], FANCINE_PRO_VERSION );
}

// Hook principal de inicialización
add_action( 'plugins_loaded', 'fancine_init_plugin' );
function fancine_init_plugin() {
    // 1. Registrar endpoints de la API REST
    $api = new Fancine_API_Interface();
    $api->register_endpoints();

    // 2. Registrar y cargar módulos
    $modules = [
        'blog-posts',
        'basic-cards',
        'wysiwyg-widgets',
        'dynamic-content',
    ];
    $manager = Fancine_Module_Manager::instance();
    foreach ( $modules as $slug ) {
        $manager->register_module( $slug, [
            'path' => FANCINE_PRO_PATH . "modules/{$slug}",
            'url'  => FANCINE_PRO_URL  . "modules/{$slug}",
        ] );
        $file = FANCINE_PRO_PATH . "modules/{$slug}/module.php";
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    // 3. Validación de skins antes de guardar
    $validator = new Fancine_Skin_Validator();
    add_filter( 'fancine_skin_before_save', function( $code ) use ( $validator ) {
        if ( ! $validator->validate_php_code( $code ) ) {
            wp_die( __( 'Código de skin no válido.', 'fancine-elementor' ) );
        }
        return $code;
    } );

    // 4. Inicializar internacionalización
    load_plugin_textdomain(
        'fancine-elementor',
        false,
        dirname( plugin_basename( FANCINE_PRO_FILE ) ) . '/languages/'
    );

    // 5. Inicializar motor dinámico
    Fancine_Dynamic_Engine::init();
}

// Registrar widgets de Elementor de todos los módulos
add_action( 'elementor/widgets/register', function( $widgets_manager ) {
    $modules = Fancine_Module_Manager::instance()->get_modules();
    foreach ( $modules as $config ) {
        $widgets_file = $config['path'] . '/widgets.php';
        if ( file_exists( $widgets_file ) ) {
            require_once $widgets_file;
        }
    }
} );

// Registrar skins de Elementor de todos los módulos
add_action( 'elementor/skins/register', function( $widget ) {
    $modules = Fancine_Module_Manager::instance()->get_modules();
    foreach ( $modules as $config ) {
        $skins_file = $config['path'] . '/skins.php';
        if ( file_exists( $skins_file ) ) {
            require_once $skins_file;
        }
    }
} );

// Cargar admin si es necesario
if ( is_admin() ) {
    require_once FANCINE_PRO_PATH . 'admin/class-admin-core.php';
    new Fancine_Admin_Core();
}
