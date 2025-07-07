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

defined('ABSPATH') || exit;

// Definir constantes
define('FANCINE_PRO_VERSION', '1.0.0');
define('FANCINE_PRO_PATH', plugin_dir_path(__FILE__));
define('FANCINE_PRO_URL', plugin_dir_url(__FILE__));
define('FANCINE_PRO_FILE', __FILE__);

// Cargar núcleo del plugin
require_once FANCINE_PRO_PATH . 'core/class-module-manager.php';
require_once FANCINE_PRO_PATH . 'core/class-dynamic-engine.php';
require_once FANCINE_PRO_PATH . 'core/class-template-engine.php';

// Inicializar módulos
add_action('plugins_loaded', 'fancine_init_plugin');
function fancine_init_plugin() {
    // Carga de módulos principales
    $modules = [
        'blog-posts',
        'basic-cards',
        'wysiwyg-widgets',
        'dynamic-content'
    ];
    
    foreach ($modules as $module) {
        $file = FANCINE_PRO_PATH . "modules/{$module}/module.php";
        if (file_exists($file)) require_once $file;
    }
    
    // Cargar admin si es necesario
    if (is_admin()) {
        require_once FANCINE_PRO_PATH . 'admin/class-admin-core.php';
        new Fancine_Admin_Core();
    }
    
    // Cargar internacionalización
    load_plugin_textdomain(
        'fancine-elementor',
        false,
        dirname(plugin_basename(FANCINE_PRO_FILE)) . '/languages/'
    );
    
    // Inicializar motor dinámico
    Fancine_Dynamic_Engine::init();
}