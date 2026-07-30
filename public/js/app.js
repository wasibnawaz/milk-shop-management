// this code for pagination
$(document).ready(function () {
    const itemsPerPage = 15;
    const totalRows = $('#myTable tbody tr').length;
    const totalPages = Math.ceil(totalRows / itemsPerPage);
    let currentPage = 1;

    function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        $('#myTable tbody tr').hide().slice(start, end).show();
    }

    function updatePagination() {
        const paginationContainer = $('#pagination');
        paginationContainer.empty();

        // Previous link
        const previousLink = $('<a href="#">').html('&lt;');
        previousLink.click(function () {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
                updateActiveRow();
                updatePagination();
            }
        });
        paginationContainer.append(previousLink);

        const visiblePages = 5;
        const startPage = Math.max(1, currentPage - Math.floor(visiblePages / 2));
        const endPage = Math.min(totalPages, startPage + visiblePages - 1);

        for (let i = startPage; i <= endPage; i++) {
            const pageLink = $('<a href="#">').text(i);

            if (i === currentPage) {
                pageLink.addClass('active');
            }

            pageLink.click(function () {
                currentPage = i;
                showPage(currentPage);
                updateActiveRow();
                updatePagination();
            });

            paginationContainer.append(pageLink);
        }

        // Next link
        const nextLink = $('<a href="#">').html('&gt;');
        nextLink.click(function () {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
                updateActiveRow();
                updatePagination();
            }
        });
        paginationContainer.append(nextLink);
    }

    function updateActiveRow() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        $('#myTable tbody tr').removeClass('active');
        $('#myTable tbody tr').slice(startIndex, endIndex).addClass('active');
    }

    showPage(currentPage);
    updatePagination();
    updateActiveRow();
});

// <![CDATA[  <-- For SVG support
if ('WebSocket' in window) {
    (function () {
        function refreshCSS() {
            var sheets = [].slice.call(document.getElementsByTagName("link"));
            var head = document.getElementsByTagName("head")[0];
            for (var i = 0; i < sheets.length; ++i) {
                var elem = sheets[i];
                var parent = elem.parentElement || head;
                parent.removeChild(elem);
                var rel = elem.rel;
                if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
                    var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
                    elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
                }
                parent.appendChild(elem);
            }
        }
        var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
        var address = protocol + window.location.host + window.location.pathname + '/ws';
        var socket = new WebSocket(address);
        socket.onmessage = function (msg) {
            if (msg.data == 'reload') window.location.reload();
            else if (msg.data == 'refreshcss') refreshCSS();
        };
        if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
            console.log('Live reload enabled.');
            sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
        }
    })();
}
else {
    console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
}
// ]]>

// Get all elements with class 'auto-next'
var inputs = document.getElementsByClassName('auto-next');

for (var i = 0; i < inputs.length; i++) {
    // Attach an event listener to each input
    inputs[i].addEventListener('input', function () {
        // If the current input has reached its maxlength, focus on the next input
        if (this.value.length == this.maxLength) {
            var index = Array.prototype.indexOf.call(inputs, this);
            if (index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        }
    });
}

// Get the input element
var myInput = document.getElementById('myInput');

// Attach an event listener to the document
document.addEventListener('keydown', function (event) {
    // Check if the combination Shift + S is pressed
    if (event.shiftKey && event.key === 'S') {
        // Prevent the default browser behavior for Shift + S
        event.preventDefault();

        // Set the value of the input field to "subhan"
        myInput.value = 'Subhan';
    }

    // Check if the combination Shift + F is pressed
    if (event.shiftKey && event.key === 'F') {
        // Prevent the default browser behavior for Shift + F
        event.preventDefault();

        // Set the value of the input field to "faizan"
        myInput.value = 'Faizan';
    }

    // Check if the combination Shift + R is pressed
    if (event.shiftKey && event.key === 'R') {
        // Prevent the default browser behavior for Shift + R
        event.preventDefault();

        // Set the value of the input field to "rehan"
        myInput.value = 'Rehan';
    }
});