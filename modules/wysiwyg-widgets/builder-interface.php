<?php
class Fancine_Visual_Component_Builder {
    private $components = [];
    
    public function register_component($slug, $config) {
        $this->components[$slug] = [
            'name' => $config['name'],
            'icon' => $config['icon'],
            'fields' => $this->add_dynamic_fields($config['fields']),
            'template' => $this->parse_dynamic_template($config['template'])
        ];
    }
    
    private function add_dynamic_fields($fields) {
        $engine = Fancine_Dynamic_Engine::init();
        
        $fields['dynamic_content'] = [
            'type' => 'dynamic_select',
            'label' => __('Contenido Dinámico', 'fancine-elementor'),
            'options' => $engine->get_all_tags()
        ];
        return $fields;
    }
    
    private function parse_dynamic_template($template) {
        return preg_replace_callback(
            '/{{(.*?)}}/',
            function($matches) {
                return sprintf(
                    '<span class="fancine-dynamic-placeholder" data-tag="%s">{{%s}}</span>',
                    esc_attr($matches[1]),
                    esc_html($matches[1])
                );
            },
            $template
        );
    }
}