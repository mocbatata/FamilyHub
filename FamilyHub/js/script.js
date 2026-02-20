// FamilyHub - Script Principal

document.addEventListener('DOMContentLoaded', function () {

    // === Toggle Formulário de Atividades ===
    const btnNovaAtividade = document.getElementById('btn-nova-atividade');
    const formAtividade = document.getElementById('form-atividade');
    const btnCancelarAtividade = document.getElementById('btn-cancelar');

    if (btnNovaAtividade && formAtividade) {
        btnNovaAtividade.addEventListener('click', function () {
            formAtividade.style.display = formAtividade.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (btnCancelarAtividade && formAtividade) {
        btnCancelarAtividade.addEventListener('click', function () {
            formAtividade.style.display = 'none';
        });
    }

    // === Toggle Formulário de Membros ===
    const btnNovoMembro = document.getElementById('btn-novo-membro');
    const formMembro = document.getElementById('form-membro');
    const btnCancelarMembro = document.getElementById('btn-cancelar-membro');

    if (btnNovoMembro && formMembro) {
        btnNovoMembro.addEventListener('click', function () {
            formMembro.style.display = formMembro.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (btnCancelarMembro && formMembro) {
        btnCancelarMembro.addEventListener('click', function () {
            formMembro.style.display = 'none';
        });
    }
});
