document.addEventListener('DOMContentLoaded', function () {
    const themeToggleButton = document.getElementById('theme-toggle');
    
    // Vérifiez si l'utilisateur a déjà un mode préféré
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
        themeToggleButton.textContent = 'Mode Clair'; // Texte du bouton modifié
    } else {
        themeToggleButton.textContent = 'Mode Nuit'; // Si le mode clair est sélectionné par défaut
    }

    // Changez le thème au clic
    themeToggleButton.addEventListener('click', function () {
        // Basculez la classe dark-theme
        document.body.classList.toggle('dark-theme');
        
        // Mémorisez la préférence de l'utilisateur dans localStorage
        if (document.body.classList.contains('dark-theme')) {
            localStorage.setItem('theme', 'dark');
            themeToggleButton.textContent = "Mode Clair";
        } else {
            localStorage.setItem('theme', 'light');
            themeToggleButton.textContent = 'Mode Nuit';
        }
    });
});
