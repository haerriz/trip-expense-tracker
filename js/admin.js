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
});

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
                        <h6>${category.name}</h6>
                        <p><strong>Subcategories:</strong> ${category.subcategories}</p>
                        <button class="btn-small red" onclick="deleteCategory(${category.id})">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                `;
            });
            
            $('#categories-list').html(html);
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
            });
    }
}

function loadUsers() {
    $.get('api/admin/get_users.php')
        .done(function(data) {
            const users = data.users || [];
            let html = '<table class="striped"><thead><tr><th>Name</th><th>Email</th><th>Trips</th><th>Total Expenses</th><th>Actions</th></tr></thead><tbody>';
            
            users.forEach(function(user) {
                html += `
                    <tr>
                        <td>
                            <img src="${user.picture || 'default-avatar.png'}" class="circle" style="width:30px;height:30px;">
                            ${user.name}
                        </td>
                        <td>${user.email}</td>
                        <td>${user.trip_count || 0}</td>
                        <td>$${parseFloat(user.total_expenses || 0).toFixed(2)}</td>
                        <td>
                            <button class="btn-small" onclick="viewUserDetails(${user.id})">View</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
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
            let html = '<table class="striped"><thead><tr><th>Trip Name</th><th>Creator</th><th>Members</th><th>Budget</th><th>Expenses</th><th>Actions</th></tr></thead><tbody>';
            
            trips.forEach(function(trip) {
                html += `
                    <tr>
                        <td>${trip.name}</td>
                        <td>${trip.creator_name}</td>
                        <td>${trip.member_count || 0}</td>
                        <td>$${parseFloat(trip.budget || 0).toFixed(2)}</td>
                        <td>$${parseFloat(trip.total_expenses || 0).toFixed(2)}</td>
                        <td>
                            <button class="btn-small" onclick="viewTripDetails(${trip.id})">View</button>
                            <button class="btn-small red" onclick="deleteTrip(${trip.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            $('#trips-list').html(html);
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
            }
        });
}