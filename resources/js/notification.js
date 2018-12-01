document.addEventListener("DOMContentLoaded", function(event) {
    // progressive enhancement: only add if window.getSelection() is supported
    if (window.getSelection && document.queryCommandSupported('copy')) {
        // handle reference (size and copy)
        var refs = document.getElementsByClassName('reference');
        for (var i = 0; i < refs.length; i++) {
            el = refs[i];

            // resize dynamically
            window.addEventListener('resize', function() {
                el.style.height = 'auto';
                el.style.height = el.scrollHeight + 'px';
            });
            // initial size
            setTimeout(function() {
                var styles = window.getComputedStyle(el);
                var canvas = document.createElement("canvas");
                if (canvas.getContext && canvas.getContext("2d")) {
                    var context = canvas.getContext("2d")
                    context.font = styles.getPropertyValue('font-size') + ' ' + styles.getPropertyValue('font-family');
                    el.style.width = (Math.floor(context.measureText(el.value).width) + 10) + 'px';
                }
            }, 25);
            setTimeout(function() {
                var styles = window.getComputedStyle(el);
                var lineHeight = parseInt(styles.getPropertyValue('line-height'));
                var realHeight = parseInt(styles.getPropertyValue('height'));
                if (el.scrollHeight > realHeight) {
                    el.style.height = el.scrollHeight + 'px';
                }
            }, 50);

            // create copy button
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
                el.select();
                document.execCommand('copy');
                el.blur();
                this.focus();
                this.classList.add('tooltip');
                this.classList.add('is-tooltip-active');
                event.preventDefault();
            });
            copyLink.addEventListener('mouseleave', function(event) {
                this.classList.remove('tooltip');
                this.classList.remove('is-tooltip-active');
            });
            copyLink.addEventListener('blur', function(event) {
                this.classList.remove('tooltip');
                this.classList.remove('is-tooltip-active');
            });
            // add button
            p = el.parentNode.nextElementSibling;
            if (p instanceof Element) {
                p.insertBefore(copyLink, p.firstChild);
            } else {
                el.parentNode.appendChild(copyLink);
            }
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
