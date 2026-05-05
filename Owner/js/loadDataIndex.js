document.addEventListener("DOMContentLoaded", function () {
    function loadRevenueThisMonth() {
        const container = document.getElementById("revenueThisMonthContainer");
        if (!container) return;

        fetch(`${BASE_URL}/Owner/processes/index/get_revenue_this_month.php`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<div class="appointment">₱ ---</div>`;
                    console.error(data.error);
                    return;
                }

                const formattedRevenue = `₱${parseFloat(data.current_revenue || 0).toLocaleString()}`;
                const branchesWithRevenue = (data.branches || []).filter(b => parseFloat(b.total_revenue) > 0);

                let branchHTML = "";
                if (branchesWithRevenue.length > 0) {
                    branchHTML = `
                        <hr>
                        ${branchesWithRevenue.map(b => `
                            <div class="appointment">
                                <strong>${b.name}</strong><br>
                                ₱${parseFloat(b.total_revenue).toLocaleString()}
                            </div>
                        `).join("")}
                    `;
                } else {
                    branchHTML = `<hr><div class="appointment">No branch revenue data</div>`;
                }

                container.innerHTML = `
                    <div class="appointment"><strong>Total:</strong> ${formattedRevenue}</div>
                    ${branchHTML}
                `;
            })
            .catch(error => {
                console.error("Error loading revenue data:", error);
                container.innerHTML = `<div class="appointment">₱ ---</div>`;
            });
    }

    function loadBranchPerformance() {
        const container = document.getElementById("branchPerformanceContainer");
        if (!container) return;

        fetch(`${BASE_URL}/Owner/processes/index/get_branch_performance.php`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<div class="appointment">Error: ${data.error}</div>`;
                    console.error(data.error);
                    return;
                }

                if (data.branches?.length > 0) {
                    container.innerHTML = data.branches.map(b => `
                        <div class="appointment">
                            <strong>${b.name}</strong><br>
                            Revenue: <span>₱${parseFloat(b.total_revenue).toLocaleString()}</span>
                        </div>
                    `).join("");
                } else {
                    container.innerHTML = `<div class="appointment">No branch data available</div>`;
                }
            })
            .catch(error => {
                console.error("Error loading branch performance:", error);
                container.innerHTML = `<div class="appointment">Failed to load data</div>`;
            });
    }

    function loadAppointmentsOverview() {
        const container = document.getElementById("appointmentsOverviewContainer");
        if (!container) return;

        fetch(`${BASE_URL}/Owner/processes/index/get_appointments_overview.php`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<div class="appointment">Error: ${data.error}</div>`;
                    console.error(data.error);
                    return;
                }

                const branchesWithBookings = (data.overview || []).filter(b => parseInt(b.total_booked) > 0);

                if (branchesWithBookings.length > 0) {
                    container.innerHTML = branchesWithBookings.map(b => `
                        <div class="appointment">
                            <strong>${b.name}</strong><br>
                            Total Booked: <span>${b.total_booked}</span>
                        </div>
                    `).join("");
                } else {
                    container.innerHTML = `<div class="appointment">No appointment data available</div>`;
                }
            })
            .catch(error => {
                console.error("Error loading appointments overview:", error);
                container.innerHTML = `<div class="appointment">Failed to load data</div>`;
            });
    }

    loadRevenueThisMonth();
    loadBranchPerformance();
    loadAppointmentsOverview();
});
