function openDentistsModal() {
    const modal = document.getElementById("dentistsModal");
    modal.style.display = "flex";
    loadDentists();
}

function closeDentistsModal() {
    document.getElementById("dentistsModal").style.display = "none";
}

function loadDentists() {
    const container = document.getElementById("dentistsContainer");
    container.innerHTML = "<p>Loading...</p>";

    fetch(`${BASE_URL}/processes/fetch_dentists.php`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.dentists.length > 0) {
                container.innerHTML = data.dentists
                    .map(
                        (d) => `
                        <div class="dentist-card">
                            <img src="${d.profile_image}" alt="${d.dentist_name}" class="dentist-photo" />

                            <div class="dentist-info">
                                <h3>${d.dentist_name}</h3>

                                <div class="dentist-details">
                                    <p><span>Branch:</span> ${d.branch_name}</p>
                                    <p><span>Email:</span> ${d.email}</p>
                                    <p><span>Contact:</span> ${d.contact_number}</p>
                                    <p><span>Services:</span> ${d.services}</p>

                                    <div class="dentist-schedule">
                                        <p><span>Schedule:</span></p>
                                        <ul>
                                            ${
                                                d.schedule.length > 0
                                                    ? d.schedule
                                                        .map(
                                                            (s) =>
                                                                `<li>${s.day} – ${s.branch} (${s.time})</li>`
                                                        )
                                                        .join("")
                                                    : "<li>No schedule</li>"
                                            }
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `
                    )
                    .join("");
            } else {
                container.innerHTML = "<p>No dentists found.</p>";
            }
        })
        .catch(() => {
            container.innerHTML = "<p>Error loading dentists.</p>";
        });
}
