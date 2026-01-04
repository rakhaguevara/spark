/**
 * WALLET JAVASCRIPT
 * Handles payment method management
 */

// Open add payment modal
function openAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.add('active');
    document.getElementById('step1').classList.add('active');
    document.getElementById('step2').classList.remove('active');
}

// Close add payment modal
function closeAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.remove('active');
    document.getElementById('addPaymentForm').reset();
}

// Select payment type
function selectType(type) {
    document.getElementById('paymentType').value = type;
    document.getElementById('step1').classList.remove('active');
    document.getElementById('step2').classList.add('active');

    // Update placeholder based on type
    const accountInput = document.getElementById('accountNumber');
    const providerInput = document.getElementById('providerName');

    if (type === 'bank') {
        providerInput.placeholder = 'e.g., BCA, Mandiri, BNI';
        accountInput.placeholder = 'Account number';
    } else if (type === 'ewallet') {
        providerInput.placeholder = 'e.g., DANA, OVO, GoPay';
        accountInput.placeholder = 'Phone number or account ID';
    } else if (type === 'paypal') {
        providerInput.placeholder = 'PayPal';
        accountInput.placeholder = 'Email address';
    }
}

// Back to step 1
function backToStep1() {
    document.getElementById('step1').classList.add('active');
    document.getElementById('step2').classList.remove('active');
}

// Handle form submission
document.getElementById('addPaymentForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch(BASEURL + '/api/add-payment-method.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Payment method added successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add payment method');
    }
});

// Set default payment method
async function setDefaultPayment(id) {
    if (!confirm('Set this as your default payment method?')) return;

    try {
        const response = await fetch(BASEURL + '/api/set-default-payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id_wallet=' + id
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to set default payment method');
    }
}

// Remove payment method
async function removePayment(id) {
    if (!confirm('Are you sure you want to remove this payment method?')) return;

    try {
        const response = await fetch(BASEURL + '/api/remove-payment-method.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id_wallet=' + id
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to remove payment method');
    }
}

// Close modal on outside click
document.getElementById('addPaymentModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeAddPaymentModal();
    }
});
