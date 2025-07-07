<?php
/**
 * core/class-module-manager.php
 *
 * Gestor de módulos para Fancine Elementor Addons Pro.
 */

defined( 'ABSPATH' ) || exit;

class Fancine_Module_Manager {
    /**
     * Instancia singleton.
     *
     * @var Fancine_Module_Manager|null
     */
    protected static $instance = null;

    /**
     * Módulos registrados: ['slug' => ['path'=>'…','url'=>'…'], …].
     *
     * @var array
     */
    protected $modules = [];

    /**
     * Slugs de los módulos activos.
     *
     * @var array
     */
    protected $active_modules = [];

    /**
     * Nombre de la opción en wp_options.
     */
    const OPTION_ACTIVE_MODULES = 'fancine_active_modules';

    /**
     * Constructor privado: carga el estado de módulos activos.
     */
    private function __construct() {
        $this->active_modules = get_option( self::OPTION_ACTIVE_MODULES, [] );
        if ( ! is_array( $this->active_modules ) ) {
            $this->active_modules = [];
        }
    }

    /**
     * Devuelve la instancia única.
     *
     * @return Fancine_Module_Manager
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registra la configuración de un módulo.
     *
     * @param string $slug   Identificador del módulo.
     * @param array  $config ['path'=>'ruta absoluta','url'=>'url pública'].
     */
    public function register_module( $slug, array $config ) {
        $this->modules[ $slug ] = $config;
    }

    /**
     * Devuelve todos los módulos registrados (activos e inactivos).
     *
     * @return array
     */
    public function get_all_modules() {
        return $this->modules;
    }

    /**
     * Devuelve sólo los módulos activos.
     *
     * @return array
     */
    public function get_modules() {
        return array_filter(
            $this->modules,
            function( $config, $slug ) {
                return $this->is_active( $slug );
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Comprueba si un módulo está activo.
     *
     * @param string $slug
     * @return bool
     */
    public function is_active( $slug ) {
        return in_array( $slug, $this->active_modules, true );
    }

    /**
     * Activa un módulo (si no lo está ya).
     *
     * @param string $slug
     * @return bool True si cambió el estado.
     */
    public function activate_module( $slug ) {
        if ( ! isset( $this->modules[ $slug ] ) || $this->is_active( $slug ) ) {
            return false;
        }
        $this->active_modules[] = $slug;
        $this->update_active_modules_option();
        do_action( 'fancine_module_activated', $slug );
        return true;
    }

    /**
     * Desactiva un módulo (si está activo).
     *
     * @param string $slug
     * @return bool True si cambió el estado.
     */
    public function deactivate_module( $slug ) {
        if ( ! isset( $this->modules[ $slug ] ) || ! $this->is_active( $slug ) ) {
            return false;
        }
        $this->active_modules = array_diff( $this->active_modules, [ $slug ] );
        $this->update_active_modules_option();
        do_action( 'fancine_module_deactivated', $slug );
        return true;
    }

    /**
     * Guarda en la base de datos la lista actualizada de módulos activos.
     */
    protected function update_active_modules_option() {
        update_option( self::OPTION_ACTIVE_MODULES, $this->active_modules );
    }
}
