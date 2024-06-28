<?php
class Seu_Plugin_Editor_Codigo {

    public function run() {
        add_shortcode('editor_codigo', array($this, 'render_editor'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
        add_action('wp_ajax_save_user_code', array($this, 'save_user_code'));
        add_action('wp_ajax_nopriv_save_user_code', array($this, 'save_user_code'));
        add_action('wp_ajax_retrieve_user_code', array($this, 'retrieve_user_code'));
        add_action('wp_ajax_nopriv_retrieve_user_code', array($this, 'retrieve_user_code'));
        add_action('wp_ajax_get_user_codes', array($this, 'get_user_codes'));
        add_action('wp_ajax_toggle_code_visibility', array($this, 'toggle_code_visibility'));
        add_action('wp_ajax_delete_user_code', array($this, 'delete_user_code'));
        add_action('admin_menu', array($this, 'register_admin_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function render_editor($atts) {
        ob_start();
        $current_user = wp_get_current_user();
        $is_logged_in = is_user_logged_in();
        ?>
        <div id="editor-container">
            <div class="editor-column">
                <h3>HTML/Pug</h3>
                <div id="html-editor" class="editor"></div>
                <input type="file" id="upload-html" accept=".html,.pug">
                <button id="download-html" class="custom-button">Download HTML</button>
            </div>
            <div class="editor-column">
                <h3>CSS/SCSS/Stylus</h3>
                <div id="css-editor" class="editor"></div>
                <input type="file" id="upload-css" accept=".css,.scss,.styl">
                <button id="download-css" class="custom-button">Download CSS</button>
            </div>
            <div class="editor-column">
                <h3>JavaScript/Babel</h3>
                <div id="js-editor" class="editor"></div>
                <input type="file" id="upload-js" accept=".js,.babel">
                <button id="download-js" class="custom-button">Download JS</button>
            </div>
        </div>
        <div id="preview-container">
            <iframe id="preview-iframe"></iframe>
        </div>
        <div id="code-controls">
            <input type="text" id="code-name" placeholder="Nome do Código">
            <button id="save-code-button" class="custom-button">Salvar Código</button>
            <button id="retrieve-code-button" class="custom-button" style="margin-left: 50px;">Recuperar Código</button>
            <?php if ($is_logged_in): ?>
                <div id="user-info" style="position: relative; margin-left: auto; margin-right: auto;">
                    <div id="user-avatar" style="background-image: url('<?php echo esc_url(get_avatar_url($current_user->ID)); ?>'); width: 50px; height: 50px; border-radius: 50%; background-size: cover; margin-right: 10px; display: inline-block;"></div>
                    <div id="user-name" style="display: inline-block; vertical-align: top;"><?php echo esc_html($current_user->display_name); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_editor_scripts() {
        wp_enqueue_style('ace-custom-style', plugin_dir_url(__FILE__) . '../css/ace-custom-style.css');
        wp_enqueue_script('ace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js', array(), null, true);
        wp_enqueue_script('editor-codigo-js', plugin_dir_url(__FILE__) . '../js/editor-codigo.js', array('jquery', 'ace'), null, true);
        wp_localize_script('editor-codigo-js', 'ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('save_user_code_nonce')
        ));
    }

    public function save_user_code() {
        check_ajax_referer('save_user_code_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Usuário não autenticado.');
            return;
        }

        $user_id = get_current_user_id();
        $code_name = sanitize_text_field($_POST['code_name']);
        $html_code = base64_decode($_POST['html_code']);
        $css_code = base64_decode($_POST['css_code']);
        $js_code = base64_decode($_POST['js_code']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';

        $data = array(
            'user_id' => $user_id,
            'code_name' => $code_name,
            'html_code' => $html_code,
            'css_code' => $css_code,
            'js_code' => $js_code,
            'created_at' => current_time('mysql')
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%s');

        if ($wpdb->insert($table_name, $data, $format)) {
            wp_send_json_success('Código salvo com sucesso.');
        } else {
            wp_send_json_error('Erro ao salvar o código.');
        }
    }

    public function retrieve_user_code() {
        check_ajax_referer('save_user_code_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Usuário não autenticado.');
            return;
        }

        $user_id = get_current_user_id();
        $code_name = sanitize_text_field($_POST['code_name']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $code = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND code_name = %s", $user_id, $code_name), ARRAY_A);

        if ($code) {
            wp_send_json_success($code);
        } else {
            wp_send_json_error('Código não encontrado.');
        }
    }

    public function get_user_codes() {
        check_ajax_referer('save_user_code_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Usuário não autenticado.');
            return;
        }

        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $codes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id));

        if ($codes) {
            wp_send_json_success($codes);
        } else {
            wp_send_json_error('Nenhum código encontrado para este usuário.');
        }
    }

    public function toggle_code_visibility() {
        check_ajax_referer('save_user_code_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Usuário não autenticado.');
            return;
        }

        $user_id = get_current_user_id();
        $code_id = intval($_POST['code_id']);
        $is_public = intval($_POST['is_public']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $updated = $wpdb->update($table_name, array('public' => $is_public), array('id' => $code_id, 'user_id' => $user_id));

        if ($updated !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Erro ao atualizar visibilidade do código.');
        }
    }

    public function delete_user_code() {
        check_ajax_referer('save_user_code_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Usuário não autenticado.');
            return;
        }

        $user_id = get_current_user_id();
        $code_id = intval($_POST['code_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $deleted = $wpdb->delete($table_name, array('id' => $code_id, 'user_id' => $user_id));

        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Erro ao excluir código.');
        }
    }

    public function enqueue_admin_assets() {
        wp_enqueue_script('admin-codes-js', plugin_dir_url(__FILE__) . '../assets/js/admin-codes.js', array('jquery'), null, true);
        wp_localize_script('admin-codes-js', 'ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('save_user_code_nonce')
        ));
    }

    public function register_admin_page() {
        add_menu_page(
            'Gerenciamento de Códigos',
            'Gerenciamento de Códigos',
            'manage_options',
            'gerenciamento-de-codigos',
            array($this, 'admin_page_content'),
            'dashicons-editor-code',
            6
        );
    }

    public function admin_page_content() {
        ?>
        <div class="wrap">
            <h1>Gerenciamento de Códigos</h1>
            <div id="admin-codes-container"></div>
        </div>
        <?php
    }
}
