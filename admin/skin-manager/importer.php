<?php
class Fancine_Skin_Importer {
    public function import_skin($file) {
        // Validar y procesar archivo de skin
        $validator = new Fancine_Skin_Validator();
        
        $skin_data = file_get_contents($file['tmp_name']);
        if ($validator->validate_skin_data($skin_data)) {
            // Guardar skin
            return true;
        }
        return false;
    }
    
    public function export_skin($skin_slug) {
        // Generar archivo de exportación
        $skin_data = get_option("fancine_skin_{$skin_slug}");
        header('Content-Disposition: attachment; filename="' . $skin_slug . '.json"');
        echo json_encode($skin_data);
        exit;
    }
}