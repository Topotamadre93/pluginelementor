<?php
class Fancine_Template_Cache {
    public function get_compiled_template($template_path, $data) {
        $cache_key = 'fancine_tpl_' . md5($template_path . serialize($data));
        $cached = get_transient($cache_key);
        
        if (false === $cached) {
            $engine = new Fancine_Template_Engine();
            $cached = $engine->render(file_get_contents($template_path), $data);
            set_transient($cache_key, $cached, 12 * HOUR_IN_SECONDS);
        }
        
        return $cached;
    }
}