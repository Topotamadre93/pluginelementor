<?php
class Fancine_Dynamic_Engine {
    private static $instance = null;
    private $tags = [];
    
    public static function init() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Registrar tags predeterminados
        $this->register_default_tags();
    }

    private function register_default_tags() {
        $this->tags = [
            'post' => [
                'title' => ['callback' => 'get_the_title'],
                'excerpt' => ['callback' => 'get_the_excerpt'],
                'date' => ['callback' => [$this, 'get_post_date']],
                'thumbnail' => ['callback' => [$this, 'get_post_thumbnail']]
            ],
            'author' => [
                'name' => ['callback' => 'get_the_author'],
                'bio' => ['callback' => 'get_the_author_meta', 'params' => ['description']]
            ],
            'system' => [
                'site.name' => ['callback' => [$this, 'get_site_name']],
                'current.date' => ['callback' => [$this, 'get_current_date']]
            ]
        ];
    }

    public function resolve_tag($tag_string, $context = []) {
        if (preg_match('/^{{([a-z.]+)}}$/', $tag_string, $matches)) {
            $tag = $matches[1];
            $parts = explode('.', $tag);
            $category = $parts[0];
            $tag_name = $parts[1] ?? '';
            
            if (isset($this->tags[$category][$tag_name])) {
                $config = $this->tags[$category][$tag_name];
                $callback = $config['callback'];
                $params = $config['params'] ?? [];
                
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $params);
                }
            }
        }
        return '';
    }

    public function register_tag($category, $tag_name, $config) {
        if (!isset($this->tags[$category])) {
            $this->tags[$category] = [];
        }
        $this->tags[$category][$tag_name] = $config;
    }

    public function get_all_tags() {
        return $this->tags;
    }
    
    // Helper functions
    private function get_post_date() {
        return get_the_date();
    }
    
    private function get_post_thumbnail() {
        return get_the_post_thumbnail(null, 'medium');
    }
    
    private function get_site_name() {
        return get_bloginfo('name');
    }
    
    private function get_current_date() {
        return date('Y-m-d');
    }
}