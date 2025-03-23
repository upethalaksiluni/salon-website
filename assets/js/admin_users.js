document.addEventListener('DOMContentLoaded', function() {
    // View User Details
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            document.getElementById('userDetailsContent').innerHTML = 'Loading...';
            modal.show();
            
            fetch(`api/get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const user = data.user;
                    const appointments = data.recent_appointments;
                    
                    let html = `
                        <div class="user-profile">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img src="${user.profile_image || 'assets/images/default-avatar.png'}" 
                                         class="rounded-circle user-avatar mb-3" 
                                         alt="${user.fullname}">
                                    <h4>${user.fullname}</h4>
                                    <p class="text-muted">@${user.username}</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="user-stats row mb-4">
                                        <div class="col-4 text-center">
                                            <h5>${user.total_appointments || 0}</h5>
                                            <p>Appointments</p>
                                        </div>
                                        <div class="col-4 text-center">
                                            <h5>${user.avg_rating === 'N/A' ? 'N/A' : user.avg_rating}</h5>
                                            <p>Avg Rating</p>
                                        </div>
                                        <div class="col-4 text-center">
                                            <h5>${user.total_feedback || 0}</h5>
                                            <p>Reviews</p>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info">
                                        <p><strong>Email:</strong> ${user.email}</p>
                                        <p><strong>Phone:</strong> ${user.phone || 'Not provided'}</p>
                                        <p><strong>Gender:</strong> ${user.gender || 'Not specified'}</p>
                                        <p><strong>Member Since:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                                        <p><strong>Preferred Time:</strong> ${user.preferred_time || 'Not specified'}</p>
                                        ${user.frequent_services ? `
                                        <p><strong>Frequent Services:</strong> ${user.frequent_services}</p>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            ${appointments.length > 0 ? `
                            <div class="recent-appointments mt-4">
                                <h5 class="mb-3">Recent Appointments</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Services</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${appointments.map(apt => `
                                                <tr>
                                                    <td>${new Date(apt.appointment_date + ' ' + apt.appointment_time).toLocaleString()}</td>
                                                    <td>${apt.services || 'No services'}</td>
                                                    <td><span class="badge bg-${getStatusColor(apt.status)}">${apt.status}</span></td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            ` : '<p class="text-muted mt-3">No appointments found</p>'}
                        </div>
                    `;
                    
                    document.getElementById('userDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('userDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            Error loading user details: ${error.message}
                        </div>
                    `;
                });
        });
    });

    // View Appointments
    document.querySelectorAll('.view-appointments').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            fetch(`api/get_user_appointments.php?id=${userId}`)
                .then(response => response.json())
                .then(appointments => {
                    const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Services</th>
                                        <th>Status</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    appointments.forEach(apt => {
                        html += `
                            <tr>
                                <td>${new Date(apt.appointment_date + ' ' + apt.appointment_time).toLocaleString()}</td>
                                <td>${apt.services}</td>
                                <td><span class="badge bg-${getStatusColor(apt.status)}">${apt.status}</span></td>
                                <td>${apt.rating ? `${apt.rating} ⭐` : 'No rating'}</td>
                            </tr>
                        `;
                    });
                    
                    html += `</tbody></table></div>`;
                    document.getElementById('userDetailsContent').innerHTML = html;
                    modal.show();
                });
        });
    });

    // View Feedback
    document.querySelectorAll('.view-feedback').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            document.getElementById('userDetailsContent').innerHTML = 'Loading...';
            
            fetch(`api/get_user_feedback.php?id=${userId}`)
                .then(response => response.json())
                .then(feedback => {
                    let html = `
                        <div class="feedback-container">
                            <h4 class="mb-4">User Feedback History</h4>
                            ${feedback.length === 0 ? '<p class="text-muted">No feedback available</p>' : ''}
                            <div class="feedback-list">
                    `;
                    
                    feedback.forEach(item => {
                        html += `
                            <div class="feedback-card card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Appointment Date: ${new Date(item.appointment_date).toLocaleDateString()}</span>
                                    <span class="badge bg-primary">${item.service_quality}</span>
                                </div>
                                <div class="card-body">
                                    <div class="rating-display mb-2">
                                        ${getStarRating(item.rating)}
                                    </div>
                                    <p class="feedback-comment">${item.comments || 'No comments provided'}</p>
                                    <div class="services-list">
                                        <small class="text-muted">Services: ${item.services}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `</div></div>`;
                    document.getElementById('userDetailsContent').innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('userDetailsContent').innerHTML = 
                        '<div class="alert alert-danger">Error loading feedback</div>';
                });
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const userId = this.dataset.id;
                const formData = new FormData();
                formData.append('id', userId);
                
                fetch('api/delete_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        showAlert('User deleted successfully', 'success');
                    } else {
                        showAlert('Error deleting user: ' + data.message, 'danger');
                    }
                });
            }
        });
    });
});

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'confirmed': 'info',
        'completed': 'success',
        'cancelled': 'danger',
        'no_show': 'secondary'
    };
    return colors[status] || 'primary';
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.card'));
    setTimeout(() => alertDiv.remove(), 3000);
}

// Helper function to generate star rating HTML
function getStarRating(rating) {
    const fullStar = '★';
    const emptyStar = '☆';
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += `<span class="star ${i <= rating ? 'active' : ''}">${i <= rating ? fullStar : emptyStar}</span>`;
    }
    return `<div class="stars">${stars}</div>`;
}