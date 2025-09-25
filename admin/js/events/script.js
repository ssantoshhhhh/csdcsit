const notyf = new Notyf({
    duration: 3000,
    position: {
        x: 'right',
        y: 'top'
    }
});

const eventsGrid = document.getElementById('eventsGrid');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const statusFilter = document.getElementById('statusFilter');

function toggleRegistrations(eventId) {
    const registrationsSection = document.getElementById(`registrations-${eventId}`);
    const toggleBtn = document.querySelector(`[data-event-id="registrations-${eventId}"]`);

    registrationsSection.classList.toggle('show');
    registrationsSection.style.display = registrationsSection.classList.contains('show') ? 'block' : 'none';

    toggleBtn.innerHTML = registrationsSection.classList.contains('show') ?
        'Hide Registrations' :
        'View Registrations';
}

function toggleOrganisers(eventId) {
    const organisersSection = document.getElementById(`organisers-${eventId}`);
    const toggleBtn = document.querySelector(`[data-event-id="organisers-${eventId}"]`);

    organisersSection.classList.toggle('show');
    organisersSection.style.display = organisersSection.classList.contains('show') ? 'block' : 'none';

    toggleBtn.innerHTML = organisersSection.classList.contains('show') ?
        'Hide Organisers' :
        'View Organisers';
}

function renderRegistrationsTable(registrationsList) {
    return registrationsList.map(reg => `
        <tr>
            <td>${reg.name}</td>
            <td>${reg.registration_number}</td>
        </tr>
    `).join('');
}

function renderOrganisersTable(organisersList) {
    return organisersList.map(org => `
        <tr>
            <td>${org.name}</td>
            <td>${org.username}</td>
        </tr>
    `).join('');
}

document.addEventListener('click', function (e) {
    let deleteButton = e.target.closest('.delete-btn'); 
    if (deleteButton) {
        let eventId = deleteButton.getAttribute('delete'); 

        if (!eventId) {
            alert("Error: Event ID is missing.");
            return;
        }

        if (confirm("Are you sure you want to delete this event?")) {
            fetch(`delete_event.php?event_id=${eventId}`, {
                method: "GET"
            })
            .then(response => response.json()) 
            .then(data => {
                if (data.status === "success") {
                    alert("Event deleted successfully!");
                    location.reload();
                } else {
                    alert("Failed to delete the event: " + data.message);
                }
            })
            .catch(error => console.error("Error deleting event:", error));
        }
    }
});

function updateEvent(eventId) {
    window.location.href = `update_event.php?event_id=${eventId}`;
}

function renderEvents(data) {
    eventsGrid.innerHTML = '';

    data.forEach(event => {
        const today = new Date();
        const eventDate = new Date(event.event_date);
        const status = eventDate > today ? 'Upcoming' : 'Past';

        const eventName = event.title || 'No name available';
        const eventDateFormatted = new Date(event.event_date).toLocaleDateString() || 'No date available';
        // Handle both old and new image path formats for admin display
        let eventPoster = event.image_path || 'https://via.placeholder.com/500x250.png?text=No+Poster';
        if (eventPoster && eventPoster.startsWith('admin/pages/')) {
            // Remove 'admin/pages/' prefix for admin display
            eventPoster = eventPoster.substring(12);
        }

        const eventCard = document.createElement('div');
        eventCard.className = 'col-md-4 mb-4';

        let organiserDetailsHTML = event.organisers.length > 0 ? `
            <div id="organisers-${event.event_id}" class="organiser-details p-3" style="display: none;">
    <input type="text" class="form-control mb-2 organiser-search" placeholder="Search organisers..." 
        onkeyup="searchTable(this, 'organisers-table-${event.event_id}')">
    <br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
            </tr>
        </thead>
        <tbody id="organisers-table-${event.event_id}">
            ${renderOrganisersTable(event.organisers)}
        </tbody>
    </table>
</div>

        ` : '<p>No organisers listed.</p>';

        eventCard.innerHTML = `
            <div class="card event-card position-relative">
                <span class="badge bg-${status === 'Upcoming' ? 'success' : 'secondary'} position-absolute top-0 end-0 m-2">
                    ${status}
                </span>
                <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                    <i class="bi bi-people-fill"></i> ${event.registrationsList.length} Registered
                </span>
                <img src="${eventPoster}" class="card-img-top event-poster" alt="${eventName} Poster">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">${eventName}</h5>
                        <div>
                            <button class="btn btn-sm btn-danger delete-btn" delete="${event.event_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-primary update-btn" onclick="updateEvent(${event.event_id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </div>
                    <p class="card-text text-muted">${event.description || 'No description available'}</p>

                    <div class="text-start my-2">
    <button class="btn btn-sm btn-outline-secondary"
        data-event-id="organisers-${event.event_id}"
        onclick="toggleOrganisers(${event.event_id})">
        View Organisers
    </button>
</div>

                    ${organiserDetailsHTML}

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i> 
                            ${eventDateFormatted}
                        </small>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" 
                                data-event-id="registrations-${event.event_id}"
                                onclick="toggleRegistrations(${event.event_id})">
                                View Registrations
                            </button>
                        </div>
                    </div>
                </div>
                <div id="registrations-${event.event_id}" class="registration-details p-3" style="display: none;">
    <input type="text" class="form-control mb-2 registration-search" placeholder="Search registrations..." 
        onkeyup="searchTable(this, 'registrations-table-${event.event_id}')">
    <br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Registration Number</th>
            </tr>
        </thead>
        <tbody id="registrations-table-${event.event_id}">
            ${renderRegistrationsTable(event.registrationsList)}
        </tbody>
    </table>
</div>

        `;

        eventsGrid.appendChild(eventCard);
    });
}

function filterEvents() {
    const searchTerm = searchInput.value.toLowerCase();
    const categoryTerm = categoryFilter.value;
    const statusTerm = statusFilter.value;

    const today = new Date();

    const filteredEvents = events.filter(event => {
        const matchSearch = event.title.toLowerCase().includes(searchTerm);
        const matchCategory = !categoryTerm || event.category === categoryTerm;
        const eventDate = new Date(event.event_date);
        const matchStatus = !statusTerm ||
            (statusTerm === 'upcoming' && eventDate > today) ||
            (statusTerm === 'past' && eventDate <= today);

        return matchSearch && matchCategory && matchStatus;
    });

    renderEvents(filteredEvents);
}

searchInput.addEventListener('input', filterEvents);
categoryFilter.addEventListener('change', filterEvents);
statusFilter.addEventListener('change', filterEvents);

document.addEventListener('DOMContentLoaded', () => {
    renderEvents(events);
});
function searchTable(input, tableId) {
    let filter = input.value.toLowerCase();
    let table = document.getElementById(tableId);
    
    if (!table) {
        console.error(`Table with ID '${tableId}' not found`);
        return;
    }

    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) { // Start from 1 to avoid header row
        let cells = rows[i].getElementsByTagName("td");
        let rowMatch = false;
        
        for (let cell of cells) {
            if (cell.textContent.toLowerCase().includes(filter)) {
                rowMatch = true;
                break;
            }
        }

        rows[i].style.display = rowMatch ? "" : "none";
    }
}

