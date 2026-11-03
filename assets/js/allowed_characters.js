// Rule 1: Allow letters, numbers, / and -
const allowSlashHyphen = /[^A-Za-z0-9/-]/g;

// Rule 2: Allow only letters and numbers
const allowAlphanumeric = /[^A-Za-z0-9 ]/g;

// Rule 3: Letters, numbers, spaces and the question mark
const allowQuestion = /[^A-Za-z0-9 ?]/g;

document.addEventListener("input", function (e) {
    if (e.target.classList.contains("allow-slash-hyphen")) {
        e.target.value = e.target.value.replace(allowSlashHyphen, "");
    }
    else if (e.target.classList.contains("allow-alphanumeric")) {
        e.target.value = e.target.value.replace(allowAlphanumeric, "");
    }
    else if (e.target.classList.contains("allow-question")) {
        e.target.value = e.target.value.replace(allowQuestion, "");
    }
});