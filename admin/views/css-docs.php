<?php
$documenter = new Fancine_CSS_Documenter();
$skin = isset($_GET['skin']) ? sanitize_text_field($_GET['skin']) : 'default';
$css_data = $documenter->generate_reference($skin);
?>

<div class="wrap fancine-css-docs">
    <h1><?php _e('Documentación CSS', 'fancine-elementor'); ?></h1>
    
    <div class="css-reference">
        <div class="selector-list">
            <?php foreach ($css_data['selectors'] as $selector => $data) : ?>
                <div class="selector-item" data-selector="<?php echo esc_attr($selector); ?>">
                    <span class="selector-name"><?php echo esc_html($selector); ?></span>
                    <span class="selector-desc"><?php echo esc_html($data['description']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="selector-details">
            <h3><?php _e('Detalles del Selector', 'fancine-elementor'); ?></h3>
            <div class="selector-header">
                <span class="selector-title"></span>
                <span class="selector-desc"></span>
            </div>
            <div class="css-properties"></div>
            <div class="css-copy-section">
                <textarea class="css-copy-area" readonly></textarea>
                <button class="copy-button"><?php _e('Copiar CSS', 'fancine-elementor'); ?></button>
                <div class="usage-hint"></div>
            </div>
        </div>
    </div>
</div>