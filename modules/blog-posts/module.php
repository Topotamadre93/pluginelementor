<?php
// Registrar widget de posts
add_action('elementor/widgets/register', 'fancine_register_blog_posts_widget');
function fancine_register_blog_posts_widget($widgets_manager) {
    require_once __DIR__ . '/class-blog-posts-widget.php';
    $widgets_manager->register(new Fancine_Blog_Posts_Widget());
}