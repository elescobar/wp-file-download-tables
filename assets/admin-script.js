document.addEventListener("DOMContentLoaded", function() {
    let tablesContainer = document.getElementById('file-tables-container');
    let addTableBtn = document.getElementById('add-table');

    if (!tablesContainer || !addTableBtn) {
        console.error("File Tables UI elements not found.");
        return;
    }

    // Add new table
    addTableBtn.addEventListener('click', function() {
        let newTableId = prompt("Enter new table ID:");
        if (!newTableId) return;

        let tableHtml = `
            <div class="file-table" data-table-id="${newTableId}">
                <h2>Table ID: ${newTableId}</h2>
                <input type="text" class="table-id" value="${newTableId}" placeholder="Table ID">
                <button type="button" class="remove-table button">Remove Table</button>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Description</th>
                            <th>URL</th>
                            <th>Last Update</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="add-file button">Add File</button>
            </div>`;

        tablesContainer.insertAdjacentHTML('beforeend', tableHtml);
    });

    // Handle dynamic events using event delegation
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('add-file')) {
            let tbody = event.target.previousElementSibling.querySelector('tbody');
            let fileRowHtml = `<tr>
                <td><input type="text" class="file-name"></td>
                <td><input type="text" class="file-description"></td>
                <td><input type="text" class="file-url"></td>
                <td><input type="text" class="file-last-update"></td>
                <td><input type="text" class="file-size"></td>
                <td><button type="button" class="remove-file button">Remove</button></td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', fileRowHtml);
        }

        if (event.target.classList.contains('remove-file')) {
            event.target.closest('tr').remove();
        }

        if (event.target.classList.contains('remove-table')) {
            event.target.closest('.file-table').remove();
        }
    });
});
