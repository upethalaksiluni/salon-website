document.addEventListener('DOMContentLoaded', function() {
    // Edit Service
    const editButtons = document.querySelectorAll('.edit-service');
    const editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const data = this.dataset;
            const form = document.getElementById('editServiceForm');
            
            // Fill form with service data
            form.querySelector('[name="service_id"]').value = data.id;
            form.querySelector('[name="name"]').value = data.name;
            form.querySelector('[name="category"]').value = data.category;
            form.querySelector('[name="duration"]').value = data.duration;
            form.querySelector('[name="price"]').value = data.price;
            form.querySelector('[name="description"]').value = data.description;
            form.querySelector('[name="status"]').value = data.status;
            
            editModal.show();
        });
    });

    // Delete Service
    const deleteButtons = document.querySelectorAll('.delete-service');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm(`Are you sure you want to delete "${this.dataset.name}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_service.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const serviceIdInput = document.createElement('input');
                serviceIdInput.type = 'hidden';
                serviceIdInput.name = 'service_id';
                serviceIdInput.value = this.dataset.id;
                
                form.appendChild(actionInput);
                form.appendChild(serviceIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Price Input Formatting
    const priceInputs = document.querySelectorAll('input[name="price"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            let decimalCount = (value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                value = value.substring(0, value.lastIndexOf('.'));
            }
            this.value = value;
        });

        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
});