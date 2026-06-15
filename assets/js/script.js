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

    const mortgageForm = document.getElementById("mortgageCalculatorForm");

    
if (mortgageForm) {
    mortgageForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const propertyPrice = parseFloat(document.getElementById("propertyPrice").value);
        const deposit = parseFloat(document.getElementById("deposit").value);
        const interestRate = parseFloat(document.getElementById("interestRate").value);
        const loanTerm = parseFloat(document.getElementById("loanTerm").value);

        if (
            isNaN(propertyPrice) ||
            isNaN(deposit) ||
            isNaN(interestRate) ||
            isNaN(loanTerm) ||
            propertyPrice <= 0 ||
            deposit < 0 ||
            loanTerm <= 0
        ) {
            alert("Please enter valid calculator values.");
            return;
        }

        const loanAmount = propertyPrice - deposit;

        if (loanAmount <= 0) {
            alert("Deposit cannot be equal to or greater than the property price.");
            return;
        }

        const monthlyInterestRate = interestRate / 100 / 12;
        const numberOfPayments = loanTerm * 12;

        let monthlyPayment;

        if (monthlyInterestRate === 0) {
            monthlyPayment = loanAmount / numberOfPayments;
        } else {
            monthlyPayment =
                loanAmount *
                (monthlyInterestRate * Math.pow(1 + monthlyInterestRate, numberOfPayments)) /
                (Math.pow(1 + monthlyInterestRate, numberOfPayments) - 1);
        }

        const totalRepayment = monthlyPayment * numberOfPayments;
        const totalInterest = totalRepayment - loanAmount;

        document.getElementById("monthlyPayment").textContent = formatCurrency(monthlyPayment);
        document.getElementById("loanAmount").textContent = formatCurrency(loanAmount);
        document.getElementById("totalRepayment").textContent = formatCurrency(totalRepayment);
        document.getElementById("totalInterest").textContent = formatCurrency(totalInterest);

        document.getElementById("mortgageResult").style.display = "block";
    });
}

function formatCurrency(amount) {
    return "R" + amount.toLocaleString("en-ZA", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
});