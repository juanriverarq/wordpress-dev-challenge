<?php

//Internal menu creation
function menu_links_validator()
{
    add_menu_page(
        "Link Scanner",
        "Link Scanner",
        "manage_options",
        "menu-links-validator",
        "link_scanning_results",
        "dashicons-editor-unlink",
        6
    );
}
add_action("admin_menu", "menu_links_validator");
