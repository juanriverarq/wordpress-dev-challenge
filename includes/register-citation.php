<?php
/*
 **** Register citation ****
 */
function register_citation()
{
    add_meta_box(
        "mi-meta-box-id",
        __("Citation", "RegisterCitation"),
        "citation_callback",
        "post"
    );
}
add_action("add_meta_boxes", "register_citation");
