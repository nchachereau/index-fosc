document.addEventListener("DOMContentLoaded", function(event) {
    // progressive enhancement: only add if window.getSelection() is supported
    if (window.getSelection && document.queryCommandSupported('copy')) {
        // make link clickable and make click copy to clipboard
        var refs = document.getElementsByClassName('reference');
        for (var i = 0; i < refs.length; i++) {
            input = refs[i];
            // autofocus
            input.addEventListener('click', function(event) {
                if (this.classList.contains('focused')) return;
                this.classList.add('focused');
                this.select();
            });
            input.addEventListener('blur', function (event) {
                this.classList.remove('focused');
            });
            // create button
            var copyLink = document.createElement('a');
            copyLink.setAttribute('href', '#');
            copyLink.appendChild(document.createTextNode("Copier la référence"));
            var icon = document.createElement('i');
            icon.classList.add('fas');
            icon.classList.add('fa-copy');
            copyLink.appendChild(icon);
            copyLink.classList.add('button');
            copyLink.setAttribute('data-tooltip', 'La référence a été placée dans le presse-papier.');
            // add events
            copyLink.addEventListener('click', function(event) {
                input.select();
                document.execCommand('copy');
                this.focus();
                this.classList.add('tooltip');
                this.classList.add('is-tooltip-active');
                event.preventDefault();
            });
            copyLink.addEventListener('mouseleave', function (event) {
                this.classList.remove('tooltip');
                this.classList.remove('is-tooltip-active');
            });
            copyLink.addEventListener('blur', function (event) {
                this.classList.remove('tooltip');
                this.classList.remove('is-tooltip-active');
            });
            // add button
            p = input.parentNode.nextElementSibling;
            p.insertBefore(copyLink, p.firstChild);
        }
    }
    // add link to close notification
    var e = document.getElementById('reference-box');
    var closeLink = document.createElement('a');
    closeLink.className = 'delete';
    closeLink.addEventListener('click', function() {
        e.parentNode.parentNode.style.display = 'none';
        window.history.pushState({'hidden': true}, document.title, baseRoute);
    });
    e.insertBefore(closeLink, e.childNodes[0]);

    window.onpopstate = function(event) {
        if (event.state && event.state.hidden) {
            e.parentNode.parentNode.style.display = 'none';
        } else {
            e.parentNode.parentNode.style.display = 'block';
        }
    };
});
