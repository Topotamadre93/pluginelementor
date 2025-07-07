<?php
// Registrar widget de tarjetas
add_action('elementor/widgets/register', 'fancine_register_basic_cards_widget');
function fancine_register_basic_cards_widget($widgets_manager) {
    // Verificar si el archivo existe antes de incluirlo
    $widget_file = __DIR__ . '/class-basic-cards-widget.php';
    
    if (file_exists($widget_file)) {
        require_once $widget_file;
        $widgets_manager->register(new Fancine_Basic_Cards_Widget());
    } else {
        // Registrar error solo en modo depuración
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Fancine Basic Cards Widget file not found: ' . $widget_file);
        }
    }
}