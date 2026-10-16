// Delete button (works on every page of the table)
document.querySelector('#filesTable tbody').addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return; // the click was not on a Delete button

    const id = btn.dataset.id;

    const result = await Swal.fire({
        theme: 'auto',
        icon: 'warning',
        title: 'Are you sure?',
        text: 'Do you want to delete this file?',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#dc2626'
    });
    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('data_access/file_master_delete.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'Success') {
            const page = table.page(); // remember the current page

            // Remove the row. draw(false) keeps us on the same page.
            table.row(btn.closest('tr')).remove().draw(false);

            // If that page is now empty, go back one page
            const pages = table.page.info().pages;
            if (pages > 0 && page >= pages) {
                table.page(pages - 1).draw(false);
            }

            Swal.fire({
                theme: 'auto',
                icon: 'success',
                title: 'Deleted',
                text: data.message
            });
        } else {
            Swal.fire({
                theme: 'auto',
                icon: 'error',
                title: 'Cannot delete',
                text: data.message
            });
        }
    } catch (error) {
        Swal.fire({
            theme: 'auto',
            icon: 'error',
            title: 'Request failed',
            text: 'Something went wrong. Please try again.'
        });
    }
});