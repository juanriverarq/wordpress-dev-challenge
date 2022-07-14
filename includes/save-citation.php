<?php
/*
 **** Save citation ****
 */
function save_citation($post_id)
{
    // We check if it is auto saved
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    // We check the nonce value created in citation_callback()
    if (
        !isset($_POST["meta_box_nonce"]) ||
        !wp_verify_nonce($_POST["meta_box_nonce"], "mi_meta_box_nonce")
    ) {
        return;
    }
    // Check if the current user can't edit the post
    if (!current_user_can("edit_post")) {
        return;
    }
    // Save...
    if (isset($_POST["citations"])) {
        update_post_meta($post_id, "citations", $_POST["citations"]);
    }
}
add_action("save_post", "save_citation");
