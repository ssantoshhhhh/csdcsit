// Keep existing variable declarations
const fileInput = document.getElementById('excelFile');
const dropZone = document.getElementById('dropZone');
const tableBody = document.getElementById('tableBody');
const submitButton = document.getElementById('submitData');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
let originalData = [];

// Keep existing drag and drop handlers
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropZone.classList.add('dragover');
}

function unhighlight() {
    dropZone.classList.remove('dragover');
}

dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

fileInput.addEventListener('change', function() {
    handleFiles(this.files);
});

function handleFiles(files) {
    if (files.length > 0) {
        const file = files[0];
        processExcelFile(file);
    }
}

// Modified Excel processing function to filter out blank rows
function processExcelFile(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {
            type: 'array'
        });
        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];
        const jsonData = XLSX.utils.sheet_to_json(sheet, {
            header: 1,
            defval: '' // Set default value for empty cells
        });

        // Filter out header row and blank rows
        originalData = jsonData.slice(1).filter(row => {
            // Check if row has at least one non-empty value
            return row.length > 0 && row.some(cell => cell !== '');
        }).map(row => {
            // Ensure each row has exactly 3 elements
            return [
                row[0] || '', // Registration number
                row[1] || '', // Status
                row[2] || 0   // Points (default to 0 if empty)
            ];
        });

        renderTable(originalData);
    };
    reader.readAsArrayBuffer(file);
}

// Modified render table function to handle clean data
function renderTable(data) {
    tableBody.innerHTML = '';
    data.forEach((row, index) => {
        // Only render row if it has a registration number
        if (row[0]) {
            const tr = document.createElement('tr');
            tr.dataset.index = index;
            
            // Normalize status for display (lowercase for CSS class)
            const statusClass = row[1].toLowerCase();
            
            tr.innerHTML = `
                <td>${row[0]}</td>
                <td>
                    <span class="badge status-badge status-${statusClass}">${row[1]}</span>
                </td>
                <td>${row[2]}</td>
                <td>
                    <button class="btn btn-sm btn-warning btn-action edit-btn" data-index="${index}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-action delete-btn" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        }
    });

    submitButton.disabled = data.length === 0;
}

// Keep existing event delegation for edit and delete buttons
tableBody.addEventListener('click', function(e) {
    const target = e.target.closest('button');
    if (!target) return;

    const index = target.dataset.index;
    if (!index) return;

    if (target.classList.contains('edit-btn')) {
        handleEdit(index);
    } else if (target.classList.contains('delete-btn')) {
        handleDelete(index);
    } else if (target.classList.contains('save-btn')) {
        saveEdit(index);
    } else if (target.classList.contains('cancel-btn')) {
        cancelEdit(index);
    }
});

function handleEdit(index) {
    const row = originalData[index];
    const tr = tableBody.querySelector(`tr[data-index="${index}"]`);
    if (!tr) return;

    tr.innerHTML = `
        <td>${row[0]}</td>
        <td>
            <select class="form-select form-select-sm">
                <option value="Winner" ${row[1] === 'Winner' ? 'selected' : ''}>Winner</option>
                <option value="Runner" ${row[1] === 'Runner' ? 'selected' : ''}>Runner</option>
                <option value="Participate" ${row[1] === 'Participate' ? 'selected' : ''}>Participate</option>
            </select>
        </td>
        <td><input type="number" class="form-control form-control-sm" value="${row[2]}"></td>
        <td>
            <button class="btn btn-sm btn-success btn-action save-btn" data-index="${index}">
                <i class="bi bi-check-lg"></i>
            </button>
            <button class="btn btn-sm btn-secondary btn-action cancel-btn" data-index="${index}">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    `;
}

function saveEdit(index) {
    const tr = tableBody.querySelector(`tr[data-index="${index}"]`);
    if (!tr) return;

    const selects = tr.querySelectorAll('select');
    const status = selects[0].value;
    const add_on_pointsInput = tr.querySelector('input');
    const add_on_points = add_on_pointsInput.value.trim() ? parseInt(add_on_pointsInput.value) : 0;

    originalData[index] = [
        originalData[index][0], // Registration Number
        status,
        add_on_points // Ensure updated points are stored
    ];

    renderTable(originalData); // Re-render the table with updated values
    submitButton.disabled = false; // Enable submit button
}

function cancelEdit(index) {
    renderTable(originalData);
}

function handleDelete(index) {
    if (confirm('Are you sure you want to delete this row?')) {
        originalData.splice(index, 1);
        renderTable(originalData);
        submitButton.disabled = originalData.length === 0;
    }
}

// Modified filter function to handle clean data
function filterTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusTerm = statusFilter.value;

    const filteredData = originalData.filter(row => {
        if (!row[0]) return false; // Skip rows without registration number
        const matchSearch = row[0].toString().toLowerCase().includes(searchTerm);
        const matchStatus = !statusTerm || row[1] === statusTerm;
        return matchSearch && matchStatus;
    });

    renderTable(filteredData);
}

// Keep existing event listeners
searchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);

submitButton.addEventListener('click', function () {
    // Show loading notification
    toastr.info("Submitting data... Please wait.");

    // Prepare data to send
    const eventSelect = document.getElementById('eventSelect');
    const selectedEvent = eventSelect.value;

    if (!selectedEvent) {
        toastr.error('Please select an event before submitting.');
        return;
    }

    const dataToSend = originalData.map(row => ({
        registrationNumber: row[0],
        status: row[1],
        add_on_points: parseInt(row[2]) || 0,
        event: selectedEvent
    }));

    fetch('upload_bulkpoints.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dataToSend)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
        if (data.success) {
            toastr.success(`Successfully uploaded! Processed: ${data.processed}, Updated: ${data.updated}`);
            if (data.errors > 0) {
                toastr.warning(`There were ${data.errors} errors during processing. Check console for details.`);
                console.error('Error details:', data.error_details);
            }
        } else {
            toastr.error(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred during the upload: ' + error.message);
    });
});

// Keep existing download sample functionality
document.getElementById('downloadSample').addEventListener('click', () => {
    const sampleData = [
        ["registration_number", "status", "add_on_points"],
        ["22B91A6123", "Participate", "10"],
        ["22B91A6345", "Runner", "15"],
        ["22B91A6456", "Winner", "20"]
    ];
    const worksheet = XLSX.utils.aoa_to_sheet(sampleData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Sample");
    XLSX.writeFile(workbook, "SampleData.xlsx");
    
    toastr.info("Downloading sample Excel file...");
});

// Configure toastr notifications
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 5000
};