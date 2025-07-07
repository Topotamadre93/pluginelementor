<?php
class Fancine_Blog_Posts_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'fancine_blog_posts';
    }

    public function get_title() {
        return __('Fancine Blog Posts', 'fancine-elementor');
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }

    public function get_categories() {
        return ['fancine-category'];
    }

    protected function _register_controls() {
        // Controles del widget
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'fancine-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'fancine-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $settings['posts_per_page']
        ]);

        if ($query->have_posts()) {
            echo '<div class="fancine-blog-posts">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<article class="fancine-blog-post">';
                echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                echo '<div class="post-excerpt">' . get_the_excerpt() . '</div>';
                echo '</article>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No posts found', 'fancine-elementor') . '</p>';
        }
    }
}