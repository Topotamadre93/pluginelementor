<?php
class Fancine_CSS_Documenter {
    public function generate_reference($skin_slug) {
        $generator = new Fancine_CSS_Reference_Generator();
        return $generator->generate($skin_slug);
    }
    
    public function render_documentation($skin) {
        $data = $this->generate_reference($skin);
        include FANCINE_PRO_PATH . 'admin/views/css-documentation.php';
    }
}