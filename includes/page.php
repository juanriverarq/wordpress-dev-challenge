<?php
//I run the menu screen with the function link_scanning_results
function link_scanning_results()
{
    $ListTable = new List_Table();
    table_creation();
    links_errors();
    $ListTable->prepare_items();
    ?>
    <div class="wrap">
        <h2>Escaner de links</h2>
        <?php $ListTable->display(); ?>
    </div>
<?php
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists("WP_List_Table")) {
    require_once ABSPATH . "wp-admin/includes/class-wp-list-table.php";
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

        $data = $this->table_data();
        usort($data, [&$this, "sort_data"]);

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args([
            "total_items" => $totalItems,
            "per_page" => $perPage,
        ]);

        $data = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = [
            "url" => "URL",
            "status" => "Estado",
            "post_id" => "Origen",
        ];

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return ["status" => ["status", false]];
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        global $wpdb;

        $data = [];

        //I query the table to extract the data
        $sql = "SELECT * FROM `errors_url_table`";
        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach ($results as $result) {
            $data[] = [
                "url" =>
                    "<b><span><img src='assets/img/alerta.png' width='15' style='padding-right:10px'></span><a href=" .
                    $result["url"] .
                    ">" .
                    $result["url"] .
                    "</a></b>",
                "status" =>
                    "<b><p style='color:orange; text-transform:capitalize;'> " .
                    $result["status"] .
                    "</p></b>",
                "post_id" =>
                    "<b><a href=" .
                    $result["post_id"] .
                    ">" .
                    get_the_title($result["post_id"]) .
                    "</a></b>",
            ];
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case "url":
            case "status":
            case "post_id":
                return $item[$column_name];

            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = "status";
        $order = "asc";

        // If orderby is set, use this as the sort column
        if (!empty($_GET["orderby"])) {
            $orderby = $_GET["orderby"];
        }

        // If order is set use this as the order
        if (!empty($_GET["order"])) {
            $order = $_GET["order"];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === "asc") {
            return $result;
        }

        return -$result;
    }
}

function table_creation()
{
    global $wpdb;
    $sql =
        "CREATE TABLE  IF NOT EXISTS errors_url_table (id bigint(10) NOT NULL AUTO_INCREMENT,post_id bigint(10) UNSIGNED NOT NULL,url varchar(500) DEFAULT '' NOT NULL,status varchar(30) DEFAULT '' NOT NULL,PRIMARY KEY  (id))";
    $wpdb->query($sql);
}

// cron desativation
function cron_desactivation()
{
    wp_clear_scheduled_hook("cron_hook");
}

function cron_activation()
{
    links_errors();
}

add_action("cron_hook", "links_errors");
function links_errors(): void
{
    global $wpdb;
    $post_table = $wpdb->prefix . "posts";
    $errors_url_table = "errors_url_table";
    $post_results = $wpdb->get_results(
        "SELECT * FROM `" .
            $post_table .
            "` WHERE `post_status` IN ('publish','draft') AND `post_type` LIKE 'post' ORDER BY `ID` ASC",
        ARRAY_A
    );
    $wpdb->query("DELETE FROM $errors_url_table");
    foreach ($post_results as $post_result) {
        $post_content = $post_result["post_content"];
        $post_id = $post_result["ID"];
        $post_urls = get_urls($post_content);

        if ($post_urls) {
            foreach ($post_urls as $post_url) {
                $post_url_status = get_status_url($post_url);
                if ($post_url_status != "enlace valido") {
                    $wpdb->insert($errors_url_table, [
                        "url" => $post_url,
                        "status" => $post_url_status,
                        "post_id" => $post_id,
                    ]);
                }
            }
        }
    }
}

function get_urls($post_content): array
{
    $result = [];
    $pattern = '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i';
    preg_match_all($pattern, $post_content, $result);
    return $result["href"];
}

//validate status for URL
function get_status_url($post_url): string
{
    if (!validate_whitespace($post_url)) {
        if (validate_filter_url($post_url)) {
            if (validate_protocol($post_url)) {
                if (validate_exist_url($post_url)) {
                    $state_url = "enlace valido";
                } else {
                    $state_url = "enlace no existe";
                }
            } else {
                $state_url = "enlace inseguro";
            }
        } else {
            if (!validate_protocol($post_url, 2)) {
                if (validate_filter_url($post_url, "https://")) {
                    $state_url = "protocolo no especificado";
                } else {
                    $state_url = "enlance malformado";
                }
            } else {
                $state_url = "protocolo no especificado";
            }
        }
    } else {
        $state_url = "enlance malformado";
    }

    return $state_url;
}

// validate whitespace
function validate_whitespace($post_url): string
{
    return count(explode(" ", $post_url)) - 1 > 0;
}

//validate protocol https
function validate_protocol($post_url, $type = 1): string
{
    if ($type == 1) {
        return strripos($post_url, "https://") === 0;
    } elseif ($type == 2) {
        return strripos($post_url, "http:") === 0 ||
            strripos($post_url, "https:") === 0;
    }
    return false;
}

//validate exist url

function validate_exist_url($post_url): string
{
    $headers = @get_headers($post_url);
    return strpos($headers[0], "200");
}
//Validate filter url
function validate_filter_url($post_url, $protocol = ""): string
{
    return filter_var($protocol . $post_url, FILTER_VALIDATE_URL);
}
