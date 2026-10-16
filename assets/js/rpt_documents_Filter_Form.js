const form = document.getElementById('filterForm');

const pickers = flatpickr('.date-filter', {
    altInput: true,
    altFormat: 'd-m-Y', // what the user sees
    dateFormat: 'Y-m-d', // what is sent to PHP
    allowInput: true,
    maxDate: "today"
});

async function loadRows() {
    const qs = new URLSearchParams(new FormData(form)).toString();
    const res = await fetch('rpt_documents.php?' + qs);
    const html = await res.text();

    // Take the new rows from the returned page
    const page = new DOMParser().parseFromString(html, 'text/html');
    const newBody = page.querySelector('#docTable tbody');

    // If the session expired, the login page comes back: reload fully
    if (!newBody) {
        location.reload();
        return;
    }

    table.destroy();
    document.querySelector('#docTable tbody').innerHTML = newBody.innerHTML;
    table = new DataTable('#docTable', options);
}

form.addEventListener('submit', function (e) {
    e.preventDefault();
    loadRows();
});

document.getElementById('btnReset').addEventListener('click', function () {
    pickers.forEach(function (p) {
        p.clear();
    });
    form.querySelectorAll('input:not(.date-filter)').forEach(function (i) {
        i.value = '';
    });
    form.querySelectorAll('select').forEach(function (s) {
        s.selectedIndex = 0;
    });
    loadRows();
});