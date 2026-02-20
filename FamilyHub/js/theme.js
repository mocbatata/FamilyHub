// Toggle de Tema Claro/Escuro
document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    
    // Procura pelo toggle (pode estar em diferentes páginas)
    const themeTogglePage = document.getElementById('theme-toggle-page');
    const themeToggleLogin = document.getElementById('theme-toggle-login');
    const themeToggleDashboard = document.getElementById('theme-toggle-dashboard');
    const themeToggleIndex = document.getElementById('theme-toggle-index');
    const themeToggleAtividades = document.getElementById('theme-toggle-atividades');
    const themeToggleMembros = document.getElementById('theme-toggle-membros');
    const themeToggleNotificacoes = document.getElementById('theme-toggle-notificacoes');
    const themeToggleOld = document.getElementById('theme-toggle');
    const themeToggle = themeTogglePage || themeToggleLogin || themeToggleDashboard || themeToggleIndex || themeToggleAtividades || themeToggleMembros || themeToggleNotificacoes || themeToggleOld;
    
    if (!themeToggle) return; // Se não encontrar nenhum toggle, sai
    
    // Verificar preferência salva
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-theme');
        themeToggle.checked = true;
    }
    
    // Adicionar evento de mudança
    themeToggle.addEventListener('change', function() {
        if (this.checked) {
            body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
        }
    });
});
