document.addEventListener("DOMContentLoaded", function(event) {
    var dateField = document.getElementById('dt');
    var partField = document.getElementById('part');
    if (!partField.classList.contains('is-danger') && !dateField.value.includes('1883')) {
        partField.parentNode.parentNode.style.display = 'none';
    }

    dateField.addEventListener('input', function() {
        if (dateField.value.includes('1883')) {
            partField.parentNode.parentNode.style.display = 'block';
        } else {
            partField.parentNode.parentNode.style.display = 'none';
        }
    });
});
