<?php
class Fancine_Skin_Editor {
    public function render_editor() {
        include FANCINE_PRO_PATH . 'admin/views/skin-editor.php';
    }
    
    public function save_skin($skin_data) {
        // Validar y guardar datos de skin
        $validator = new Fancine_Skin_Validator();
        
        if ($validator->validate_skin($skin_data)) {
            // Guardar en base de datos
            return true;
        }
        return false;
    }
}