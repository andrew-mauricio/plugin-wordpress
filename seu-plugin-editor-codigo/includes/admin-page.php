<?php
function render_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_codes';
    $codes = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Gerenciar Códigos de Usuários</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Código</th>
                    <th>Usuário</th>
                    <th>Público</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($codes as $code): ?>
                    <tr>
                        <td><?php echo esc_html($code->id); ?></td>
                        <td><?php echo esc_html($code->code_name); ?></td>
                        <td><?php echo esc_html(get_userdata($code->user_id)->user_login); ?></td>
                        <td><?php echo $code->public ? 'Sim' : 'Não'; ?></td>
                        <td>
                            <button class="delete-code" data-code-id="<?php echo esc_attr($code->id); ?>">Excluir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
