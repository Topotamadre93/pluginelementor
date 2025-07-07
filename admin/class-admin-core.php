<?php
// admin/class-admin-core.php

defined( 'ABSPATH' ) || exit;

/**
 * Class Fancine_Admin_Core
 *
 * Gestiona la página de ajustes donde activar/desactivar módulos.
 */
class Fancine_Admin_Core {

    public function __construct() {
        add_action( 'admin_menu',   [ $this, 'add_menu_page'     ] );
        add_action( 'admin_init',   [ $this, 'register_settings' ] );
    }

    /**
     * Añade la página de menú “Fancine” en el admin.
     */
    public function add_menu_page() {
        add_menu_page(
            __( 'Fancine Settings', 'fancine-elementor' ), // Título de página
            __( 'Fancine',          'fancine-elementor' ), // Título de menú
            'manage_options',                              // Capability
            'fancine-settings',                            // Slug
            [ $this, 'settings_page_html' ],               // Callback de render
            'dashicons-admin-generic',                     // Icono
            80                                             // Posición
        );
    }

    /**
     * Renderiza el HTML de la página de ajustes.
     */
    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fancine Settings', 'fancine-elementor' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                    settings_fields( 'fancine_settings' );
                    do_settings_sections( 'fancine-settings' );
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Registra el setting y los campos de la sección “Módulos”.
     */
    public function register_settings() {
        // 1) Registramos la opción que guardará los slugs activos
        register_setting(
            'fancine_settings',
            Fancine_Module_Manager::OPTION_ACTIVE_MODULES,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_active_modules' ],
                'default'           => [],
            ]
        );

        // 2) Creamos la sección de módulos
        add_settings_section(
            'fancine_modules_section',
            __( 'Available Modules', 'fancine-elementor' ),
            [ $this, 'modules_section_callback' ],
            'fancine-settings'
        );

        // 3) Por cada módulo registrado, añadimos un checkbox
        $manager = Fancine_Module_Manager::instance();
        $all     = $manager->get_all_modules();

        foreach ( $all as $slug => $config ) {
            add_settings_field(
                'fancine_module_' . $slug,
                esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ),
                [ $this, 'module_checkbox_field' ],
                'fancine-settings',
                'fancine_modules_section',
                [ 'slug' => $slug ]
            );
        }
    }

    /**
     * Callback para la descripción de la sección.
     */
    public function modules_section_callback() {
        echo '<p>' . esc_html__( 'Selecciona qué módulos quieres activar en tu plugin.', 'fancine-elementor' ) . '</p>';
    }

    /**
     * Callback para renderizar cada checkbox de módulo.
     *
     * @param array $args Recibe ['slug' => 'blog-posts']
     */
    public function module_checkbox_field( $args ) {
        $slug        = $args['slug'];
        $option_name = Fancine_Module_Manager::OPTION_ACTIVE_MODULES;
        $active      = get_option( $option_name, [] );

        printf(
            '<label><input type="checkbox" name="%1$s[]" value="%2$s"%3$s /> %4$s</label>',
            esc_attr( $option_name ),
            esc_attr( $slug ),
            checked( in_array( $slug, $active, true ), true, false ),
            esc_html( ucwords( str_replace( '-', ' ', $slug ) ) )
        );
    }

    /**
     * Sanitiza el array de módulos seleccionados,
     * activa/desactiva cada módulo y devuelve
     * el array limpio para guardar en BD.
     *
     * @param mixed $input Lo que viene del form.
     * @return array       Lista de slugs válidos.
     */
    public function sanitize_active_modules( $input ) {
        $manager = Fancine_Module_Manager::instance();
        $all     = array_keys( $manager->get_all_modules() );
        $new     = [];

        if ( is_array( $input ) ) {
            // Solo permitimos slugs válidos
            foreach ( $input as $slug ) {
                if ( in_array( $slug, $all, true ) ) {
                    $new[] = $slug;
                }
            }
        }

        // Activamos los nuevos
        foreach ( $new as $slug ) {
            if ( ! $manager->is_active( $slug ) ) {
                $manager->activate_module( $slug );
            }
        }
        // Desactivamos los que ya no están
        $old = get_option( Fancine_Module_Manager::OPTION_ACTIVE_MODULES, [] );
        foreach ( $old as $slug ) {
            if ( ! in_array( $slug, $new, true ) ) {
                $manager->deactivate_module( $slug );
            }
        }

        return $new;
    }
}
