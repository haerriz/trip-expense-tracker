$(document).ready(function() {
    M.AutoInit();
    
    loadCategories();
    loadUsers();
    loadAllTrips();
    loadStatistics();
    
    $('#category-form').on('submit', function(e) {
        e.preventDefault();
        addCategory();
    });
    
    $('#edit-category-form').on('submit', function(e) {
        e.preventDefault();
        updateCategory();
    });
});

function editCategory(categoryId) {
    $.get('api/admin/edit_category.php', { category_id: categoryId })
        .done(function(data) {
            if (data.success) {
                const category = data.category;
                $('#edit-category-id').val(category.id);
                $('#edit-category-name').val(category.name);
                $('#edit-subcategories').val(category.subcategories || '');
                
                M.updateTextFields();
                $('#edit-category-modal').modal('open');
            } else {
                M.toast({html: data.message || 'Error loading category'});
            }
        })
        .fail(function(xhr, status, error) {
            M.toast({html: 'Network error loading category'});
        });
}

function updateCategory() {
    const categoryId = $('#edit-category-id').val();
    const name = $('#edit-category-name').val();
    const subcategories = $('#edit-subcategories').val();
    
    
    if (!categoryId) {
        M.toast({html: 'Category ID is missing'});
        return;
    }
    
    $.post('api/admin/edit_category.php', {
        category_id: categoryId,
        name: name,
        subcategories: subcategories
    })
    .done(function(data) {
        if (data.success) {
            M.toast({html: data.message});
            $('#edit-category-modal').modal('close');
            loadCategories();
        } else {
            M.toast({html: data.message || 'Error updating category'});
        }
    })
    .fail(function(xhr, status, error) {
        M.toast({html: 'Network error updating category'});
    });
}

function addCategory() {
    const name = $('#category-name').val();
    const subcategories = $('#subcategories').val();
    
    
    $.post('api/admin/add_category.php', {
        name: name,
        subcategories: subcategories
    })
    .done(function(data) {
        if (data.success) {
            M.toast({html: 'Category added successfully'});
            $('#category-form')[0].reset();
            loadCategories();
        } else {
            M.toast({html: data.message || 'Error adding category'});
        }
    })
    .fail(function(xhr, status, error) {
        M.toast({html: 'Network error adding category'});
    });
}

function loadCategories() {
    $.get('api/get_categories.php')
        .done(function(data) {
            const categories = data.categories || [];
            let html = '';
            
            categories.forEach(function(category) {
                html += `
                    <div class="card-panel">
                        <div class="category-header">
                            <h6>${category.name}</h6>
                            <div class="category-actions">
                                <button class="btn-small blue" onclick="editCategory(${category.id})" title="Edit category">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="btn-small red" onclick="deleteCategory(${category.id})" title="Delete category">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </div>
                        <p><strong>Subcategories:</strong> ${category.subcategories || 'None'}</p>
                    </div>
                `;
            });
            
            $('#categories-list').html(html);
        })
        .fail(function(xhr, status, error) {
        });
}

function deleteCategory(categoryId) {
    if (confirm('Delete this category? This cannot be undone.')) {
        $.post('api/admin/delete_category.php', { category_id: categoryId })
            .done(function(data) {
                if (data.success) {
                    M.toast({html: 'Category deleted'});
                    loadCategories();
                } else {
                    M.toast({html: data.message || 'Error deleting category'});
                }
            })
            .fail(function(xhr, status, error) {
                M.toast({html: 'Network error deleting category'});
            });
    }
}

function loadUsers() {
    $.get('api/admin/get_users.php')
        .done(function(data) {
            const users = data.users || [];
            
            // Mobile-first responsive design
            let html = `
                <div class="hide-on-small-only">
                    <table class="striped responsive-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Trips</th>
                                <th>Total Expenses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            users.forEach(function(user) {
                html += `
                    <tr>
                        <td>
                            <img src="${user.picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=30&background=D32F2F&color=fff'}" class="circle" style="width:30px;height:30px;margin-right:8px;">
                            ${user.name}
                        </td>
                        <td>${user.email}</td>
                        <td><span class="badge">${user.trip_count || 0}</span></td>
                        <td class="green-text">$${parseFloat(user.total_expenses || 0).toFixed(2)}</td>
                        <td>
                            <button class="btn-small blue waves-effect" onclick="viewUserDetails(${user.id})">
                                <i class="material-icons">visibility</i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Cards -->
                <div class="hide-on-med-and-up">
            `;
            
            users.forEach(function(user) {
                html += `
                    <div class="card">
                        <div class="card-content">
                            <div class="row valign-wrapper" style="margin-bottom: 0;">
                                <div class="col s3">
                                    <img src="${user.picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=50&background=D32F2F&color=fff'}" class="circle responsive-img" style="width:50px;height:50px;">
                                </div>
                                <div class="col s9">
                                    <h6 style="margin: 0 0 5px 0;">${user.name}</h6>
                                    <p style="margin: 0; font-size: 0.9rem; color: #666;">${user.email}</p>
                                </div>
                            </div>
                            <div class="row" style="margin: 10px 0 0 0;">
                                <div class="col s4 center-align">
                                    <span class="badge blue">${user.trip_count || 0}</span>
                                    <br><small>Trips</small>
                                </div>
                                <div class="col s4 center-align">
                                    <span class="green-text"><strong>$${parseFloat(user.total_expenses || 0).toFixed(2)}</strong></span>
                                    <br><small>Expenses</small>
                                </div>
                                <div class="col s4 center-align">
                                    <button class="btn-small blue waves-effect" onclick="viewUserDetails(${user.id})">
                                        <i class="material-icons">visibility</i>
                                    </button>
                                    <br><small>View</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            $('#users-list').html(html);
        });
}

function viewUserDetails(userId) {
    $.get('api/admin/get_user_details.php', { user_id: userId })
        .done(function(data) {
            if (data.success) {
                const user = data.user;
                const trips = data.trips || [];
                
                let tripsList = '<ul class="collection">';
                trips.forEach(function(trip) {
                    tripsList += `
                        <li class="collection-item">
                            <strong>${trip.name}</strong> - $${parseFloat(trip.budget || 0).toFixed(2)}
                            <span class="secondary-content">${trip.member_count} members</span>
                        </li>
                    `;
                });
                tripsList += '</ul>';
                
                const modalContent = `
                    <div class="modal-content">
                        <h4>${user.name}</h4>
                        <p><strong>Email:</strong> ${user.email}</p>
                        <p><strong>Total Trips:</strong> ${trips.length}</p>
                        <p><strong>Total Expenses:</strong> $${parseFloat(user.total_expenses || 0).toFixed(2)}</p>
                        <h5>Trips:</h5>
                        ${tripsList}
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-close waves-effect btn-flat">Close</a>
                    </div>
                `;
                
                $('#user-modal').remove();
                $('body').append(`<div id="user-modal" class="modal">${modalContent}</div>`);
                M.Modal.init(document.getElementById('user-modal')).open();
            }
        });
}

function loadAllTrips() {
    $.get('api/admin/get_all_trips.php')
        .done(function(data) {
            const trips = data.trips || [];
            
            // Desktop table
            let html = `
                <div class="hide-on-small-only">
                    <table class="striped responsive-table">
                        <thead>
                            <tr>
                                <th>Trip Name</th>
                                <th>Creator</th>
                                <th>Members</th>
                                <th>Budget</th>
                                <th>Expenses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            trips.forEach(function(trip) {
                const budgetText = trip.budget ? `$${parseFloat(trip.budget).toFixed(2)}` : 'No Budget';
                html += `
                    <tr>
                        <td><strong>${trip.name}</strong></td>
                        <td>${trip.creator_name}</td>
                        <td><span class="badge">${trip.member_count || 0}</span></td>
                        <td class="blue-text">${budgetText}</td>
                        <td class="green-text">$${parseFloat(trip.total_expenses || 0).toFixed(2)}</td>
                        <td>
                            <button class="btn-small blue waves-effect" onclick="viewTripDetails(${trip.id})" style="margin-right: 4px;">
                                <i class="material-icons">visibility</i>
                            </button>
                            <button class="btn-small red waves-effect" onclick="deleteTrip(${trip.id})">
                                <i class="material-icons">delete</i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Cards -->
                <div class="hide-on-med-and-up">
            `;
            
            trips.forEach(function(trip) {
                const budgetText = trip.budget ? `$${parseFloat(trip.budget).toFixed(2)}` : 'No Budget';
                html += `
                    <div class="card">
                        <div class="card-content">
                            <div class="card-title" style="font-size: 1.1rem; margin-bottom: 10px;">
                                <i class="material-icons left">flight_takeoff</i>${trip.name}
                            </div>
                            <p style="margin: 5px 0; color: #666;">
                                <i class="material-icons tiny">person</i> Created by: <strong>${trip.creator_name}</strong>
                            </p>
                            <div class="row" style="margin: 15px 0 0 0;">
                                <div class="col s4 center-align">
                                    <span class="badge blue">${trip.member_count || 0}</span>
                                    <br><small>Members</small>
                                </div>
                                <div class="col s4 center-align">
                                    <span class="blue-text"><strong>${budgetText}</strong></span>
                                    <br><small>Budget</small>
                                </div>
                                <div class="col s4 center-align">
                                    <span class="green-text"><strong>$${parseFloat(trip.total_expenses || 0).toFixed(2)}</strong></span>
                                    <br><small>Expenses</small>
                                </div>
                            </div>
                            <div class="card-action center-align">
                                <button class="btn-small blue waves-effect" onclick="viewTripDetails(${trip.id})" style="margin-right: 8px;">
                                    <i class="material-icons left">visibility</i>View
                                </button>
                                <button class="btn-small red waves-effect" onclick="deleteTrip(${trip.id})">
                                    <i class="material-icons left">delete</i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            $('#trips-list').html(html);
        });
}

function viewTripDetails(tripId) {
    $.get('api/admin/get_trip_details.php', { trip_id: tripId })
        .done(function(data) {
            if (data.success) {
                const trip = data.trip;
                const members = data.members || [];
                const expenses = data.expenses || [];
                
                let membersList = '<div class="collection">';
                members.forEach(function(member) {
                    membersList += `
                        <div class="collection-item avatar">
                            <img src="${member.picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.name) + '&size=40&background=2196F3&color=fff'}" class="circle">
                            <span class="title">${member.name}</span>
                            <p>${member.email}<br>
                               Total Paid: <strong class="green-text">$${parseFloat(member.total_paid || 0).toFixed(2)}</strong>
                            </p>
                        </div>
                    `;
                });
                membersList += '</div>';
                
                let expensesList = '<ul class="collection">';
                expenses.slice(0, 10).forEach(function(expense) {
                    expensesList += `
                        <li class="collection-item">
                            <div class="row valign-wrapper" style="margin-bottom: 0;">
                                <div class="col s8">
                                    <strong>${expense.category}</strong>
                                    <br><span style="color: #666;">${expense.description}</span>
                                    <br><small>by ${expense.paid_by_name} on ${expense.date}</small>
                                </div>
                                <div class="col s4 right-align">
                                    <span class="green-text"><strong>$${parseFloat(expense.amount).toFixed(2)}</strong></span>
                                </div>
                            </div>
                        </li>
                    `;
                });
                if (expenses.length > 10) {
                    expensesList += `<li class="collection-item center-align"><em>... and ${expenses.length - 10} more expenses</em></li>`;
                }
                expensesList += '</ul>';
                
                const budgetText = trip.budget ? `$${parseFloat(trip.budget).toFixed(2)}` : 'No Budget Set';
                const totalExpenses = parseFloat(trip.total_expenses || 0);
                const remaining = trip.budget ? (parseFloat(trip.budget) - totalExpenses) : 0;
                
                const modalContent = `
                    <div class="modal-content">
                        <h4><i class="material-icons left">flight_takeoff</i>${trip.name}</h4>
                        
                        <div class="row">
                            <div class="col s12 m6">
                                <div class="card-panel blue lighten-4">
                                    <h6>Trip Summary</h6>
                                    <p><strong>Created by:</strong> ${trip.creator_name}</p>
                                    <p><strong>Budget:</strong> ${budgetText}</p>
                                    <p><strong>Total Expenses:</strong> <span class="green-text">$${totalExpenses.toFixed(2)}</span></p>
                                    ${trip.budget ? `<p><strong>Remaining:</strong> <span class="${remaining >= 0 ? 'green' : 'red'}-text">$${remaining.toFixed(2)}</span></p>` : ''}
                                    <p><strong>Members:</strong> ${members.length}</p>
                                    <p><strong>Total Expenses Count:</strong> ${expenses.length}</p>
                                </div>
                            </div>
                            <div class="col s12 m6">
                                <div class="card-panel orange lighten-4">
                                    <h6>Trip Details</h6>
                                    <p><strong>Start Date:</strong> ${trip.start_date || 'Not set'}</p>
                                    <p><strong>End Date:</strong> ${trip.end_date || 'Not set'}</p>
                                    <p><strong>Currency:</strong> ${trip.currency || 'USD'}</p>
                                    <p><strong>Created:</strong> ${new Date(trip.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                        
                        <ul class="tabs">
                            <li class="tab col s4"><a href="#trip-members" class="active">Members</a></li>
                            <li class="tab col s4"><a href="#trip-expenses">Recent Expenses</a></li>
                        </ul>
                        
                        <div id="trip-members" class="col s12">
                            <h6>Trip Members (${members.length})</h6>
                            ${membersList}
                        </div>
                        
                        <div id="trip-expenses" class="col s12">
                            <h6>Recent Expenses (${expenses.length} total)</h6>
                            ${expensesList}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-close waves-effect btn-flat">Close</a>
                    </div>
                `;
                
                $('#trip-modal').remove();
                $('body').append(`<div id="trip-modal" class="modal modal-fixed-footer" style="max-height: 80%;">${modalContent}</div>`);
                const modalInstance = M.Modal.init(document.getElementById('trip-modal'));
                modalInstance.open();
                
                // Initialize tabs in modal
                setTimeout(() => {
                    M.Tabs.init(document.querySelectorAll('#trip-modal .tabs'));
                }, 100);
            } else {
                M.toast({html: data.message || 'Error loading trip details'});
            }
        })
        .fail(function() {
            M.toast({html: 'Network error loading trip details'});
        });
}

function deleteTrip(tripId) {
    if (confirm('Delete this trip and all its data? This cannot be undone.')) {
        $.post('api/admin/delete_trip.php', { trip_id: tripId })
            .done(function(data) {
                if (data.success) {
                    M.toast({html: 'Trip deleted'});
                    loadAllTrips();
                    loadStatistics();
                } else {
                    M.toast({html: data.message || 'Error deleting trip'});
                }
            });
    }
}

function loadStatistics() {
    $.get('api/admin/get_statistics.php')
        .done(function(data) {
            if (data.success) {
                $('#total-users').text(data.stats.total_users || 0);
                $('#total-trips').text(data.stats.total_trips || 0);
                $('#total-expenses').text('$' + parseFloat(data.stats.total_expenses || 0).toFixed(2));
                $('#active-trips').text(data.stats.active_trips || 0);
                
                // Load charts
                loadCategoryChart();
                loadUserChart();
            }
        });
}

function loadCategoryChart() {
    $.get('api/admin/get_chart_data.php?type=categories')
        .done(function(data) {
            if (data.success) {
                const ctx = document.getElementById('categoryChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            data: data.values || [],
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
}

function loadUserChart() {
    $.get('api/admin/get_chart_data.php?type=users')
        .done(function(data) {
            if (data.success) {
                const ctx = document.getElementById('userChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'New Users',
                            data: data.values || [],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
}