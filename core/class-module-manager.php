<?php
class Fancine_Module_Manager {
    protected static $instance = null;
    protected $modules = [];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_module($module_slug, $module_config) {
        $this->modules[$module_slug] = $module_config;
    }

    public function get_modules() {
        return $this->modules;
    }

    public function activate_module($module_slug) {
        // Lógica de activación
    }
}