$(document).ready(function() {
    loadTrips();
    loadCategories();
    
    $('#date').val(new Date().toISOString().split('T')[0]);
    
    // Load last selected trip from localStorage
    const lastTripId = localStorage.getItem('selectedTripId');
    
    $('#current-trip').on('change', function() {
        const tripId = $(this).val();
        localStorage.setItem('selectedTripId', tripId);
        if (tripId) {
            loadTripDashboard(tripId);
        } else {
            showEmptyState();
        }
    });
    
    $('#category').on('change', function() {
        loadSubcategories($(this).val());
    });
    
    $('#new-trip-btn').on('click', function() {
        $('#trip-modal').show();
    });
    
    $('.close').on('click', function() {
        $('#trip-modal').hide();
    });
    
    $('#trip-form').on('submit', function(e) {
        e.preventDefault();
        createTrip();
    });
    
    $('#expense-form').on('submit', function(e) {
        e.preventDefault();
        addExpense();
    });
    
    $('#invite-btn').on('click', function() {
        inviteMember();
    });
});

function loadTrips() {
    $.get('api/get_trips.php')
        .done(function(data) {
            const trips = data.trips || [];
            let options = '<option value="">Select a Trip</option>';
            
            trips.forEach(function(trip) {
                const currency = getCurrencySymbol(trip.currency || 'USD');
                options += `<option value="${trip.id}" data-currency="${trip.currency || 'USD'}">${trip.name} (${currency})</option>`;
            });
            
            $('#current-trip').html(options);
            
            // Auto-select last trip
            const lastTripId = localStorage.getItem('selectedTripId');
            if (lastTripId && trips.find(t => t.id == lastTripId)) {
                $('#current-trip').val(lastTripId).trigger('change');
            } else if (trips.length === 0) {
                showEmptyState();
            }
        });
}

function loadCategories() {
    $.get('api/get_categories.php')
        .done(function(data) {
            const categories = data.categories || [];
            let options = '<option value="">Select Category</option>';
            
            categories.forEach(function(category) {
                options += `<option value="${category.name}">${category.name}</option>`;
            });
            
            $('#category').html(options);
        });
}

function getCurrencySymbol(currency) {
    const symbols = {
        'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'AUD': 'A$',
        'CAD': 'C$', 'INR': '₹', 'THB': '฿', 'VND': '₫'
    };
    return symbols[currency] || currency;
}

function loadSubcategories(category) {
    const subcategories = {
        'Food & Drinks': ['Restaurant', 'Street Food', 'Groceries', 'Drinks', 'Snacks'],
        'Transportation': ['Flight', 'Train', 'Bus', 'Taxi', 'Rental Car', 'Fuel', 'Parking'],
        'Accommodation': ['Hotel', 'Hostel', 'Airbnb', 'Camping', 'Guesthouse'],
        'Activities': ['Tours', 'Museums', 'Adventure Sports', 'Nightlife', 'Events'],
        'Shopping': ['Souvenirs', 'Clothes', 'Electronics', 'Gifts'],
        'Emergency': ['Medical', 'Insurance', 'Lost Items', 'Emergency Transport'],
        'Other': ['Tips', 'Fees', 'Miscellaneous']
    };
    
    let options = '<option value="">Select Subcategory</option>';
    if (subcategories[category]) {
        subcategories[category].forEach(function(sub) {
            options += `<option value="${sub}">${sub}</option>`;
        });
    }
    $('#subcategory').html(options);
}

function createTrip() {
    const formData = {
        name: $('#trip-name').val(),
        description: $('#trip-description').val(),
        start_date: $('#start-date').val(),
        end_date: $('#end-date').val(),
        budget: $('#budget').val(),
        currency: $('#currency').val()
    };
    
    $.post('api/create_trip.php', formData)
        .done(function(response) {
            if (response.success) {
                $('#trip-modal').hide();
                $('#trip-form')[0].reset();
                loadTrips();
            } else {
                alert('Error creating trip');
            }
        });
}

function loadTripDashboard(tripId) {
    $('#no-trip').hide();
    $('#trip-dashboard').show();
    
    loadTripSummary(tripId);
    loadTripMembers(tripId);
    loadExpenses(tripId);
    loadExpenseChart(tripId);
}

function showEmptyState() {
    $('#trip-dashboard').hide();
    $('#no-trip').show();
}

function loadTripSummary(tripId) {
    $.get('api/get_trip_summary.php', { trip_id: tripId })
        .done(function(data) {
            const currency = getCurrencySymbol(data.currency || 'USD');
            $('#trip-budget').text(currency + parseFloat(data.budget || 0).toFixed(2));
            $('#total-spent').text(currency + parseFloat(data.total_spent || 0).toFixed(2));
            $('#remaining-budget').text(currency + parseFloat((data.budget || 0) - (data.total_spent || 0)).toFixed(2));
            $('#my-share').text(currency + parseFloat(data.my_share || 0).toFixed(2));
        });
}

function loadTripMembers(tripId) {
    $.get('api/get_trip_members.php', { trip_id: tripId })
        .done(function(data) {
            const members = data.members || [];
            let html = '';
            
            members.forEach(function(member) {
                html += `
                    <div class="member-item">
                        <img src="${member.picture || 'https://via.placeholder.com/30'}" alt="${member.name}" class="member-pic">
                        <span>${member.name}</span>
                    </div>
                `;
            });
            
            $('#members-list').html(html);
        });
}

function loadExpenses(tripId) {
    $.get('api/get_trip_expenses.php', { trip_id: tripId })
        .done(function(data) {
            const expenses = data.expenses || [];
            let html = '';
            
            expenses.forEach(function(expense) {
                html += `
                    <div class="expense-item">
                        <div class="expense-info">
                            <h4>${expense.category}</h4>
                            <p>${expense.description} - ${expense.date}</p>
                            <small>Paid by ${expense.paid_by_name}</small>
                        </div>
                        <div class="expense-amount">$${parseFloat(expense.amount).toFixed(2)}</div>
                    </div>
                `;
            });
            
            $('#expenses-list').html(html || '<p>No expenses yet</p>');
        });
}

function loadExpenseChart(tripId) {
    $.get('api/get_expense_breakdown.php', { trip_id: tripId })
        .done(function(data) {
            const ctx = document.getElementById('expenseChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.categories || [],
                    datasets: [{
                        data: data.amounts || [],
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
}

function addExpense() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        alert('Please select a trip first');
        return;
    }
    
    const formData = {
        trip_id: tripId,
        category: $('#category').val(),
        subcategory: $('#subcategory').val(),
        amount: $('#amount').val(),
        description: $('#description').val(),
        date: $('#date').val(),
        split_type: $('input[name="split"]:checked').val()
    };
    
    $.post('api/add_expense.php', formData)
        .done(function(response) {
            if (response.success) {
                $('#expense-form')[0].reset();
                $('#date').val(new Date().toISOString().split('T')[0]);
                loadTripDashboard(tripId);
            } else {
                alert('Error adding expense');
            }
        });
}

function inviteMember() {
    const tripId = $('#current-trip').val();
    const email = $('#invite-email').val();
    
    if (!tripId || !email) {
        alert('Please select a trip and enter an email');
        return;
    }
    
    $.post('api/invite_member.php', { trip_id: tripId, email: email })
        .done(function(response) {
            if (response.success) {
                $('#invite-email').val('');
                loadTripMembers(tripId);
                alert('Member invited successfully');
            } else {
                alert(response.message || 'Error inviting member');
            }
        });
}