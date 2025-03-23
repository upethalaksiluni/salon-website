document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.querySelector('select[name="period"]');
    
    periodSelect.addEventListener('change', function() {
        this.form.submit();
    });

    // Update custom date fields visibility
    function updateCustomDateFields() {
        const customDateFields = document.querySelectorAll('.custom-date');
        const isCustom = periodSelect.value === 'custom';
        
        customDateFields.forEach(field => {
            field.style.display = isCustom ? 'block' : 'none';
            const input = field.querySelector('input');
            if (input) {
                input.required = isCustom;
            }
        });
    }

    updateCustomDateFields();
    periodSelect.addEventListener('change', updateCustomDateFields);
});