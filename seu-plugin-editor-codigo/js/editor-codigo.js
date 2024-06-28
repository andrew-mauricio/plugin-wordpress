jQuery(document).ready(function ($) {

    // Inicialização dos editores ACE
    var htmlEditor = ace.edit("html-editor");
    htmlEditor.setTheme("ace/theme/monokai");
    htmlEditor.getSession().setMode("ace/mode/html");

    var cssEditor = ace.edit("css-editor");
    cssEditor.setTheme("ace/theme/monokai");
    cssEditor.getSession().setMode("ace/mode/css");

    var jsEditor = ace.edit("js-editor");
    jsEditor.setTheme("ace/theme/monokai");
    jsEditor.getSession().setMode("ace/mode/javascript");

    // Função para carregar os códigos do usuário
    function loadUserCodes() {
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_user_codes',
                nonce: ajax_object.nonce
            },
            success: function (response) {
                if (response.success) {
                    var codeList = $("#user-codes");
                    codeList.empty();
                    response.data.forEach(function (code) {
                        var listItem = $("<li>").text(code.code_name);
                        var toggleButton = $("<button>").text(code.public ? "Privado" : "Público")
                            .on("click", function () {
                                toggleCodeVisibility(code.id, code.public ? 0 : 1);
                            });
                        listItem.append(toggleButton);
                        codeList.append(listItem);
                    });
                } else {
                    alert(response.data);
                }
            }
        });
    }

    // Função para alternar a visibilidade do código
    function toggleCodeVisibility(codeId, isPublic) {
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_code_visibility',
                nonce: ajax_object.nonce,
                code_id: codeId,
                is_public: isPublic
            },
            success: function (response) {
                if (response.success) {
                    loadUserCodes();
                } else {
                    alert(response.data);
                }
            }
        });
    }

    // Carregar a lista de códigos do usuário na inicialização
    if ($("#user-codes").length) {
        loadUserCodes();
    }

    // Evento ao clicar no botão "Salvar Código"
    $("#save-code-button").on("click", function () {
        var codeName = $("#code-name").val();
        var htmlCode = htmlEditor.getValue();
        var cssCode = cssEditor.getValue();
        var jsCode = jsEditor.getValue();
        var isPublic = $("#public-toggle").is(":checked");

        // Codificar os dados em base64 antes de enviar
        htmlCode = btoa(htmlCode);
        cssCode = btoa(cssCode);
        jsCode = btoa(jsCode);

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_user_code',
                nonce: ajax_object.nonce,
                code_name: codeName,
                html_code: htmlCode,
                css_code: cssCode,
                js_code: jsCode,
                is_public: isPublic
            },
            success: function (response) {
                if (response.success) {
                    alert("Código salvo com sucesso!");
                    loadUserCodes();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Evento ao clicar no botão "Recuperar Código"
    $("#retrieve-code-button").on("click", function () {
        var codeName = $("#code-name").val();
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'retrieve_user_code',
                nonce: ajax_object.nonce,
                code_name: codeName
            },
            success: function (response) {
                if (response.success) {
                    // Decodificar os dados recebidos do banco de dados
                    var htmlCode = atob(response.data.html_code);
                    var cssCode = atob(response.data.css_code);
                    var jsCode = atob(response.data.js_code);
                    var isPublic = response.data.is_public;
                    var createdAt = response.data.created_at;

                    htmlEditor.setValue(htmlCode, -1);
                    cssEditor.setValue(cssCode, -1);
                    jsEditor.setValue(jsCode, -1);
                    $("#public-toggle").prop("checked", isPublic);
                    alert("Código recuperado: criado em " + createdAt);
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Função para renderizar a visualização no iframe
    function updatePreview() {
        var htmlCode = htmlEditor.getValue();
        var cssCode = '<style>' + cssEditor.getValue() + '</style>';
        var jsCode = '<script>' + jsEditor.getValue() + '<\/script>';
        var previewFrame = document.getElementById('preview-iframe');
        var preview = previewFrame.contentDocument || previewFrame.contentWindow.document;
        preview.open();
        preview.write(htmlCode + cssCode + jsCode);
        preview.close();
    }

    // Atualiza a visualização em tempo real
    htmlEditor.getSession().on('change', updatePreview);
    cssEditor.getSession().on('change', updatePreview);
    jsEditor.getSession().on('change', updatePreview);

    // Atualiza a visualização na inicialização
    updatePreview();

    // Função para exclusão de código
    $(".delete-code").on("click", function () {
        var codeId = $(this).data("code-id");
        if (confirm("Tem certeza que deseja excluir este código?")) {
            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_user_code',
                    nonce: ajax_object.nonce,
                    code_id: codeId
                },
                success: function (response) {
                    if (response.success) {
                        location.reload(); // Recarrega para atualizar a lista de códigos salvos
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
    });
});
