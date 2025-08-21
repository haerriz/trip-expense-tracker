$(document).ready(function() {
    // Initialize Materialize components
    M.AutoInit();
    
    loadTrips();
    loadCategories();
    loadInvitations();
    
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
    
    $(document).on('change', 'input[name="split-mode"]', function() {
        loadCustomSplitSection();
    });
    
    // Start live chat updates
    startLiveChat();
    
    // Initialize invitation modal
    $('#invitations-modal').modal();
    
    $('#invitations-btn').on('click', function() {
        loadInvitations();
        $('#invitations-modal').modal('open');
    });
    
    $('#edit-expense-form').on('submit', function(e) {
        e.preventDefault();
        updateExpense();
    });
    
    $('#edit-category').on('change', function() {
        loadSubcategoriesForEdit($(this).val());
    });
    
    $('#edit-budget-form').on('submit', function(e) {
        e.preventDefault();
        updateBudget();
    });
    
    $('input[name="budget-type"]').on('change', function() {
        if ($(this).val() === 'no-budget') {
            $('#budget-amount-field').hide();
            $('#no-budget-info').show();
        } else {
            $('#budget-amount-field').show();
            $('#no-budget-info').hide();
        }
    });
    
    $('input[name="create-budget-type"]').on('change', function() {
        if ($(this).val() === 'no-budget') {
            $('#create-budget-field').hide();
        } else {
            $('#create-budget-field').show();
        }
    });
});

function editBudget() {
    const tripId = $('#current-trip').val();
    if (!tripId) {
        M.toast({html: 'Please select a trip first'});
        return;
    }
    
    // Check if user can edit budget (creator or master admin)
    const isMasterAdmin = window.userEmail === 'haerriz@gmail.com';
    
    // Get current trip data
    $.get('api/get_trip_summary.php', { trip_id: tripId })
        .done(function(data) {
            if (data.success && data.trip) {
                const isCreator = data.trip.created_by == window.currentUserId;
                
                // Check permission
                if (!isCreator && !isMasterAdmin) {
                    M.toast({html: 'Only trip creator can edit budget'});
                    return;
                }
                
                const currentBudget = data.trip.budget;
                
                if (currentBudget === null || currentBudget === undefined) {
                    // No budget currently set
                    $('input[name="budget-type"][value="no-budget"]').prop('checked', true);
                    $('#budget-amount-field').hide();
                    $('#no-budget-info').show();
                    $('#edit-budget-amount').val('');
                } else {
                    // Budget is set
                    $('input[name="budget-type"][value="with-budget"]').prop('checked', true);
                    $('#budget-amount-field').show();
                    $('#no-budget-info').hide();
                    $('#edit-budget-amount').val(parseFloat(currentBudget));
                }
                
                M.updateTextFields();
                $('#edit-budget-modal').modal('open');
            } else {
                M.toast({html: 'Error loading trip data'});
            }
        });
}

function updateBudget() {
    const tripId = $('#current-trip').val();
    const budgetType = $('input[name="budget-type"]:checked').val();
    const budgetAmount = $('#edit-budget-amount').val();
    
    const formData = {
        trip_id: tripId,
        no_budget: budgetType === 'no-budget',
        budget: budgetAmount
    };
    
    $.post('api/edit_trip_budget.php', formData)
        .done(function(data) {
            if (data.success) {
                $('#edit-budget-modal').modal('close');
                loadTripSummary(tripId); // Refresh the summary
                M.toast({html: data.message});
            } else {
                M.toast({html: data.message || 'Error updating budget'});
            }
        })
        .fail(function() {
            M.toast({html: 'Network error updating budget'});
        });
}

function editExpense(expenseId) {
    $.get('api/edit_expense.php', { expense_id: expenseId })
        .done(function(data) {
            if (data.success) {
                const expense = data.expense;
                $('#edit-expense-id').val(expense.id);
                $('#edit-category').val(expense.category);
                $('#edit-category').formSelect();
                loadSubcategoriesForEdit(expense.category, expense.subcategory);
                $('#edit-amount').val(expense.amount);
                $('#edit-description').val(expense.description);
                $('#edit-date').val(expense.date);
                
                M.updateTextFields();
                $('#edit-expense-modal').modal('open');
            } else {
                M.toast({html: data.message || 'Error loading expense'});
            }
        });
}

function updateExpense() {
    const formData = {
        expense_id: $('#edit-expense-id').val(),
        category: $('#edit-category').val(),
        subcategory: $('#edit-subcategory').val(),
        amount: $('#edit-amount').val(),
        description: $('#edit-description').val(),
        date: $('#edit-date').val()
    };
    
    $.post('api/edit_expense.php', formData)
        .done(function(data) {
            if (data.success) {
                $('#edit-expense-modal').modal('close');
                const tripId = $('#current-trip').val();
                loadTripDashboard(tripId);
                M.toast({html: 'Expense updated successfully'});
            } else {
                M.toast({html: data.message || 'Error updating expense'});
            }
        });
}

function loadSubcategoriesForEdit(category, selectedSub = '') {
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
            const selected = sub === selectedSub ? 'selected' : '';
            options += `<option value="${sub}" ${selected}>${sub}</option>`;
        });
    }
    $('#edit-subcategory').html(options);
    $('#edit-subcategory').formSelect();
}

function loadInvitations() {
    $.get('api/get_invitations.php')
        .done(function(data) {
            console.log('Invitations loaded:', data);
            if (data.success) {
                const invitations = data.invitations || [];
                $('#invitation-count').text(invitations.length);
                
                if (invitations.length === 0) {
                    $('#invitation-count').hide();
                    $('#invitations-btn').removeClass('pulse');
                } else {
                    $('#invitation-count').show();
                    $('#invitations-btn').addClass('pulse');
                }
                
                let html = '';
                if (invitations.length === 0) {
                    html = '<div class="center-align"><i class="material-icons large grey-text">inbox</i><p class="grey-text">No pending invitations</p></div>';
                } else {
                    invitations.forEach(function(inv) {
                        const inviteDate = inv.invited_at ? new Date(inv.invited_at).toLocaleDateString() : 'Recently';
                        html += `
                            <div class="card hoverable">
                                <div class="card-content">
                                    <span class="card-title blue-text">${inv.trip_name}</span>
                                    <p><i class="material-icons tiny">person</i> Invited by: <strong>${inv.invited_by_name}</strong></p>
                                    <p><i class="material-icons tiny">schedule</i> Date: ${inviteDate}</p>
                                </div>
                                <div class="card-action">
                                    <button class="btn green waves-effect" onclick="respondInvitation(${inv.id}, 'accept')">
                                        <i class="material-icons left">check</i>Accept
                                    </button>
                                    <button class="btn red waves-effect" onclick="respondInvitation(${inv.id}, 'reject')">
                                        <i class="material-icons left">close</i>Reject
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                $('#invitations-list').html(html);
            } else {
                console.error('Failed to load invitations:', data.error);
                $('#invitations-list').html('<p class="red-text center-align">Error loading invitations</p>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Invitation request failed:', error);
            $('#invitations-list').html('<p class="red-text center-align">Failed to load invitations</p>');
        });
}

function respondInvitation(invitationId, response) {
    $.post('api/respond_invitation.php', {
        invitation_id: invitationId,
        response: response
    })
    .done(function(data) {
        if (data.success) {
            M.toast({html: data.message});
            loadInvitations();
            loadTrips();
        } else {
            M.toast({html: data.message || 'Error responding to invitation'});
        }
    });
}

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
            console.log('Trips API response:', data);
            const trips = data.trips || [];
            let options = '<option value="">Select a Trip</option>';
            
            if (trips.length > 0) {
                trips.forEach(function(trip) {
                    const currency = getCurrencySymbol(trip.currency || 'USD');
                    options += `<option value="${trip.id}" data-currency="${trip.currency || 'USD'}">${trip.name} (${currency})</option>`;
                });
            }
            
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
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load trips:', error);
            console.error('Response:', xhr.responseText);
            M.toast({html: 'Failed to load trips'});
            showEmptyState();
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
            
            // Also populate edit modal categories
            $('#edit-category').html(options);
            $('#edit-category').formSelect();
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
    const budgetType = $('input[name="create-budget-type"]:checked').val();
    const formData = {
        name: $('#trip-name').val(),
        description: $('#trip-description').val(),
        start_date: $('#start-date').val(),
        end_date: $('#end-date').val(),
        currency: $('#currency').val(),
        no_budget: budgetType === 'no-budget'
    };
    
    if (budgetType === 'with-budget') {
        formData.budget = $('#budget').val();
    }
    
    $.post('api/create_trip.php', formData)
        .done(function(response) {
            if (response.success) {
                $('#trip-modal').modal('close');
                $('#trip-form')[0].reset();
                // Reset budget type to default
                $('input[name="create-budget-type"][value="with-budget"]').prop('checked', true);
                $('#create-budget-field').show();
                loadTrips();
                M.toast({html: response.message || 'Trip created successfully!'});
            } else {
                M.toast({html: response.message || 'Error creating trip'});
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
                const budget = data.trip.budget;
                
                // Handle budget display
                if (budget === null || budget === undefined) {
                    $('#trip-budget').text('No Budget');
                    $('#remaining-budget').text('Unlimited');
                } else {
                    $('#trip-budget').text(currency + parseFloat(budget).toFixed(2));
                    $('#remaining-budget').text(currency + parseFloat(data.remaining_budget || 0).toFixed(2));
                }
                
                $('#total-spent').text(currency + parseFloat(data.total_expenses || 0).toFixed(2));
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
            const currentUserId = getCurrentUserId(); // We'll need to get this
            let html = '';
            
            members.forEach(function(member) {
                const isCurrentUser = member.id == currentUserId;
                const buttonText = isCurrentUser ? 'Leave Trip' : 'Remove';
                const buttonIcon = isCurrentUser ? 'exit_to_app' : 'remove';
                const buttonTitle = isCurrentUser ? 'Leave this trip' : 'Remove member';
                
                html += `
                    <div class="trip-members__member">
                        <img src="${member.picture && !member.picture.includes('placeholder') ? member.picture : generateAvatar(member.name)}" alt="${member.name}" class="trip-members__avatar circle">
                        <span>${member.name}${isCurrentUser ? ' (You)' : ''}</span>
                        <button class="btn-small red right" onclick="removeMember(${tripId}, ${member.id})" title="${buttonTitle}">
                            <i class="material-icons">${buttonIcon}</i>
                        </button>
                    </div>
                `;
            });
            
            $('#members-list').html(html);
        })
        .fail(function() {
            $('#members-list').html('<p class="red-text">Error loading members</p>');
        });
}

function getCurrentUserId() {
    // Get current user ID from a global variable or API call
    return window.currentUserId || null;
}

function removeMember(tripId, memberId) {
    const isCurrentUser = memberId == window.currentUserId;
    
    // Check if current user is trip creator
    $.get('api/get_trip_summary.php', { trip_id: tripId })
        .done(function(tripData) {
            const isCreator = tripData.trip && tripData.trip.created_by == window.currentUserId;
            const isCreatorLeaving = isCurrentUser && isCreator;
            
            let confirmMessage;
            if (isCreatorLeaving) {
                confirmMessage = 'WARNING: You are the trip creator. Leaving will either:\n\n' +
                               '• Transfer ownership to another member (if others exist)\n' +
                               '• DELETE THE ENTIRE TRIP (if you are the only member)\n\n' +
                               'This action cannot be undone. Continue?';
            } else if (isCurrentUser) {
                confirmMessage = 'Are you sure you want to leave this trip?';
            } else {
                confirmMessage = 'Remove this member from the trip? This is only allowed if they have no expenses.';
            }
            
            if (confirm(confirmMessage)) {
                $.post('api/remove_member.php', {
                    trip_id: tripId,
                    member_id: memberId
                })
                .done(function(data) {
                    if (data.success) {
                        M.toast({html: data.message, displayLength: 6000});
                        
                        if (data.action === 'trip_deleted' || data.action === 'ownership_transferred') {
                            // Trip was deleted or ownership transferred, refresh everything
                            loadTrips();
                            $('#current-trip').val('');
                            $('#current-trip').formSelect();
                            showEmptyState();
                        } else if (isCurrentUser) {
                            // Regular user leaving
                            loadTrips();
                            $('#current-trip').val('');
                            $('#current-trip').formSelect();
                            showEmptyState();
                        } else {
                            // Member removed by creator
                            loadTripMembers(tripId);
                        }
                    } else {
                        M.toast({html: data.message || 'Error removing member'});
                    }
                })
                .fail(function() {
                    M.toast({html: 'Network error occurred'});
                });
            }
        })
        .fail(function() {
            // Fallback if can't get trip data
            const confirmMessage = isCurrentUser ? 
                'Are you sure you want to leave this trip?' : 
                'Remove this member from the trip?';
            
            if (confirm(confirmMessage)) {
                $.post('api/remove_member.php', {
                    trip_id: tripId,
                    member_id: memberId
                })
                .done(function(data) {
                    if (data.success) {
                        M.toast({html: data.message});
                        if (isCurrentUser) {
                            loadTrips();
                            $('#current-trip').val('');
                            $('#current-trip').formSelect();
                            showEmptyState();
                        } else {
                            loadTripMembers(tripId);
                        }
                    } else {
                        M.toast({html: data.message || 'Error removing member'});
                    }
                });
            }
        });
}

function loadExpenses(tripId) {
    $.get('api/get_expenses.php', { trip_id: tripId })
        .done(function(data) {
            const expenses = data.expenses || [];
            let html = '';
            
            expenses.forEach(function(expense) {
                const canEdit = expense.paid_by == window.currentUserId;
                html += `
                    <div class="expense-item">
                        <div class="expense-item__info">
                            <h6>${expense.category}</h6>
                            <p>${expense.description} - ${expense.date}</p>
                            <small>Paid by ${expense.paid_by_name}</small>
                        </div>
                        <div class="expense-item__amount">$${parseFloat(expense.amount).toFixed(2)}</div>
                        ${canEdit ? `<button class="btn-small blue" onclick="editExpense(${expense.id})"><i class="material-icons">edit</i></button>` : ''}
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
        const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
        const isPercentage = splitMode === 'percentage';
        let splitTotal = 0;
        let customSplits = {};
        let hasCustomValues = false;
        
        members.forEach(function(member) {
            const inputValue = parseFloat($(`#split_${member.id}`).val()) || 0;
            if (inputValue > 0) {
                if (isPercentage) {
                    splitTotal += inputValue;
                    customSplits[member.id] = (inputValue / 100) * totalAmount;
                } else {
                    splitTotal += inputValue;
                    customSplits[member.id] = inputValue;
                }
                hasCustomValues = true;
            }
        });
        
        if (!hasCustomValues) {
            M.toast({html: 'Please enter custom split amounts'});
            return;
        }
        
        const tolerance = isPercentage ? 0.1 : 0.01;
        const expectedTotal = isPercentage ? 100 : totalAmount;
        
        if (Math.abs(splitTotal - expectedTotal) > tolerance) {
            const unit = isPercentage ? '%' : '$';
            M.toast({html: `Custom split must equal ${expectedTotal}${unit}`});
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
        const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
        const isPercentage = splitMode === 'percentage';
        
        members.forEach(function(member) {
            const inputValue = parseFloat($(`#split_${member.id}`).val()) || 0;
            if (inputValue > 0) {
                const amount = isPercentage ? (inputValue / 100) * totalAmount : inputValue;
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
            const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
            const isPercentage = splitMode === 'percentage';
            
            let html = `
                <div class="custom-split-header">
                    <h6>Custom Split ${isPercentage ? 'Percentages' : 'Amounts'}</h6>
                    <div class="split-info">
                        <span>Total: <strong id="split-total">${isPercentage ? '100%' : '$' + totalAmount.toFixed(2)}</strong></span>
                        <span>Remaining: <strong id="split-remaining">${isPercentage ? '100%' : '$' + totalAmount.toFixed(2)}</strong></span>
                    </div>
                    <div class="split-actions">
                        <button type="button" class="btn-small" onclick="splitEqually()">Split Equally</button>
                        <button type="button" class="btn-small" onclick="clearSplits()">Clear All</button>
                        <button type="button" class="btn-small" onclick="payForAll()">I'll Pay All</button>
                    </div>
                </div>
                <div class="custom-split-members">
            `;
            
            members.forEach(function(member, index) {
                const inputMax = isPercentage ? '100' : totalAmount;
                const inputStep = isPercentage ? '0.1' : '0.01';
                const labelText = isPercentage ? 'Percentage' : 'Amount';
                const isCurrentUser = member.id == window.currentUserId;
                
                html += `
                    <div class="split-member-row">
                        <div class="member-info">
                            <img src="${member.picture || generateAvatar(member.name)}" class="split-avatar circle" alt="${member.name}">
                            <span class="member-name">${member.name}${isCurrentUser ? ' (You)' : ''}</span>
                        </div>
                        <div class="input-field split-input">
                            <input type="number" id="split_${member.id}" class="validate split-amount" 
                                   step="${inputStep}" min="0" max="${inputMax}" 
                                   data-member-id="${member.id}" data-member-name="${member.name}"
                                   onchange="updateSplitCalculation()" onkeyup="updateSplitCalculation()">
                            <label for="split_${member.id}">${labelText}</label>
                        </div>
                        <div class="split-display">
                            <span id="display_${member.id}">-</span>
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
    const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
    
    if (splitMode === 'percentage') {
        const equalPercentage = 100 / members.length;
        members.forEach(function(member) {
            $(`#split_${member.id}`).val(equalPercentage.toFixed(1));
        });
    } else {
        const equalAmount = totalAmount / members.length;
        members.forEach(function(member) {
            $(`#split_${member.id}`).val(equalAmount.toFixed(2));
        });
    }
    
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

function payForAll() {
    const members = window.tripMembers || [];
    const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
    const currentUserId = window.currentUserId;
    
    members.forEach(function(member) {
        if (member.id == currentUserId) {
            $(`#split_${member.id}`).val(splitMode === 'percentage' ? '100' : $('#amount').val());
        } else {
            $(`#split_${member.id}`).val('0');
        }
    });
    
    updateSplitCalculation();
    M.updateTextFields();
}

function updateSplitCalculation() {
    const totalAmount = parseFloat($('#amount').val()) || 0;
    const members = window.tripMembers || [];
    const splitMode = $('input[name="split-mode"]:checked').val() || 'currency';
    const isPercentage = splitMode === 'percentage';
    
    let splitTotal = 0;
    let hasValues = false;
    
    members.forEach(function(member) {
        const inputValue = parseFloat($(`#split_${member.id}`).val()) || 0;
        
        if (isPercentage) {
            splitTotal += inputValue;
            if (inputValue > 0) {
                hasValues = true;
                const amount = (inputValue / 100) * totalAmount;
                $(`#display_${member.id}`).text('$' + amount.toFixed(2));
            } else {
                $(`#display_${member.id}`).text('-');
            }
        } else {
            splitTotal += inputValue;
            if (inputValue > 0) {
                hasValues = true;
                const percentage = totalAmount > 0 ? (inputValue / totalAmount * 100).toFixed(1) : 0;
                $(`#display_${member.id}`).text(percentage + '%');
            } else {
                $(`#display_${member.id}`).text('-');
            }
        }
    });
    
    // Update totals and remaining
    if (isPercentage) {
        const remaining = 100 - splitTotal;
        $('#split-total').text('100%');
        $('#split-remaining').text(remaining.toFixed(1) + '%');
        
        // Validation for percentage
        if (hasValues) {
            if (Math.abs(remaining) > 0.1) {
                $('#split-validation').show();
                if (remaining > 0) {
                    $('#split-error').text(`${remaining.toFixed(1)}% remaining to be allocated`);
                } else {
                    $('#split-error').text(`Over-allocated by ${Math.abs(remaining).toFixed(1)}%`);
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
    } else {
        const remaining = totalAmount - splitTotal;
        $('#split-total').text('$' + totalAmount.toFixed(2));
        $('#split-remaining').text('$' + remaining.toFixed(2));
        
        // Validation for currency
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