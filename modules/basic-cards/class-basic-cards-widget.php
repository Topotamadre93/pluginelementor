<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fancine_Basic_Cards_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'fancine_basic_cards';
    }

    public function get_title() {
        return esc_html__('Fancine Basic Cards', 'fancine-elementor');
    }

    public function get_icon() {
        return 'eicon-flip-box';
    }

    public function get_categories() {
        return ['fancine-category'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'fancine-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'card_title',
            [
                'label' => esc_html__('Title', 'fancine-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Card Title', 'fancine-elementor'),
            ]
        );

        $this->add_control(
            'card_description',
            [
                'label' => esc_html__('Description', 'fancine-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Card description goes here', 'fancine-elementor'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="fancine-basic-card">
            <h3 class="card-title"><?php echo esc_html($settings['card_title']); ?></h3>
            <div class="card-content">
                <?php echo wp_kses_post($settings['card_description']); ?>
            </div>
        </div>
        <?php
    }
}