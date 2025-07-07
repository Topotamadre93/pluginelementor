<?php
class Fancine_Template_Engine {
    public function render($template, $data) {
        // 1. Procesar condicionales
        $template = $this->parse_conditionals($template, $data);
        
        // 2. Procesar bucles
        $template = $this->parse_loops($template, $data);
        
        // 3. Reemplazar etiquetas
        return $this->parse_tags($template, $data);
    }

    private function parse_conditionals($template, $data) {
        return preg_replace_callback(
            '/{{#if\s+(.*?)}}(.*?){{\/if}}/s',
            function($matches) use ($data) {
                $var = trim($matches[1]);
                $content = $matches[2];
                return isset($data[$var]) && $data[$var] ? $content : '';
            },
            $template
        );
    }

    private function parse_loops($template, $data) {
        return preg_replace_callback(
            '/{{#each\s+(.*?)}}(.*?){{\/each}}/s',
            function($matches) use ($data) {
                $var = trim($matches[1]);
                $content = $matches[2];
                $output = '';
                
                if (isset($data[$var]) && is_array($data[$var])) {
                    foreach ($data[$var] as $item) {
                        $output .= $this->parse_tags($content, $item);
                    }
                }
                return $output;
            },
            $template
        );
    }

    private function parse_tags($template, $data) {
        return preg_replace_callback(
            '/{{(.*?)}}/',
            function($matches) use ($data) {
                $key = trim($matches[1]);
                return $data[$key] ?? '';
            },
            $template
        );
    }
}