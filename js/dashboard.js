$(document).ready(function() {
    loadTransactions();
    updateSummary();
    
    $('#date').val(new Date().toISOString().split('T')[0]);
    
    $('#transaction-form').on('submit', function(e) {
        e.preventDefault();
        addTransaction();
    });
});

function addTransaction() {
    const formData = {
        type: $('#type').val(),
        category: $('#category').val(),
        amount: $('#amount').val(),
        description: $('#description').val(),
        date: $('#date').val()
    };
    
    $.post('api/add_transaction.php', formData)
        .done(function(response) {
            if (response.success) {
                $('#transaction-form')[0].reset();
                $('#date').val(new Date().toISOString().split('T')[0]);
                loadTransactions();
                updateSummary();
            } else {
                alert('Error adding transaction');
            }
        });
}

function loadTransactions() {
    $.get('api/get_transactions.php')
        .done(function(data) {
            const transactions = data.transactions || [];
            let html = '';
            
            transactions.forEach(function(transaction) {
                html += `
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <h4>${transaction.category}</h4>
                            <p>${transaction.description} - ${transaction.date}</p>
                        </div>
                        <div class="transaction-amount ${transaction.type}">
                            ${transaction.type === 'income' ? '+' : '-'}$${parseFloat(transaction.amount).toFixed(2)}
                        </div>
                    </div>
                `;
            });
            
            $('#transactions').html(html || '<p>No transactions yet</p>');
        });
}

function updateSummary() {
    $.get('api/get_summary.php')
        .done(function(data) {
            $('#total-income').text('$' + parseFloat(data.income || 0).toFixed(2));
            $('#total-expenses').text('$' + parseFloat(data.expenses || 0).toFixed(2));
            $('#balance').text('$' + parseFloat((data.income || 0) - (data.expenses || 0)).toFixed(2));
        });
}