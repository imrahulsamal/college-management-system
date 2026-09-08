const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebarOverlay = document.getElementById("sidebarOverlay");

sidebarToggle.addEventListener("click", function () {
    sidebar.classList.toggle("show");
    sidebarOverlay.classList.toggle("show");
});

sidebarOverlay.addEventListener("click", function () {
    sidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
});