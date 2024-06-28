<?php

class Seu_Plugin_Editor_Codigo_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Gerenciar Códigos',
            'Gerenciar Códigos',
            'manage_options',
            'gerenciar-codigos',
            array($this, 'create_admin_page'),
            'dashicons-editor-code',
            6
        );
    }

    public function register_settings() {
        register_setting('gerenciar-codigos-group', 'seu_plugin_codigo_options');
    }

    public function create_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $codes = $wpdb->get_results("SELECT * FROM $table_name");

        ?>
        <div class="wrap">
            <h1>Gerenciar Códigos dos Usuários</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Nome do Código</th>
                        <th>Público</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $code): ?>
                        <tr>
                            <td><?php echo esc_html($code->id); ?></td>
                            <td><?php echo esc_html(get_userdata($code->user_id)->display_name); ?></td>
                            <td><?php echo esc_html($code->code_name); ?></td>
                            <td><?php echo esc_html($code->public ? 'Sim' : 'Não'); ?></td>
                            <td>
                                <button class="delete-code-button" data-id="<?php echo esc_attr($code->id); ?>">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.delete-code-button').on('click', function() {
                if (confirm('Tem certeza que deseja excluir este código?')) {
                    var codeId = $(this).data('id');
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'delete_user_code',
                            code_id: codeId,
                            nonce: '<?php echo wp_create_nonce("delete_user_code_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Erro ao excluir o código.');
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
}

public function register_admin_page() {
    add_menu_page(
        'Gerenciar Códigos',
        'Gerenciar Códigos',
        'manage_options',
        'gerenciar-codigos',
        array($this, 'render_admin_page'),
        'dashicons-admin-generic',
        6
    );
}


new Seu_Plugin_Editor_Codigo_Admin();
?>
