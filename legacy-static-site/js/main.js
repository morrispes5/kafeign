document.addEventListener("DOMContentLoaded", () => {
 
    const body = document.body;
    const themeToggle = document.querySelector(".toggle-theme");
    const savedTheme = localStorage.getItem("theme");

    if (savedTheme) {
        body.classList.add(savedTheme);
        themeToggle.textContent = savedTheme === "dark-mode" ? "☀️" : "🌙";
    }

    themeToggle.addEventListener("click", () => {
        const isDarkMode = body.classList.toggle("dark-mode");
        localStorage.setItem("theme", isDarkMode ? "dark-mode" : "light-mode");
        themeToggle.textContent = isDarkMode ? "☀️" : "🌙";
    });

    
    const navToggle = document.querySelector(".nav-toggle");
    const sidebar = document.querySelector(".sidebar");
    const closeBtn = document.querySelector(".close-btn");

    navToggle.addEventListener("click", () => {
        sidebar.classList.add("active");
    });

    closeBtn.addEventListener("click", () => {
        sidebar.classList.remove("active");
    });
});