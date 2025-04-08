document.addEventListener('DOMContentLoaded', function() {
    let accountTypeField = document.getElementById('registration_form_accountType');
    let professionalFields = document.getElementById('professional-fields');

    function toggleProfessionalFields() {
        if (accountTypeField.value === 'professionnel') {
            professionalFields.style.display = 'block';
        } else {
            professionalFields.style.display = 'none';
        }
    }

    accountTypeField.addEventListener('change', toggleProfessionalFields);
    toggleProfessionalFields();
});