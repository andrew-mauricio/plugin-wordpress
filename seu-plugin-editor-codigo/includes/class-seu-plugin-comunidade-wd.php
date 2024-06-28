<?php
class Seu_Plugin_Comunidade_WD {

    public function __construct() {
        add_shortcode('comunidade_wd', array($this, 'render_comunidade_wd'));
    }

    public function render_comunidade_wd($atts) {
        ob_start();
        $this->comunidade_wd_content();
        return ob_get_clean();
    }

    private function comunidade_wd_content() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_codes';
        $codes = $wpdb->get_results("SELECT * FROM $table_name WHERE is_public = 1 ORDER BY created_at DESC");

        ?>
        <div id="comunidade-wd-container">
            <div class="barra-superior">
                <a href="<?php echo site_url('/path-to-editor-page'); ?>">Voltar para o Editor de Códigos</a>
            </div>
            <?php foreach ($codes as $code): ?>
                <div class="codigo-container">
                    <div class="editor-bloco">
                        <h3>HTML</h3>
                        <iframe srcdoc="<?php echo base64_decode($code->html_code); ?>" width="500" height="300"></iframe>
                    </div>
                    <div class="editor-bloco">
                        <h3>CSS</h3>
                        <iframe srcdoc="<style><?php echo base64_decode($code->css_code); ?></style>" width="500" height="300"></iframe>
                    </div>
                    <div class="editor-bloco">
                        <h3>JavaScript</h3>
                        <iframe srcdoc="<script><?php echo base64_decode($code->js_code); ?></script>" width="500" height="300"></iframe>
                    </div>
                    <div class="visualizacao-bloco">
                        <h3>Visualização</h3>
                        <iframe width="100%" height="300"></iframe>
                    </div>
                    <div class="info-bloco">
                        <h3>Informações</h3>
                        <p>ID do Usuário: <?php echo $code->user_id; ?></p>
                        <p>Nome do Usuário: <?php echo get_userdata($code->user_id)->user_login; ?></p>
                        <p>Data da Criação: <?php echo $code->created_at; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <style>
            #comunidade-wd-container {
                padding: 20px;
                background-color: #1d1e22;
                color: #fff;
            }
            .barra-superior {
                background-color: #2e2e2e;
                padding: 10px;
                text-align: center;
                position: fixed;
                width: 100%;
                top: 0;
                z-index: 1000;
            }
            .barra-superior a {
                color: #fff;
                text-decoration: none;
                font-size: 18px;
            }
            .codigo-container {
                margin-top: 60px;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            .editor-bloco, .visualizacao-bloco, .info-bloco {
                background-color: #2e2e2e;
                padding: 10px;
                margin: 10px;
                width: calc(33% - 20px);
                box-sizing: border-box;
                height: 320px;
            }
            .visualizacao-bloco, .info-bloco {
                width: calc(50% - 20px);
            }
        </style>
        <script>
            document.querySelectorAll('.visualizacao-bloco iframe').forEach((iframe, index) => {
                const html = document.querySelectorAll('.editor-bloco iframe')[index * 3].srcdoc;
                const css = document.querySelectorAll('.editor-bloco iframe')[index * 3 + 1].srcdoc;
                const js = document.querySelectorAll('.editor-bloco iframe')[index * 3 + 2].srcdoc;
                iframe.srcdoc = html + '<style>' + css + '</style>' + '<script>' + js + '<\/script>';
            });
        </script>
        <?php
    }
}

new Seu_Plugin_Comunidade_WD();
?>
