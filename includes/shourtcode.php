<?php
//creation shourtcode
add_shortcode("mc-citacion", "get_citacion_shortcode");
function get_citacion_shortcode($atts)
{
    if (isset($atts["post_id"])) {
        $post_id = $atts["post_id"];
    } else {
        $post_id = get_the_ID();
    }
    return get_post_meta($post_id, "citations", true);
}
