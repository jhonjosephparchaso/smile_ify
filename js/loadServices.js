function openServicesModal() {
    const modal = document.getElementById("servicesModal");
    modal.style.display = "flex";
    loadServices();
}

function closeServicesModal() {
    document.getElementById("servicesModal").style.display = "none";
}

function loadServices() {
    const container = document.getElementById("serviceContainer");
    container.innerHTML = "<p>Loading...</p>";

    fetch(`${BASE_URL}/processes/fetch_services.php`)
        .then(response => response.json())
        .then(data => {

            if (data.success && data.services.length > 0) {
                container.innerHTML = data.services
                    .map(service => `
                        <div class="services-card">
                                <h3>${service.service_name}</h3>

                                <div class="service-details">
                                    <p><span>Price:</span> ₱${service.price}</p>
                                    <p><span>Duration:</span> ${service.duration}</p>
                                </div>
                            
                        </div>
                    `)
                    .join("");
            } else {
                container.innerHTML = "<p>No service found.</p>";
            }
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = "<p>Error loading services.</p>";
        });
}
