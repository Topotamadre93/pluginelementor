<?php
class Fancine_CSS_Reference_Generator {
    public function generate($skin_slug) {
        $css = $this->get_skin_css($skin_slug);
        return [
            'selectors' => $this->parse_selectors($css),
            'variables' => $this->extract_css_variables($css),
            'media_queries' => $this->extract_media_queries($css)
        ];
    }
    
    private function get_skin_css($skin_slug) {
        $file = FANCINE_PRO_PATH . "skins/{$skin_slug}/style.css";
        return file_exists($file) ? file_get_contents($file) : '';
    }
    
    private function parse_selectors($css) {
        $result = [];
        preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $matches);
        
        foreach ($matches[1] as $i => $selector) {
            $clean_selector = trim($selector);
            $result[$clean_selector] = [
                'properties' => $this->parse_properties($matches[2][$i]),
                'description' => $this->get_selector_description($clean_selector)
            ];
        }
        return $result;
    }
    
    private function parse_properties($css_block) {
        $properties = [];
        preg_match_all('/([a-zA-Z-]+):\s*([^;]+);/', $css_block, $matches);
        foreach ($matches[1] as $i => $name) {
            $properties[trim($name)] = trim($matches[2][$i]);
        }
        return $properties;
    }
    
    private function extract_css_variables($css) {
        preg_match_all('/--([a-z-]+):\s*([^;]+);/', $css, $matches);
        $variables = [];
        foreach ($matches[1] as $i => $name) {
            $variables[$name] = $matches[2][$i];
        }
        return $variables;
    }
    
    private function extract_media_queries($css) {
        preg_match_all('/@media\s+\(([^)]+)\)\s*\{([^\}]+)\}/s', $css, $matches);
        $media_queries = [];
        foreach ($matches[1] as $i => $condition) {
            $media_queries[$condition] = $matches[2][$i];
        }
        return $media_queries;
    }
    
    private function get_selector_description($selector) {
        $descriptions = [
            '.fancine-card' => __('Contenedor principal de tarjeta', 'fancine-elementor'),
            '.card-title' => __('Título de la tarjeta', 'fancine-elementor'),
            '.card-meta' => __('Área de metadatos (autor, fecha)', 'fancine-elementor')
        ];
        return $descriptions[$selector] ?? __('Elemento de la skin', 'fancine-elementor');
    }
}