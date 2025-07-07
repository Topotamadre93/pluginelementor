<?php
if (!defined('ABSPATH')) exit;

class Fancine_Default_Skin extends \Elementor\Skin_Base {

    public function __construct(\Elementor\Widget_Base $parent) {
        parent::__construct($parent);
    }

    public function get_id() {
        return 'fancine_default_skin';
    }

    public function get_title() {
        return __('Skin por Defecto', 'fancine-elementor');
    }

    public function render($query = null, $pagination = '') {
        $settings = $this->parent->get_settings_for_display();
        
        if(!$query) {
            $query = Fancine_Query_Manager::get_query($settings);
        }
        
        if(!$pagination) {
            $pagination = Fancine_Query_Manager::get_pagination($query, $settings);
        }
        
        // Variables CSS dinámicas
        $primary_color = $settings['primary_color'] ?: '#A8C408';
        $secondary_color = $settings['secondary_color'] ?: '#E41C6C';
        $columns = $settings['columns'] ?: 3;

        // Incluir CSS dinámico
        echo "<style>
            :root {
                --fancine-primary: {$primary_color};
                --fancine-secondary: {$secondary_color};
            }
            .fancine-blog-grid {
                grid-template-columns: repeat({$columns}, minmax(0, 1fr)) !important;
            }
        </style>";
        ?>
        
        <div class="fancine-blog-section">
            <div class="fancine-blog-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="fancine-blog-card">
                        <div class="fancine-blog-img-container">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large', ['class' => 'fancine-blog-img']); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(plugins_url('assets/img/default-thumbnail.jpg', __FILE__)); ?>" class="fancine-blog-img">
                            <?php endif; ?>
                            <div class="fancine-blog-overlay">
                                <a href="<?php the_permalink(); ?>">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="fancine-blog-content">
                            <div class="fancine-blog-category">
                                <?php 
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo esc_html($categories[0]->name);
                                }
                                ?>
                            </div>
                            
                            <h2 class="fancine-blog-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            
                            <div class="fancine-blog-meta">
                                <div class="fancine-blog-meta-item">
                                    <i class="fa fa-user fa-fw"></i>
                                    <span><?php the_author(); ?></span>
                                </div>
                                
                                <span class="fancine-blog-meta-separator">•</span>
                                
                                <div class="fancine-blog-meta-item">
                                    <i class="fa fa-calendar-alt"></i>
                                    <span><?php echo get_the_date(); ?></span>
                                </div>
                                
                                <span class="fancine-blog-meta-separator">•</span>
                                
                                <div class="fancine-blog-meta-item">
                                    <i class="fa fa-comments"></i>
                                    <span><?php comments_number('0 comentarios', '1 comentario', '% comentarios'); ?></span>
                                </div>
                            </div>

                            <div class="fancine-blog-excerpt">
                                <p><?php echo wp_trim_words(get_the_excerpt(), 25); ?></p>
                            </div>
                            
                            <div class="fancine-blog-button-container">
                                <a href="<?php the_permalink(); ?>" class="fancine-blog-button">
                                    <i class="fas fa-book-reader"></i><?php _e('Leer artículo completo', 'fancine-elementor'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <?php if ($pagination) : ?>
                <div class="fancine-pagination">
                    <?php echo $pagination; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}