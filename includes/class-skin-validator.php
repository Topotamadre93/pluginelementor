<?php
class Fancine_Skin_Validator {
    public function validate_php_code($code) {
        // 1. Comprobar funciones peligrosas
        if (preg_match('/(system|exec|shell_exec|eval|base64_decode)/i', $code)) {
            return false;
        }
        
        // 2. Validar sintaxis PHP
        return $this->check_php_syntax($code);
    }
    
    private function check_php_syntax($code) {
        // Implementar validación de sintaxis
        return true; // Temporal para desarrollo
    }
    
    public function sanitize_css($css) {
        // Eliminar expresiones peligrosas
        return preg_replace('/expression\(|javascript\:/i', '', $css);
    }
    
    public function validate_template($template_data) {
        // Validar estructura de plantilla
        return isset($template_data['name']) && isset($template_data['structure']);
    }
}