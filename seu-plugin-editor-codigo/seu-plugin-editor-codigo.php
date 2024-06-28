<?php
/**
 * Plugin Name: Seu Plugin Editor de Código
 * Description: Um plugin para adicionar um editor de código no front-end usando um shortcode.
 * Version: 1.0
 * Author: Seu Nome
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Requerer arquivos de dependências
require_once plugin_dir_path(__FILE__) . 'includes/class-seu-plugin-editor-codigo.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-seu-plugin-editor-codigo-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-seu-plugin-comunidade-wd.php'; // Novo arquivo
require_once plugin_dir_path(__FILE__) . 'includes/class-seu-plugin-editor-codigo-admin.php'; // Novo arquivo

// Hooks de ativação e desativação do plugin
register_activation_hook(__FILE__, array('Seu_Plugin_Editor_Codigo_DB', 'ativar'));
register_deactivation_hook(__FILE__, array('Seu_Plugin_Editor_Codigo_DB', 'desativar'));

// Função para enfileirar scripts dos pré-processadores
function load_preprocessors_cdn() {
    wp_enqueue_script('pug', 'https://cdnjs.cloudflare.com/ajax/libs/pug/3.0.2/pug.min.js', array(), null, true);
    wp_enqueue_script('stylus', 'https://cdnjs.cloudflare.com/ajax/libs/stylus/0.54.8/stylus.min.js', array(), null, true);
    wp_enqueue_script('scss', 'https://cdnjs.cloudflare.com/ajax/libs/sass.js/0.11.1/sass.sync.min.js', array(), null, true);
    wp_enqueue_script('babel', 'https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'load_preprocessors_cdn');

// Inicializar o plugin
function run_seu_plugin() {
    $plugin = new Seu_Plugin_Editor_Codigo();
    $plugin->run();
}
add_action('plugins_loaded', 'run_seu_plugin');

// Injetar CSS personalizado para o editor ACE
function inject_ace_custom_css() {
    ?>
    <style>
    .ace_editor {
        font-family: 'Fira Code', monospace !important;
        background-color: #1e1e1e !important;
        color: #dcdcdc !important;
        font-size: 16px !important;
        line-height: 1.5 !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_cursor {
        color: #ffffff !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_marker-layer .ace_selection {
        background: rgba(255, 255, 255, 0.10) !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_marker-layer .ace_active-line {
        background: #2a2a2a !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_gutter {
        background: #2a2a2a !important;
        color: #8F908A !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_gutter-cell {
        color: #8F908A !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_keyword {
        color: #C594C5 !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_string {
        color: #99C794 !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_comment {
        color: #65737E !important;
        font-style: italic !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_constant.ace_numeric {
        color: #F99157 !important;
        z-index: 9999 !important;
    }
    .ace_editor .ace_support.ace_function {
        color: #6699CC !important;
        z-index: 9999 !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'inject_ace_custom_css');
?>
