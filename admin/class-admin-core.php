<?php
// admin/class-admin-core.php

defined( 'ABSPATH' ) || exit;

/**
 * Class Fancine_Admin_Core
 *
 * Gestiona el menú “Fancine” y sus subpáginas (Constructor, Ajustes, CSS Docs).
 */
class Fancine_Admin_Core {

    public function __construct() {
        // Registro del menú y de los ajustes
        add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Registra el menú padre y sus subpáginas.
     */
    public function register_admin_pages() {
        // 1) Menú padre “Fancine”
        add_menu_page(
            __( 'Fancine', 'fancine-elementor' ),           // Título página
            __( 'Fancine', 'fancine-elementor' ),           // Texto menú
            'manage_options',                               // Capability
            'fancine-dashboard',                            // Slug padre
            [ $this, 'constructor_page' ],                  // Callback principal
            'dashicons-admin-generic',                      // Icono
            80                                              // Posición
        );

        // 2) Subpágina: Constructor (misma callback que la principal)
        add_submenu_page(
            'fancine-dashboard',                            // Parent slug
            __( 'Constructor', 'fancine-elementor' ),       // Título página
            __( 'Constructor', 'fancine-elementor' ),       // Texto submenú
            'manage_options',                               // Capability
            'fancine-dashboard',                            // Slug (misma que el padre)
            [ $this, 'constructor_page' ]                   // Callback
        );

        // 3) Subpágina: Ajustes (nuestros checkboxes)
        add_submenu_page(
            'fancine-dashboard',
            __( 'Ajustes', 'fancine-elementor' ),
            __( 'Ajustes', 'fancine-elementor' ),
            'manage_options',
            'fancine-settings',
            [ $this, 'settings_page_html' ]
        );

        // 4) Subpágina: CSS Docs
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
     * Callback para la página “Constructor”.
     */
    public function constructor_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fancine Constructor', 'fancine-elementor' ); ?></h1>
            <p><?php esc_html_e( 'Aquí irá tu UI para arrastrar widgets, etc.', 'fancine-elementor' ); ?></p>
        </div>
        <?php
    }

    /**
     * Callback para la página “Ajustes” (módulos).
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
     * Callback para la página “CSS Docs”.
     */
    public function css_docs_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fancine CSS Docs', 'fancine-elementor' ); ?></h1>
            <p><?php esc_html_e( 'Documentación de clases y utilidades CSS.', 'fancine-elementor' ); ?></p>
        </div>
        <?php
    }

    /**
     * Registra la opción y los campos de la sección “Available Modules”.
     */
    public function register_settings() {
        // 1) Opción array para slugs activos
        register_setting(
            'fancine_settings',
            Fancine_Module_Manager::OPTION_ACTIVE_MODULES,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_active_modules' ],
                'default'           => [],
            ]
        );

        // 2) Sección de módulos
        add_settings_section(
            'fancine_modules_section',
            __( 'Available Modules', 'fancine-elementor' ),
            [ $this, 'modules_section_callback' ],
            'fancine-settings'
        );

        // 3) Por cada módulo, un checkbox
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
     * Descripción de la sección de módulos.
     */
    public function modules_section_callback() {
        echo '<p>' . esc_html__( 'Marca los módulos que quieres activar:', 'fancine-elementor' ) . '</p>';
    }

    /**
     * Renderiza el checkbox de cada módulo.
     *
     * @param array $args ['slug'=>'blog-posts']
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

        // Activar nuevos
        foreach ( $new as $slug ) {
            if ( ! $manager->is_active( $slug ) ) {
                $manager->activate_module( $slug );
            }
        }
        // Desactivar los eliminados
        $old = get_option( Fancine_Module_Manager::OPTION_ACTIVE_MODULES, [] );
        foreach ( $old as $slug ) {
            if ( ! in_array( $slug, $new, true ) ) {
                $manager->deactivate_module( $slug );
            }
        }

        return $new;
    }
}
