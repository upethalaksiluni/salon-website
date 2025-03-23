document.addEventListener('DOMContentLoaded', function() {
    initializeAppointmentFilters();
    initializeAppointmentActions();
    initializeDateRangeFilter();

    // Update appointment status
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            
            document.getElementById('updateAppointmentId').value = appointmentId;
            document.getElementById('appointmentStatus').value = currentStatus;
            
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        });
    });

    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });

    // Handle appointment status updates
    const statusForms = document.querySelectorAll('.appointment-status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('process_admin_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing request');
            });
        });
    });

    // Initialize charts
    const appointmentsByDayChart = document.getElementById('appointmentsByDayChart');
    if (appointmentsByDayChart) {
        new Chart(appointmentsByDayChart, {
            type: 'bar',
            data: appointmentsByDayData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});

function initializeAppointmentFilters() {
    const filterForm = document.querySelector('.appointments-filters form');
    const periodSelect = document.getElementById('period');
    const statusSelect = document.getElementById('status');

    // Auto-submit on filter change
    [periodSelect, statusSelect].forEach(select => {
        select?.addEventListener('change', () => filterForm?.submit());
    });
}

function initializeAppointmentActions() {
    // View appointment details
    document.querySelectorAll('.view-appointment').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.id;
            loadAppointmentDetails(appointmentId);
        });
    });

    // Approve appointment
    document.querySelectorAll('.approve-appointment').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to approve this appointment?')) {
                const id = this.dataset.id;
                window.location.href = `appointment_status_handler.php?action=approve&id=${id}`;
            }
        });
    });

    // Cancel appointment
    document.querySelectorAll('.cancel-appointment').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                const id = this.dataset.id;
                window.location.href = `appointment_status_handler.php?action=cancel&id=${id}`;
            }
        });
    });
}

function initializeDateRangeFilter() {
    const dateRange = document.getElementById('dateRange');
    const customDateFields = document.querySelectorAll('.custom-date');

    if (dateRange && customDateFields.length > 0) {
        dateRange.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            customDateFields.forEach(field => {
                field.classList.toggle('d-none', !isCustom);
                const input = field.querySelector('input');
                if (input) {
                    input.required = isCustom;
                }
            });
        });
    }
}

function loadAppointmentDetails(appointmentId) {
    const modal = new bootstrap.Modal(document.getElementById('appointmentDetailsModal'));
    const content = document.getElementById('appointmentDetailsContent');
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    fetch(`get_appointment_details.php?id=${appointmentId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            modal.show();
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading appointment details</div>';
            console.error('Error:', error);
        });
}