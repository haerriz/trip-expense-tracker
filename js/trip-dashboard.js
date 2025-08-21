$(document).ready(function() {
    // Initialize Materialize components
    M.AutoInit();
    
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
    
    $('#new-trip-btn').on('click', function() {
        $('#trip-modal').modal('open');
    });
    
    $('#expense-form').on('submit', function(e) {
        e.preventDefault();
        addExpense();
    });
    
    $('#invite-btn').on('click', function() {
        inviteMember();
    });
    
    $('#export-pdf').on('click', function() {
        exportToPDF();
    });
    
    $('#export-excel').on('click', function() {
        exportToExcel();
    });
    
    $('#email-report').on('click', function() {
        emailReport();
    });
    
    $('#send-message').on('click', function() {
        sendChatMessage();
    });
    
    $('#chat-message').on('keypress', function(e) {
        if (e.which === 13) {
            sendChatMessage();
        }
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
            $('#current-trip').formSelect(); // Reinitialize Materialize select
            
            // Auto-select last trip
            const lastTripId = localStorage.getItem('selectedTripId');
            if (lastTripId && trips.find(t => t.id == lastTripId)) {
                $('#current-trip').val(lastTripId);
                $('#current-trip').formSelect();
                $('#current-trip').trigger('change');
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
            $('#category').formSelect();
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
    $('#subcategory').formSelect();
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
                $('#trip-modal').modal('close');
                $('#trip-form')[0].reset();
                loadTrips();
                M.toast({html: 'Trip created successfully!'});
            } else {
                M.toast({html: 'Error creating trip'});
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
    loadTripChat(tripId);
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
                    <div class="trip-members__member">
                        <img src="${member.picture && !member.picture.includes('placeholder') ? member.picture : generateAvatar(member.name)}" alt="${member.name}" class="trip-members__avatar circle">
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
                        <div class="expense-item__info">
                            <h6>${expense.category}</h6>
                            <p>${expense.description} - ${expense.date}</p>
                            <small>Paid by ${expense.paid_by_name}</small>
                        </div>
                        <div class="expense-item__amount">$${parseFloat(expense.amount).toFixed(2)}</div>
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
                M.toast({html: 'Expense added successfully!'});
            } else {
                M.toast({html: 'Error adding expense'});
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
                M.toast({html: 'Member invited successfully!'});
            } else {
                M.toast({html: response.message || 'Error inviting member'});
            }
        });
}

function loadTripChat(tripId) {
    $.get('api/get_chat.php', { trip_id: tripId })
        .done(function(data) {
            const messages = data.messages || [];
            let html = '';
            
            messages.forEach(function(msg) {
                const time = new Date(msg.created_at).toLocaleTimeString();
                html += `
                    <div class="chat-message">
                        <span class="chat-message__sender">${msg.sender_name}:</span>
                        <span class="chat-message__time">${time}</span>
                        <div class="chat-message__text">${msg.message}</div>
                    </div>
                `;
            });
            
            $('#chat-messages').html(html);
            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
        });
}

function sendChatMessage() {
    const tripId = $('#current-trip').val();
    const message = $('#chat-message').val().trim();
    
    if (!tripId || !message) return;
    
    $.post('api/send_chat.php', { trip_id: tripId, message: message })
        .done(function(response) {
            if (response.success) {
                $('#chat-message').val('');
                loadTripChat(tripId);
            }
        });
}

function exportToPDF() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        M.toast({html: 'Please select a trip first'});
        return;
    }
    window.open('api/export_pdf.php?trip_id=' + tripId, '_blank');
}

function exportToExcel() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        M.toast({html: 'Please select a trip first'});
        return;
    }
    window.open('api/export_excel.php?trip_id=' + tripId, '_blank');
}

function emailReport() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        M.toast({html: 'Please select a trip first'});
        return;
    }
    
    const email = prompt('Enter email address (leave blank to use your email):');
    if (email === null) return;
    
    $.post('api/email_report.php', { trip_id: tripId, email: email })
        .done(function(response) {
            if (response.success) {
                M.toast({html: 'Report sent successfully!'});
            } else {
                M.toast({html: 'Failed to send report: ' + (response.message || 'Unknown error')});
            }
        })
        .fail(function() {
            M.toast({html: 'Failed to send report'});
        });
}