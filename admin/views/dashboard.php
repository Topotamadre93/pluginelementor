<div class="wrap fancine-dashboard">
    <h1><?php _e('Fancine Elementor Dashboard', 'fancine-elementor'); ?></h1>
    
    <div class="fancine-stats">
        <div class="stat-card">
            <h3><?php _e('Widgets Registrados', 'fancine-elementor'); ?></h3>
            <p>12</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Skins Disponibles', 'fancine-elementor'); ?></h3>
            <p>5</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Plantillas Guardadas', 'fancine-elementor'); ?></h3>
            <p>8</p>
        </div>
    </div>
    
    <div class="fancine-quick-links">
        <a href="<?php echo admin_url('admin.php?page=fancine-widget-builder'); ?>" class="button button-primary">
            <?php _e('Constructor de Widgets', 'fancine-elementor'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=fancine-css-docs'); ?>" class="button">
            <?php _e('Documentación CSS', 'fancine-elementor'); ?>
        </a>
    </div>
</div>