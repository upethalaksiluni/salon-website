document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Handle form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password Toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    // Add Admin Form Handler
    const addAdminForm = document.getElementById('addAdminForm');
    if (addAdminForm) {
        addAdminForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            try {
                const formData = new FormData(this);
                formData.append('action', 'create');

                const response = await fetch('process_admin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Admin created successfully', 'success');
                    location.reload();
                } else {
                    showAlert(data.message || 'Error creating admin', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            }
        });
    }

    // Delete Admin Handler
    document.querySelectorAll('.delete-admin').forEach(button => {
        button.addEventListener('click', async function() {
            const adminId = this.dataset.id;
            const adminName = this.dataset.name;

            if (confirm(`Are you sure you want to delete administrator "${adminName}"?`)) {
                try {
                    const response = await fetch('process_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            admin_id: adminId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('Admin deleted successfully', 'success');
                        location.reload();
                    } else {
                        showAlert(data.message || 'Error deleting admin', 'danger');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'danger');
                }
            }
        });
    });

    // Handle admin editing
    document.querySelectorAll('.edit-admin').forEach(button => {
        button.addEventListener('click', async function() {
            const adminId = this.dataset.id;
            const modal = document.getElementById('editAdminModal');
            
            try {
                const response = await fetch(`get_admin.php?id=${adminId}`);
                const data = await response.json();
                
                if (data.success) {
                    modal.querySelector('.modal-content').innerHTML = data.html;
                    setupEditForm(modal);
                    new bootstrap.Modal(modal).show();
                } else {
                    showAlert(data.message || 'Error loading admin details', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred while loading admin details', 'danger');
            }
        });
    });

    // Setup edit form submission
    function setupEditForm(modal) {
        const form = modal.querySelector('#editAdminForm');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            try {
                const formData = new FormData(this);
                formData.append('action', 'update');

                const response = await fetch('process_admin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('Admin updated successfully', 'success');
                    bootstrap.Modal.getInstance(modal).hide();
                    location.reload();
                } else {
                    showAlert(data.message || 'Error updating admin', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'danger');
            }
        });
    }

    // Alert Helper Function
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }
});