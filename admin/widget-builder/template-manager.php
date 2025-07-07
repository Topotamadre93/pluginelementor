<?php
class Fancine_Template_Manager {
    public function save_template($template_data) {
        // Validar y guardar plantilla
        $validator = new Fancine_Skin_Validator();
        
        if ($validator->validate_template($template_data)) {
            // Guardar en base de datos
            return true;
        }
        return false;
    }
    
    public function get_templates() {
        return get_option('fancine_widget_templates', []);
    }
}