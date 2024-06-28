<?php
class Seu_Plugin_Editor_Codigo_DB {

    public static function ativar() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            code_name tinytext NOT NULL,
            html_code longtext NOT NULL,
            css_code longtext NOT NULL,
            js_code longtext NOT NULL,
            is_public boolean NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function desativar() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);
    }
}
?>
