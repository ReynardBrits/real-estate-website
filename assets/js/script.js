document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menuToggle");
    const mainNav = document.getElementById("mainNav");

    if (menuToggle && mainNav) {
        menuToggle.addEventListener("click", function () {
            mainNav.classList.toggle("active");
        });
    }

    const liveSearch = document.getElementById("liveSearch");

    if (liveSearch) {
        liveSearch.addEventListener("input", function () {
            const searchValue = liveSearch.value.toLowerCase();
            const cards = document.querySelectorAll(".property-card");

            cards.forEach(function (card) {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchValue) ? "block" : "none";
            });
        });
    }

    const enquiryForm = document.getElementById("enquiryForm");

    if (enquiryForm) {
        enquiryForm.addEventListener("submit", function (event) {
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const message = document.getElementById("message").value.trim();

            if (name === "" || email === "" || message === "") {
                event.preventDefault();
                alert("Please fill in all fields.");
            } else if (!email.includes("@")) {
                event.preventDefault();
                alert("Please enter a valid email address.");
            }
        });
    }

    const galleryImages = document.querySelectorAll(".gallery-thumb");
    const mainImage = document.getElementById("mainPropertyImage");

    if (galleryImages.length > 0 && mainImage) {
        galleryImages.forEach(function (image) {
            image.addEventListener("click", function () {
                mainImage.src = image.src;
            });
        });
    }
});