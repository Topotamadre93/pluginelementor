<?php
class Fancine_API_Interface {
    public function register_endpoints() {
        add_action('rest_api_init', function() {
            register_rest_route('fancine/v1', '/tags', [
                'methods' => 'GET',
                'callback' => [$this, 'get_available_tags'],
                'permission_callback' => '__return_true'
            ]);
        });
    }
    
    public function get_available_tags() {
        $engine = Fancine_Dynamic_Engine::init();
        return new WP_REST_Response($engine->get_all_tags(), 200);
    }
}