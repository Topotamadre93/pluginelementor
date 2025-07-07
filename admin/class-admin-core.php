<?php
// admin/class-admin-core.php

defined( 'ABSPATH' ) || exit;

/**
 * Class Fancine_Admin_Core
 *
 * Gestiona el menú “Fancine” y sus subpáginas:
 * - Constructor
 * - Ajustes  (donde aparecerán los checkboxes)
 * - CSS Docs
 */
class Fancine_Admin_Core {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Registra el menú padre “Fancine” y sus subpáginas.
     */
    public function register_admin_pages() {
        // Menú padre (Constructor)
        add_menu_page(
            __( 'Constructor', 'fancine-elementor' ),     // Título página
            __( 'Fancine',     'fancine-elementor' ),     // Texto menú
            'manage_options',                              // Capability
            'fancine-dashboard',                           // Slug padre
            [ $this, 'constructor_page' ],                 // Callback
            'dashicons-admin-generic',                     // Icono
            80                                              // Posición
        );

        // Subpágina: Ajustes (modificar módulos)
        add_submenu_page(
            'fancine-dashboard',                           // Parent slug
            __( 'Ajustes', 'fancine-elementor' ),          // Título página
            __( 'Ajustes', 'fancine-elementor' ),          // Texto submenú
            'manage_options',                              // Capability
            'fancine-settings',                            // Slug
            [ $this, 'settings_page_html' ]                // Callback
        );

        // Subpágina: CSS Docs
        add_submenu_page(
            'fancine-dashboard',
            __( 'CSS Docs', 'fancine-elementor' ),
            __( 'CSS Docs', 'fancine-elementor' ),
            'manage_options',
            'fancine-css-docs',
            [ $this, 'css_docs_page' ]
        );
    }

    /**
     * Renderiza la UI de “Constructor” (pantalla principal).
     */
    public function constructor_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fancine Constructor', 'fancine-elementor' ); ?></h1>
            <p><?php esc_html_e( 'Aquí irá tu interfaz para arrastrar widgets, etc.', 'fancine-elementor' ); ?></p>
        </div>
        <?php
    }

    /**
     * Renderiza la UI de “Ajustes” (check­boxes de módulos).
     */
    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ajustes de Fancine', 'fancine-elementor' ); ?></h1>
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
     * Renderiza la UI de “CSS Docs”.
     */
    public function css_docs_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Documentación CSS de Fancine', 'fancine-elementor' ); ?></h1>
            <p><?php esc_html_e( 'Aquí irán tus guías y ejemplos de CSS.', 'fancine-elementor' ); ?></p>
        </div>
        <?php
    }

    /**
     * Registra la opción y los campos de la sección “Available Modules”.
     */
    public function register_settings() {
        // 1) Opción array para los slugs activos
        register_setting(
            'fancine_settings',
            Fancine_Module_Manager::OPTION_ACTIVE_MODULES,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_active_modules' ],
                'default'           => [],
            ]
        );

        // 2) Sección “Available Modules”
        add_settings_section(
            'fancine_modules_section',
            __( 'Available Modules', 'fancine-elementor' ),
            function() {
                echo '<p>' . esc_html__( 'Marca los módulos que quieres activar:', 'fancine-elementor' ) . '</p>';
            },
            'fancine-settings'
        );

        // 3) Un checkbox por cada módulo registrado
        $manager = Fancine_Module_Manager::instance();
        foreach ( $manager->get_all_modules() as $slug => $config ) {
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
     * Callback para renderizar cada checkbox de módulo.
     *
     * @param array $args ['slug' => 'blog-posts']
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
     * Sanitiza y actualiza los módulos activos al guardar ajustes.
     *
     * @param mixed $input
     * @return array
     */
    public function sanitize_active_modules( $input ) {
        $manager = Fancine_Module_Manager::instance();
        $all     = array_keys( $manager->get_all_modules() );
        $new     = [];

        if ( is_array( $input ) ) {
            foreach ( $input as $slug ) {
                if ( in_array( $slug, $all, true ) ) {
                    $new[] = $slug;
                }
            }
        }

        // Activar los recién marcados
        foreach ( $new as $slug ) {
            if ( ! $manager->is_active( $slug ) ) {
                $manager->activate_module( $slug );
            }
        }
        // Desactivar los que se hayan desmarcado
        $old = get_option( Fancine_Module_Manager::OPTION_ACTIVE_MODULES, [] );
        foreach ( $old as $slug ) {
            if ( ! in_array( $slug, $new, true ) ) {
                $manager->deactivate_module( $slug );
            }
        }

        return $new;
    }
}
