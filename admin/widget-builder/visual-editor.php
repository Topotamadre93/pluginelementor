<?php
class Fancine_Widget_Builder_UI {
    public function render() {
        ob_start(); ?>
        <div class="widget-builder">
            <div class="components-panel">
                <?php $this->render_components_list(); ?>
            </div>
            
            <div class="editing-canvas">
                <div class="widget-preview">
                    <?php _e('Arrastra componentes aquí', 'fancine-elementor'); ?>
                </div>
            </div>
            
            <div class="properties-panel">
                <?php $this->render_properties_form(); ?>
                <div class="dynamic-content-section">
                    <h4><?php _e('Contenido Dinámico', 'fancine-elementor'); ?></h4>
                    <?php $this->render_tag_selector(); ?>
                </div>
                <div class="css-reference-section">
                    <h4><?php _e('Referencia CSS', 'fancine-elementor'); ?></h4>
                    <?php $this->render_css_reference(); ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }
    
    private function render_components_list() {
        // Obtener componentes registrados
        $components = apply_filters('fancine_registered_components', []);
        foreach ($components as $slug => $component) {
            echo '<div class="component" data-slug="' . esc_attr($slug) . '">';
            echo '<span class="component-icon">' . $component['icon'] . '</span>';
            echo '<span class="component-name">' . esc_html($component['name']) . '</span>';
            echo '</div>';
        }
    }
}