document.addEventListener("DOMContentLoaded", function(event) {
    // progressive enhancement: only add if window.getSelection() is supported
    if (window.getSelection) {
        // make link clickable and make click copy to clipboard
        var e = document.getElementById('reference');
        e.setAttribute('href', '#');
        e.addEventListener('click', function(event) {
            copyText(this);
            event.preventDefault();
        });
        // add icon
        e.classList.add('can-copy');
        // add tooltip
        p = e.parentNode;
        p.classList.add('tooltip');
        p.setAttribute('data-tooltip', 'Cliquez pour copier');
        e.addEventListener('focus', function() {
            p.classList.add('is-tooltip-active');
        });
        e.addEventListener('blur', function() {
            p.classList.remove('is-tooltip-active');
        });
    }
    // add link to close notification
    var e = document.getElementById('reference-box');
    var closeLink = document.createElement('a');
    closeLink.className = 'delete';
    closeLink.addEventListener('click', function() {
        e.parentNode.parentNode.style.display = 'none';
    });
    e.insertBefore(closeLink, e.childNodes[0]);
});

// function to copy to clipboard
function copyText(elem) {
    var range = document.createRange();
    var sel = window.getSelection();
    range.selectNodeContents(elem);
    sel.removeAllRanges();
    sel.addRange(range);
    document.execCommand('copy');
    sel.removeAllRanges();
}
