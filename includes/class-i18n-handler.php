<?php
class Fancine_i18n_Handler {
    public function load_textdomain() {
        load_plugin_textdomain(
            'fancine-elementor',
            false,
            dirname(plugin_basename(FANCINE_PRO_FILE)) . '/languages/'
        );
    }
    
    public function get_dynamic_translations() {
        return [
            'copy_css' => __('Copiar CSS', 'fancine-elementor'),
            'selector_docs' => __('Documentación del Selector', 'fancine-elementor'),
            'dynamic_tag' => __('Etiqueta Dinámica', 'fancine-elementor'),
            'create_widget' => __('Crear Widget', 'fancine-elementor')
        ];
    }
}