document.addEventListener('DOMContentLoaded', function() {
    // Add Stylist Form
    const addStylistForm = document.getElementById('addStylistForm');
    if (addStylistForm) {
        addStylistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const formData = new FormData(this);
            
            fetch('process_stylist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addStylistModal'));
                    modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error adding stylist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    }

    // Edit Stylist Functionality
    document.querySelectorAll('.edit-stylist').forEach(button => {
        button.addEventListener('click', function() {
            const stylistId = this.dataset.id;
            fetch(`process_stylist.php?action=get&id=${stylistId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fill edit form
                        const form = document.getElementById('editStylistForm');
                        form.querySelector('[name="stylist_id"]').value = data.stylist.id;
                        form.querySelector('[name="name"]').value = data.stylist.name;
                        form.querySelector('[name="phone"]').value = data.stylist.phone;
                        form.querySelector('[name="email"]').value = data.stylist.email;
                        form.querySelector('[name="specialization"]').value = data.stylist.specialization || '';
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editStylistModal'));
                        modal.show();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading stylist details');
                });
        });
    });

    // Edit Stylist Form
    const editStylistForm = document.getElementById('editStylistForm');
    if (editStylistForm) {
        editStylistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            fetch('process_stylist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editStylistModal'));
                    modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating stylist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the stylist');
            });
        });
    }

    // Toggle Status
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const newStatus = this.dataset.status === 'active' ? 'inactive' : 'active';
            const message = `Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this stylist?`;
            
            if (confirm(message)) {
                fetch('process_stylist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'toggle_status',
                        stylist_id: this.dataset.id,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error updating status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating status');
                });
            }
        });
    });
});