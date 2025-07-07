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
        add_action( 'admin_menu',   [ $this, 'add_menu_page'    ] );
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
        // Registramos la opción array que guardará los slugs activos
        register_setting(
            'fancine_settings',
            Fancine_Module_Manager::OPTION_ACTIVE_MODULES,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_active_modules' ],
                'default'           => [],
            ]
        );

        // Sección sin texto (solo para agrupar los campos)
        add_settings_section(
            'fancine_modules_section',
            __( 'Available Modules', 'fancine-elementor' ),
            '__return_false',
            'fancine-settings'
        );

        // Por cada módulo registrado, añadimos un checkbox
        $manager = Fancine_Module_Manager::instance();
        $all     = $manager->get_all_modules();
        $active  = get_option( Fancine_Module_Manager::OPTION_ACTIVE_MODULES, [] );

        foreach ( $all as $slug => $config ) {
            $label = esc_html( ucwords( str_replace( '-', ' ', $slug ) ) );
            add_settings_field(
                'fancine_module_' . $slug,
                sprintf(
                    '<label><input type="checkbox" name="%1$s[]" value="%2$s"%3$s /> %4$s</label>',
                    esc_attr( Fancine_Module_Manager::OPTION_ACTIVE_MODULES ),
                    esc_attr( $slug ),
                    in_array( $slug, $active, true ) ? ' checked' : '',
                    $label
                ),
                '__return_false',
                'fancine-settings',
                'fancine_modules_section'
            );
        }
    }

    /**
     * Sanitiza el array de módulos seleccionados,
     * activa/desactiva cada módulo y devuelve
     * el array limpio para guardar en BD.
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
