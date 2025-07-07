<?php
class Fancine_Tag_Manager {
    public static function init() {
        $engine = Fancine_Dynamic_Engine::init();
        
        // Registrar tags de taxonomías
        $engine->register_tag('taxonomies', 'category.name', [
            'callback' => [__CLASS__, 'get_primary_category'],
            'description' => __('Categoría principal del post', 'fancine-elementor')
        ]);
        
        $engine->register_tag('taxonomies', 'tags.list', [
            'callback' => [__CLASS__, 'get_tags_list'],
            'description' => __('Lista de etiquetas separadas por comas', 'fancine-elementor')
        ]);
    }
    
    public static function get_primary_category() {
        $categories = get_the_category();
        return !empty($categories) ? $categories[0]->name : '';
    }
    
    public static function get_tags_list() {
        $tags = get_the_tags();
        if (!$tags) return '';
        
        $tag_names = array_map(function($tag) {
            return $tag->name;
        }, $tags);
        
        return implode(', ', $tag_names);
    }
}
add_action('init', ['Fancine_Tag_Manager', 'init']);