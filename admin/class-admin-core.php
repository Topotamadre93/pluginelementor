<?php
class Fancine_Admin_Core {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Fancine Elementor', 'fancine-elementor'),
            __('Fancine', 'fancine-elementor'),
            'manage_options',
            'fancine-elementor',
            [$this, 'render_dashboard'],
            'dashicons-admin-appearance',
            58
        );
        
        // Submenús
        add_submenu_page(
            'fancine-elementor',
            __('Constructor de Widgets', 'fancine-elementor'),
            __('Constructor', 'fancine-elementor'),
            'manage_options',
            'fancine-widget-builder',
            [$this, 'render_widget_builder']
        );
        
        add_submenu_page(
            'fancine-elementor',
            __('Documentación CSS', 'fancine-elementor'),
            __('CSS Docs', 'fancine-elementor'),
            'manage_options',
            'fancine-css-docs',
            [$this, 'render_css_docs']
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'fancine-') === false) return;
        
        wp_enqueue_style(
            'fancine-admin-css',
            FANCINE_PRO_URL . 'assets/css/admin.css',
            [],
            FANCINE_PRO_VERSION
        );
        
        wp_enqueue_script(
            'fancine-admin-js',
            FANCINE_PRO_URL . 'assets/js/admin.js',
            ['jquery'],
            FANCINE_PRO_VERSION,
            true
        );
    }
    
    public function render_dashboard() {
        include FANCINE_PRO_PATH . 'admin/views/dashboard.php';
    }
    
    public function render_widget_builder() {
        include FANCINE_PRO_PATH . 'admin/views/widget-builder.php';
    }
    
    public function render_css_docs() {
        include FANCINE_PRO_PATH . 'admin/views/css-docs.php';
    }
}