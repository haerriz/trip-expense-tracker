<?php
session_start();
// Mock session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_name'] = 'Test User';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Frontend Debug</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Frontend Debug Test</h2>
    <div id="results"></div>
    
    <script>
    function getCurrencySymbol(currency) {
        const symbols = {
            'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'AUD': 'A$',
            'CAD': 'C$', 'INR': '₹', 'THB': '฿', 'VND': '₫'
        };
        return symbols[currency] || currency;
    }
    
    // Test the API call
    $.get('api/get_trip_summary.php', { trip_id: 2 })
        .done(function(data) {
            console.log('API Response:', data);
            $('#results').html('<h3>API Response:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>');
            
            if (data.success && data.trip) {
                const currency = getCurrencySymbol(data.trip.currency || 'USD');
                const budget = data.trip.budget;
                
                $('#results').append('<h3>Processed Values:</h3>');
                $('#results').append('<p>Currency Symbol: ' + currency + '</p>');
                $('#results').append('<p>Budget: ' + currency + parseFloat(budget || 0).toFixed(2) + '</p>');
                $('#results').append('<p>Total Expenses: ' + currency + parseFloat(data.total_expenses || 0).toFixed(2) + '</p>');
                $('#results').append('<p>Remaining: ' + currency + parseFloat(data.remaining_budget || 0).toFixed(2) + '</p>');
                $('#results').append('<p>Per Person: ' + currency + parseFloat(data.per_person_share || 0).toFixed(2) + '</p>');
            }
        })
        .fail(function(xhr, status, error) {
            console.log('API Error:', xhr.responseText);
            $('#results').html('<h3>API Error:</h3><pre>' + xhr.responseText + '</pre>');
        });
    </script>
</body>
</html>