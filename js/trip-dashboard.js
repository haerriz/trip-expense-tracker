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
    
    $('#export-xlsx').on('click', function() {
        exportToXLSX();
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
    
    // Typing indicator
    let typingTimer;
    $('#chat-message').on('input', function() {
        const tripId = $('#current-trip').val();
        if (!tripId) return;
        
        // Send typing status
        $.post('api/chat_status.php', { trip_id: tripId, action: 'typing' })
            .fail(function() { console.log('Typing status failed'); });
        
        // Clear previous timer
        clearTimeout(typingTimer);
        
        // Stop typing after 2 seconds of inactivity
        typingTimer = setTimeout(function() {
            $.post('api/chat_status.php', { trip_id: tripId, action: 'stop_typing' })
                .fail(function() { console.log('Stop typing failed'); });
        }, 2000);
    });
    
    $('input[name="split"]').on('change', function() {
        if ($(this).val() === 'custom') {
            loadCustomSplitSection();
            $('#custom-split-section').show();
        } else {
            $('#custom-split-section').hide();
        }
    });
    
    // Start live chat updates
    startLiveChat();
});

// Live chat variables
let chatInterval;
let statusInterval;
let heartbeatInterval;

function startLiveChat() {
    const tripId = $('#current-trip').val();
    if (!tripId) return;
    
    // Clear existing intervals
    clearInterval(chatInterval);
    clearInterval(statusInterval);
    clearInterval(heartbeatInterval);
    
    // Auto-refresh chat every 3 seconds
    chatInterval = setInterval(function() {
        loadTripChat(tripId);
    }, 3000);
    
    // Update status every 2 seconds
    statusInterval = setInterval(function() {
        updateChatStatus(tripId);
    }, 2000);
    
    // Send heartbeat every 15 seconds
    heartbeatInterval = setInterval(function() {
        $.post('api/chat_status.php', { trip_id: tripId, action: 'heartbeat' });
    }, 15000);
}

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
    startLiveChat();
}

function showEmptyState() {
    $('#trip-dashboard').hide();
    $('#no-trip').show();
}

function loadTripSummary(tripId) {
    $.get('api/get_trip_summary.php', { trip_id: tripId })
        .done(function(data) {
            if (data.success && data.trip) {
                const currency = getCurrencySymbol(data.trip.currency || 'USD');
                $('#trip-budget').text(currency + parseFloat(data.trip.budget || 0).toFixed(2));
                $('#total-spent').text(currency + parseFloat(data.total_expenses || 0).toFixed(2));
                $('#remaining-budget').text(currency + parseFloat(data.remaining_budget || 0).toFixed(2));
                $('#my-share').text(currency + parseFloat(data.per_person_share || 0).toFixed(2));
            }
        })
        .fail(function() {
            console.log('Failed to load trip summary');
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
    $.get('api/get_expenses.php', { trip_id: tripId })
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
        })
        .fail(function() {
            console.log('Failed to load expenses');
        });
}

let expenseChart = null;

function loadExpenseChart(tripId) {
    $.get('api/get_expense_breakdown.php', { trip_id: tripId })
        .done(function(data) {
            const ctx = document.getElementById('expenseChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (expenseChart) {
                expenseChart.destroy();
            }
            
            expenseChart = new Chart(ctx, {
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
        M.toast({html: 'Please select a trip first'});
        return;
    }
    
    const splitType = $('input[name="split"]:checked').val();
    const totalAmount = parseFloat($('#amount').val()) || 0;
    
    // Validate custom split
    if (splitType === 'custom') {
        const members = window.tripMembers || [];
        let splitTotal = 0;
        let customSplits = {};
        let hasCustomValues = false;
        
        members.forEach(function(member) {
            const amount = parseFloat($(`#split_${member.id}`).val()) || 0;
            if (amount > 0) {
                splitTotal += amount;
                customSplits[member.id] = amount;
                hasCustomValues = true;
            }
        });
        
        if (!hasCustomValues) {
            M.toast({html: 'Please enter custom split amounts'});
            return;
        }
        
        if (Math.abs(splitTotal - totalAmount) > 0.01) {
            M.toast({html: 'Custom split amounts must equal the total expense amount'});
            return;
        }
    }
    
    const formData = {
        trip_id: tripId,
        category: $('#category').val(),
        subcategory: $('#subcategory').val(),
        amount: $('#amount').val(),
        description: $('#description').val(),
        date: $('#date').val(),
        split_type: splitType
    };
    
    // Add custom splits if applicable
    if (splitType === 'custom') {
        const members = window.tripMembers || [];
        members.forEach(function(member) {
            const amount = parseFloat($(`#split_${member.id}`).val()) || 0;
            if (amount > 0) {
                formData[`split_${member.id}`] = amount;
            }
        });
    }
    
    $.post('api/add_expense.php', formData)
        .done(function(response) {
            if (response.success) {
                $('#expense-form')[0].reset();
                $('#custom-split-section').hide();
                $('#date').val(new Date().toISOString().split('T')[0]);
                loadTripDashboard(tripId);
                M.toast({html: 'Expense added successfully!'});
            } else {
                M.toast({html: response.message || 'Error adding expense'});
            }
        })
        .fail(function() {
            M.toast({html: 'Network error adding expense'});
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
    
    // Stop typing indicator
    $.post('api/chat_status.php', { trip_id: tripId, action: 'stop_typing' });
    
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

function exportToXLSX() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        M.toast({html: 'Please select a trip first'});
        return;
    }
    window.open('api/export_xlsx.php?trip_id=' + tripId, '_blank');
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

function loadCustomSplitSection() {
    const tripId = $('#current-trip').val();
    const totalAmount = parseFloat($('#amount').val()) || 0;
    
    if (!tripId) return;
    
    $.get('api/get_trip_members.php', { trip_id: tripId })
        .done(function(data) {
            const members = data.members || [];
            const equalSplit = totalAmount / members.length;
            
            let html = `
                <div class="custom-split-header">
                    <h6>Custom Split Amounts</h6>
                    <div class="split-info">
                        <span>Total: <strong id="split-total">$${totalAmount.toFixed(2)}</strong></span>
                        <span>Remaining: <strong id="split-remaining">$${totalAmount.toFixed(2)}</strong></span>
                    </div>
                    <div class="split-actions">
                        <button type="button" class="btn-small" onclick="splitEqually()">Split Equally</button>
                        <button type="button" class="btn-small" onclick="clearSplits()">Clear All</button>
                    </div>
                </div>
                <div class="custom-split-members">
            `;
            
            members.forEach(function(member, index) {
                html += `
                    <div class="split-member-row">
                        <div class="member-info">
                            <img src="${member.picture || generateAvatar(member.name)}" class="split-avatar circle" alt="${member.name}">
                            <span class="member-name">${member.name}</span>
                        </div>
                        <div class="input-field split-input">
                            <input type="number" id="split_${member.id}" class="validate split-amount" 
                                   step="0.01" min="0" max="${totalAmount}" 
                                   data-member-id="${member.id}" data-member-name="${member.name}"
                                   onchange="updateSplitCalculation()" onkeyup="updateSplitCalculation()">
                            <label for="split_${member.id}">Amount</label>
                        </div>
                        <div class="split-percentage">
                            <span id="percent_${member.id}">0%</span>
                        </div>
                    </div>
                `;
            });
            
            html += `
                </div>
                <div class="split-validation" id="split-validation" style="display:none;">
                    <div class="card-panel red lighten-4">
                        <i class="material-icons left">warning</i>
                        <span id="split-error"></span>
                    </div>
                </div>
            `;
            
            $('#member-splits').html(html);
            
            // Store members data for calculations
            window.tripMembers = members;
            window.totalExpenseAmount = totalAmount;
        });
}

function splitEqually() {
    const totalAmount = parseFloat($('#amount').val()) || 0;
    const members = window.tripMembers || [];
    const equalAmount = totalAmount / members.length;
    
    members.forEach(function(member) {
        $(`#split_${member.id}`).val(equalAmount.toFixed(2));
    });
    
    updateSplitCalculation();
    M.updateTextFields();
}

function clearSplits() {
    const members = window.tripMembers || [];
    members.forEach(function(member) {
        $(`#split_${member.id}`).val('');
    });
    updateSplitCalculation();
    M.updateTextFields();
}

function updateSplitCalculation() {
    const totalAmount = parseFloat($('#amount').val()) || 0;
    const members = window.tripMembers || [];
    let splitTotal = 0;
    let hasValues = false;
    
    members.forEach(function(member) {
        const amount = parseFloat($(`#split_${member.id}`).val()) || 0;
        splitTotal += amount;
        
        if (amount > 0) {
            hasValues = true;
            const percentage = totalAmount > 0 ? (amount / totalAmount * 100).toFixed(1) : 0;
            $(`#percent_${member.id}`).text(percentage + '%');
        } else {
            $(`#percent_${member.id}`).text('0%');
        }
    });
    
    const remaining = totalAmount - splitTotal;
    $('#split-total').text('$' + totalAmount.toFixed(2));
    $('#split-remaining').text('$' + remaining.toFixed(2));
    
    // Validation
    if (hasValues) {
        if (Math.abs(remaining) > 0.01) {
            $('#split-validation').show();
            if (remaining > 0) {
                $('#split-error').text(`$${remaining.toFixed(2)} remaining to be allocated`);
            } else {
                $('#split-error').text(`Over-allocated by $${Math.abs(remaining).toFixed(2)}`);
            }
            $('#split-remaining').addClass('red-text');
        } else {
            $('#split-validation').hide();
            $('#split-remaining').removeClass('red-text');
        }
    } else {
        $('#split-validation').hide();
        $('#split-remaining').removeClass('red-text');
    }
}

// Update total when amount changes
$('#amount').on('input change', function() {
    if ($('input[name="split"]:checked').val() === 'custom') {
        loadCustomSplitSection();
    }
});

function updateChatStatus(tripId) {
    $.get('api/chat_status.php', { trip_id: tripId })
        .done(function(data) {
            if (data.success === false) {
                console.log('Chat status error:', data.error);
                return;
            }
            
            // Update typing indicator
            if (data.typing && data.typing.length > 0) {
                const typingNames = data.typing.map(t => t.name).join(', ');
                $('#typing-user').text(typingNames);
                $('#typing-indicator').show();
            } else {
                $('#typing-indicator').hide();
            }
            
            // Update online members
            let onlineHtml = '';
            if (data.online && data.online.length > 0) {
                data.online.forEach(function(member) {
                    onlineHtml += `
                        <span class="chip">
                            <span class="online-indicator"></span>
                            ${member.name}
                        </span>
                    `;
                });
                $('#online-status').text(`${data.online.length} online`);
            } else {
                $('#online-status').text('Offline');
            }
            $('#online-members').html(onlineHtml);
        })
        .fail(function(xhr, status, error) {
            console.log('Chat status request failed:', error);
            $('#online-status').text('Connection error');
        });
}