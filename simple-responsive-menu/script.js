document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.querySelector(".srm-toggle");
    const center = document.querySelector(".srm-center");

    if (toggle && center) {
        toggle.addEventListener("click", function() {
            center.classList.toggle("active");
        });
    }
});