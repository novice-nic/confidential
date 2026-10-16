document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("fileMasterForm");
    // Allowed Characters Validation 
    document.getElementById("formNumber").addEventListener("input", function () {
        this.value = this.value.replace(/[^A-Za-z0-9\/-]/g, "");
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        // const result = await Swal.fire({
        //     title: "Are you sure?",
        //     text: "Do you want to save this file?",
        //     icon: "question",
        //     showCancelButton: true,
        //     confirmButtonText: "Yes, save",
        // });

        // if (!result.isConfirmed) return;

        try {
            const formdata = new FormData(form);

            const response = await fetch('data_access/file_master_insert.php', {
                method: 'POST',
                body: formdata
            });

            // if (!response.ok) {
            //     Swal.fire({
            //         theme: "auto",
            //         icon: "error",
            //         title: "Server error",
            //         text: "Server returned an error (" + response.status + "). Please try again."
            //     });
            //     return;
            // }

            const data = await response.json();

            if (data.status === "Success") {
                const isUpdate = data.mode === "update";
                await Swal.fire({
                    theme: "auto",
                    icon: "success",
                    title: "Success",
                    text: data.message
                });
                if (isUpdate) {
                    // Edit mode: go back to the report
                    window.location.href = "rpt_files.php"
                } else {
                    // New Record: Clear the form
                    form.reset();
                    form.classList.remove("was-validated")
                }
            } else {
                Swal.fire({
                    theme: "auto",
                    icon: "error",
                    title: "Error",
                    text: data.message,
                });
            }
        } catch (error) {
            Swal.fire({
                theme: "auto",
                icon: "error",
                title: "Request failed",
                text: "Something went wrong. Please try again.",
            });
        }
    });

});