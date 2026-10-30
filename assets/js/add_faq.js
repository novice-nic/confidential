document.addEventListener("DOMContentLoaded", function () {
    const form          = document.getElementById("addFaqForm");
    const fileSelect    = document.getElementById("chooseFile");
    const section       = document.getElementById("docsSection");
    const tbody         = document.querySelector("#docsTable tbody");
    const checkAll      = document.getElementById("checkAll");
    const selectedCount = document.getElementById("selectedCount");
    const submitBtn     = form.querySelector('button[type="submit"]');

    let requestNo = 0; // ignores slow, outdated answers if the file is changed quickly

    // Build one cell with safe text
    function cell(text) {
        const td = document.createElement("td");
        td.textContent = text;
        return td;
    }

    // Shows how many documents are ticked
    function updateCount() {
        const n = tbody.querySelectorAll(".doc-check:checked").length;
        selectedCount.textContent = n + (n === 1 ? " document is selected" : " documents are selected");
    }

    // Keeps the select-all box in step with the row boxes
    function syncCheckAll() {
        const boxes  = tbody.querySelectorAll(".doc-check");
        const ticked = tbody.querySelectorAll(".doc-check:checked").length;
        checkAll.checked = boxes.length > 0 && ticked === boxes.length;
        checkAll.indeterminate = ticked > 0 && ticked < boxes.length;
        updateCount();
    }

    function showMessage(text) {
        selectedCount.textContent = "";
        tbody.innerHTML = "";
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 5;
        td.className = "text-center text-muted";
        td.textContent = text;
        tr.appendChild(td);
        tbody.appendChild(tr);
        checkAll.checked = false;
        checkAll.indeterminate = false;
        checkAll.disabled = true;
    }

    function renderRows(rows, selectedIds) {
        tbody.innerHTML = "";
        rows.forEach(function (r, i) {
            const tr = document.createElement("tr");

            const tdCheck = document.createElement("td");
            const box = document.createElement("input");
            box.type = "checkbox";
            box.className = "form-check-input doc-check";
            box.name = "document_ids[]";
            box.value = r.id;
            box.checked = selectedIds.includes(r.id); // pre-tick the saved ones
            tdCheck.appendChild(box);

            tr.appendChild(tdCheck);
            tr.appendChild(cell(i + 1));
            tr.appendChild(cell(r.type));
            tr.appendChild(cell(r.name));
            tr.appendChild(cell(r.date_fmt));
            tbody.appendChild(tr);
        });
        checkAll.disabled = false;
        syncCheckAll();
    }

    // Load the documents of a file (selectedIds = the ones to tick)
    async function loadDocuments(fileId, selectedIds) {
        const thisRequest = ++requestNo;
        section.classList.remove("d-none");
        showMessage("Loading...");

        try {
            const response = await fetch(
                "data_access/fetch_file_documents.php?file_id=" + encodeURIComponent(fileId)
            );
            const data = await response.json();
            if (thisRequest !== requestNo) return; // an older request, ignore it

            if (data.status !== "Success") {
                showMessage(data.message || "Could not load documents.");
                return;
            }
            if (data.data.length === 0) {
                showMessage("No documents found for this file.");
                return;
            }
            renderRows(data.data, selectedIds);
        } catch (error) {
            if (thisRequest !== requestNo) return;
            showMessage("Something went wrong. Please try again.");
        }
    }

    // When the file changes, load its documents
    fileSelect.addEventListener("change", function () {
        if (!fileSelect.value) {
            section.classList.add("d-none");
            return;
        }
        loadDocuments(fileSelect.value, []);
    });

    // Edit mode: load the saved file's documents and tick the linked ones
    const editFile = parseInt(form.dataset.editFile, 10) || 0;
    if (editFile > 0) {
        let saved = [];
        try {
            saved = JSON.parse(form.dataset.selected || "[]");
        } catch (e) {
            saved = [];
        }
        loadDocuments(editFile, saved.map(Number));
    }

    // Select-all checkbox
    checkAll.addEventListener("change", function () {
        tbody.querySelectorAll(".doc-check").forEach(function (box) {
            box.checked = checkAll.checked;
        });
        syncCheckAll();
    });

    // A single row box was clicked
    tbody.addEventListener("change", function (e) {
        if (e.target.classList.contains("doc-check")) {
            syncCheckAll();
        }
    });

    // ---------- Submit the form ----------
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Browser checks: file, question and answer
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }

        // At least one document must be ticked
        if (tbody.querySelectorAll(".doc-check:checked").length === 0) {
            Swal.fire({
                theme: "auto",
                icon: "warning",
                title: "Select a document",
                text: "Please select at least one document."
            });
            return;
        }

        submitBtn.disabled = true; // stop double submit

        try {
            const response = await fetch("data_access/add_faq_insert.php", {
                method: "POST",
                body: new FormData(form)
            });
            const data = await response.json();

            await Swal.fire({
                theme: "auto",
                icon: data.status === "Success" ? "success" : "error",
                title: data.status,
                text: data.message
            });

            if (data.status === "Success") {
                if (data.mode === "update") {
                    // Edit mode: go back to the report
                    window.location.href = "rpt_faq.php";
                } else {
                    // New FAQ: clear the form
                    requestNo++;
                    form.reset();
                    form.classList.remove("was-validated");
                    tbody.innerHTML = "";
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                    selectedCount.textContent = "";
                    section.classList.add("d-none");
                }
            }
        } catch (error) {
            Swal.fire({
                theme: "auto",
                icon: "error",
                title: "Request failed",
                text: "Something went wrong. Please try again."
            });
        } finally {
            submitBtn.disabled = false; // always enable the button again
        }
    });
});