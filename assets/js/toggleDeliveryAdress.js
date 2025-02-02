function toggleDeliveryAddress() {
    let deliveryMode = document.querySelector('input[name="delivery_mode"]:checked').value;
    let deliveryAddress = document.getElementById('delivery-address');
    let deliveryFields = deliveryAddress.querySelectorAll('input, select, textarea');
    if (deliveryMode === 'comptoir') {
        deliveryAddress.style.display = 'none';
        deliveryFields.forEach(function(field) {
            field.disabled = true;
        });
    } else {
        deliveryAddress.style.display = 'block';
        deliveryFields.forEach(function(field) {
            field.disabled = false;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('input[name="delivery_mode"][value="comptoir"]').checked = true;
    toggleDeliveryAddress();

    document.querySelectorAll('input[name="delivery_mode"]').forEach(function(input) {
        input.addEventListener('change', toggleDeliveryAddress);
    });
});