document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".wp-fdt-table th").forEach((header) => {
        header.addEventListener("click", function () {
            let table = header.closest("table");
            let tbody = table.querySelector("tbody");
            let rows = Array.from(tbody.querySelectorAll("tr"));
            let index = Array.from(header.parentNode.children).indexOf(header);
            let ascending = header.dataset.order === "asc" ? false : true;

            rows.sort((rowA, rowB) => {
                let cellA = rowA.children[index].textContent.trim().toLowerCase();
                let cellB = rowB.children[index].textContent.trim().toLowerCase();

                return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            });

            header.dataset.order = ascending ? "asc" : "desc";

            rows.forEach((row) => tbody.appendChild(row));
        });
    });

    document.querySelectorAll(".wp-fdt-filter").forEach((input) => {
        input.addEventListener("input", function () {
            let table = this.closest(".wp-fdt-container").querySelector(".wp-fdt-table");
            let filterValue = this.value.toLowerCase();
            let rows = table.querySelectorAll("tbody tr");

            rows.forEach((row) => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filterValue) ? "" : "none";
            });
        });
    });
});
