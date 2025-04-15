document.addEventListener('DOMContentLoaded', function () {
    // Sélectionner le bouton de bascule pour changer le thème
    const themeToggleButton = document.getElementById('theme-toggle');
    
    // Vérifiez si l'utilisateur a déjà un mode préféré enregistré dans le localStorage
    if (localStorage.getItem('theme') === 'dark') {
        // Si le thème préféré est 'dark', appliquez la classe dark-theme à l'élément body
        document.body.classList.add('dark-theme');
        // Changez le texte du bouton pour indiquer l'option "Mode Clair" 
        themeToggleButton.textContent = 'Mode Clair';
    } else {
        // Si le mode clair est sélectionné par défaut, affichez "Mode Nuit"
        themeToggleButton.textContent = 'Mode Nuit';
    }

    // Ajoutez un écouteur d'événements pour changer le thème lorsque l'utilisateur clique sur le bouton
    themeToggleButton.addEventListener('click', function () {
        // Basculez la classe dark-theme sur le body (ajoute ou supprime)
        document.body.classList.toggle('dark-theme');
        
        // Mémorisez la préférence de l'utilisateur dans le localStorage
        if (document.body.classList.contains('dark-theme')) {
            // Si le mode sombre est activé, enregistrez 'dark' dans le localStorage
            localStorage.setItem('theme', 'dark');
            // Modifiez le texte du bouton pour indiquer l'option "Mode Clair"
            themeToggleButton.textContent = "Mode Clair";
        } else {
            // Si le mode sombre est désactivé, enregistrez 'light' dans le localStorage
            localStorage.setItem('theme', 'light');
            // Modifiez le texte du bouton pour indiquer l'option "Mode Nuit"
            themeToggleButton.textContent = 'Mode Nuit';
        }
    });
});
