document.addEventListener("DOMContentLoaded", function () {
    const fileSelect = document.getElementById("chooseFile");
    const section = document.getElementById("docsSection");
    const tbody = document.querySelector("#docsTable tbody");
    const checkAll = document.getElementById("checkAll");

    let requestNo = 0; // ignores slow, outdated answers if the file is changed quickly

    // Build one cell with safe text
    function cell(text) {
        const td = document.createElement("td");
        td.textContent = text;
        return td;
    }

    function showMessage(text) {
        tbody.innerHTML = "";
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 6;
        td.className = "text-center text-muted";
        td.textContent = text;
        tr.appendChild(td);
        tbody.appendChild(tr);
        checkAll.checked = false;
        checkAll.indeterminate = false;
        checkAll.disabled = true;
    }

    function renderRows(rows) {
        tbody.innerHTML = "";
        rows.forEach(function (r, i) {
            const tr = document.createElement("tr");

            const tdCheck = document.createElement("td");
            const box = document.createElement("input");
            box.type = "checkbox";
            box.className = "form-check-input doc-check";
            box.name = "document_ids[]";
            box.value = r.id;
            tdCheck.appendChild(box);

            tr.appendChild(tdCheck);
            tr.appendChild(cell(i + 1));
            tr.appendChild(cell(r.type));
            tr.appendChild(cell(r.name));
            tr.appendChild(cell(r.date_fmt));
            tr.appendChild(cell(r.remarks));
            tbody.appendChild(tr);
        });
        checkAll.checked = false;
        checkAll.indeterminate = false;
        checkAll.disabled = false;
    }

    // When the file changes, load its documents
    fileSelect.addEventListener("change", async function () {
        const fileId = fileSelect.value;
        if (!fileId) {
            section.classList.add("d-none");
            return;
        }

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
            renderRows(data.data);
        } catch (error) {
            if (thisRequest !== requestNo) return;
            showMessage("Something went wrong. Please try again.");
        }
    });

    // Select-all checkbox
    checkAll.addEventListener("change", function () {
        tbody.querySelectorAll(".doc-check").forEach(function (box) {
            box.checked = checkAll.checked;
        });
    });

    // Keep the select-all box in step with the row boxes
    tbody.addEventListener("change", function (e) {
        if (!e.target.classList.contains("doc-check")) return;
        const boxes = tbody.querySelectorAll(".doc-check");
        const ticked = tbody.querySelectorAll(".doc-check:checked").length;
        checkAll.checked = ticked === boxes.length;
        checkAll.indeterminate = ticked > 0 && ticked < boxes.length;
    });
});