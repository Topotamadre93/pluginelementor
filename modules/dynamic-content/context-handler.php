<?php
class Fancine_Context_Handler {
    public function get_current_context() {
        global $post;
        $context = [];
        
        if ($post) {
            $context['post'] = $post;
            $context['author'] = get_userdata($post->post_author);
        }
        
        return $context;
    }
}