document.addEventListener("DOMContentLoaded", function() {
    const hero = document.querySelector(".hero");
    
    setTimeout(() => {
        hero.style.opacity = "1";
        hero.style.transform = "translateY(0)";
    }, 500);
});
