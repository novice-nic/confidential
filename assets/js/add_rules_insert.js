document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addRulesForm");
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }

        submitBtn.disabled = true; // stop double submit

        try {
            const response = await fetch("data_access/add_rules_insert.php", {
                method: "POST",
                body: new FormData(form),
            });
            const data = await response.json();

            Swal.fire({
                theme: "auto",
                icon: data.status === "Success" ? "success" : "error",
                title: data.status,
                text: data.message,
            });

            if (data.status === "Success") {
                form.reset();
                document.getElementById("formDate")._flatpickr.clear();
                form.classList.remove("was-validated");
            }
        } catch (error) {
            Swal.fire({
                theme: "auto",
                icon: "error",
                title: "Request failed",
                text: "Something went wrong. Please try again.",
            });
        } finally {
            submitBtn.disabled = false; // always enable the button again
        }
    });
});