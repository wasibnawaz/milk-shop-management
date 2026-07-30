/*
| Submit-state handling for plain (non-AJAX) forms.
|
| Two problems this solves:
|   1. Double submission — on a slow connection an impatient second click
|      posts the same sale twice.
|   2. No feedback — the page appears frozen between click and navigation.
*/

const BUSY_ATTR = 'data-busy';

function markBusy(form) {
    const submitters = form.querySelectorAll('button[type="submit"], input[type="submit"]');

    submitters.forEach((button) => {
        // Buttons are disabled *after* the submit event has been dispatched,
        // so the button's own name/value still reaches the server.
        window.setTimeout(() => {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }, 0);

        if (button.dataset.busyLabel) {
            button.dataset.idleLabel = button.innerHTML;
            button.textContent = button.dataset.busyLabel;
        }
    });

    form.setAttribute(BUSY_ATTR, 'true');
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-busy')) {
        return;
    }

    // A form already submitting must not queue a second request.
    if (form.hasAttribute(BUSY_ATTR)) {
        event.preventDefault();

        return;
    }

    // Let the browser's own validation run first; a rejected form is not busy.
    if (!form.noValidate && typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return;
    }

    markBusy(form);
}, true);

/*
| Restoring from the back/forward cache replays the DOM as it was left —
| including disabled buttons. Clear the busy state so the form works again.
*/
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll(`form[${BUSY_ATTR}]`).forEach((form) => {
        form.removeAttribute(BUSY_ATTR);

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-busy');

            if (button.dataset.idleLabel) {
                button.innerHTML = button.dataset.idleLabel;
            }
        });
    });
});
