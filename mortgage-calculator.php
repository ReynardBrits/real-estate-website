<?php
require_once "includes/db.php";
require_once "includes/functions.php";

$propertyPrice = $_GET['price'] ?? "";

require_once "includes/header.php";
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Mortgage Calculator</h1>
            <p style="margin-bottom: 20px;">
                Estimate your monthly home loan repayment based on the property price, deposit, interest rate and loan term.
            </p>

            <form id="mortgageCalculatorForm">
                <div class="form-group">
                    <input 
                        type="number" 
                        id="propertyPrice" 
                        placeholder="Property Price e.g. 1500000" 
                        value="<?= e($propertyPrice); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="number" 
                        id="deposit" 
                        placeholder="Deposit e.g. 150000" 
                        value="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="number" 
                        step="0.01" 
                        id="interestRate" 
                        placeholder="Annual Interest Rate e.g. 11.75" 
                        value="11.75"
                        required
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="number" 
                        id="loanTerm" 
                        placeholder="Loan Term in Years e.g. 20" 
                        value="20"
                        required
                    >
                </div>

                <button class="btn" type="submit">Calculate</button>
            </form>
        </div>

        <div class="panel" id="mortgageResult" style="display: none;">
            <h2>Estimated Monthly Repayment</h2>
            <p class="price" id="monthlyPayment">R0.00</p>

            <br>

            <p><strong>Loan Amount:</strong> <span id="loanAmount">R0.00</span></p>
            <p><strong>Total Repayment:</strong> <span id="totalRepayment">R0.00</span></p>
            <p><strong>Total Interest:</strong> <span id="totalInterest">R0.00</span></p>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>