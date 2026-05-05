let charts = {};
const branchColors = {};

const colorPalette = [
    '#1abc9c', '#3498db', '#9b59b6', '#f1c40f',
    '#e74c3c', '#2ecc71', '#34495e', '#16a085',
    '#d35400', '#7f8c8d'
];

function getBranchColor(branchName) {
    if (!branchColors[branchName]) {
        const existingColors = Object.values(branchColors);
        const availableColors = colorPalette.filter(c => !existingColors.includes(c));
        branchColors[branchName] = availableColors.length > 0
            ? availableColors[0]
            : colorPalette[Object.keys(branchColors).length % colorPalette.length];
    }
    return branchColors[branchName];
}

document.addEventListener("DOMContentLoaded", function () {
    const hasAdminBranch = typeof ADMIN_BRANCH_ID !== "undefined" && ADMIN_BRANCH_ID;
    const hasUserRole = typeof USER_ROLE !== "undefined" ? USER_ROLE.toLowerCase() : "";

    if (hasUserRole === "admin" && hasAdminBranch) {
        switchSubTab("all", "daily");
        switchSubTab(ADMIN_BRANCH_ID, "daily");
    } else if (hasUserRole === "owner") {
        document.querySelectorAll(".tab-content").forEach(tab => {
            const branch_id = tab.id.replace("branch", "");
            switchSubTab(branch_id, "daily");
        });
    } else if (hasAdminBranch) {
        switchSubTab(ADMIN_BRANCH_ID, "daily");
    } else {
        document.querySelectorAll(".tab-content").forEach(tab => {
            const branch_id = tab.id.replace("branch", "");
            switchSubTab(branch_id, "daily");
        });
    }
});

function switchTab(branch_id) {
    document.querySelectorAll(".tab, .tab-content").forEach(el => el.classList.remove("active"));
    const activeTab = document.querySelector(`.tab[onclick="switchTab('${branch_id}')"]`);
    const activeContent = document.getElementById(branch_id);
    if (activeTab) activeTab.classList.add("active");
    if (activeContent) activeContent.classList.add("active");
}

function switchSubTab(branch_id, mode) {

    const container = document.getElementById(`branch${branch_id}`);
    if (!container) return;

    container.querySelectorAll(".sub-tab, .sub-tab-content").forEach(el => el.classList.remove("active"));

    const activeSubTab = container.querySelector(`.sub-tab[onclick*="${mode}"]`);
    const activeContent = container.querySelector(`#branch${branch_id}-${mode}`);

    if (activeSubTab) activeSubTab.classList.add("active");
    if (activeContent) activeContent.classList.add("active");

    loadReports(branch_id, mode);
}

function loadReports(branch_id, mode) {
    fetch(`/Smile-ify/processes/fetch_Reports.php?branch_id=${branch_id}&mode=${mode}`)
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            console.error("Error:", data.error);
            return;
        }

    if (branch_id === "all") {

        const tabContent = document.getElementById(`branch${branch_id}-${mode}`);
        if (!tabContent) return;

        tabContent.querySelectorAll("tbody").forEach(tbody => tbody.innerHTML = "");

        Object.keys(charts).forEach(key => {
            if (key.includes(`branchGrowth${branch_id}-${mode}`) || key.includes(`decline${branch_id}-${mode}`)) {
                charts[key].destroy();
                delete charts[key];
            }
        });

        renderBranchGrowthTable(branch_id, mode, data.branchGrowthData);
        renderBranchGrowthChart(branch_id, mode, data.branchGrowthChartData, 'line');
        renderDeclineTable(branch_id, mode, data.declineData);
        renderDeclineChart(branch_id, mode, data.declineData, 'bar');
        return;
    }

        updateKPI(branch_id, mode, data.kpi);

        [
            "appointments",
            "servicesTrend",
            "incomeTrend",
            "branchComparison",
            "servicesBreakdown",
            "staffPerformance",
            "patientMix",
            "branchGrowth",
            "decline",
            "peakHours"
        ].forEach(chartName => destroyChart(`${chartName}${branch_id}-${mode}`));

        renderAppointmentsChart(branch_id, mode, data.appointments);
        renderServicesTrendChart(branch_id, mode, data.trend);
        renderIncomeTrendChart(branch_id, mode, data.trend);
        renderBranchComparisonChart(branch_id, mode, data.branchComparison);
        renderServicePricesTable(branch_id, mode, data.servicePrices);
        renderStaffPerformanceTable(branch_id, mode, data.staffPerformance);
        
        if (mode === 'weekly' || mode === 'monthly') {
            renderGrowthTrendChart(branch_id, mode, data.growthTrend);
        } else if (mode === 'daily') {
        
            const growthChartKey = `growthTrend${branch_id}-${mode}`;
            if (charts[growthChartKey]) {
                charts[growthChartKey].destroy();
                delete charts[growthChartKey];
            }
            const growthContainer = document.getElementById(`growthTrendChart${branch_id}-${mode}`)?.parentElement;
            if (growthContainer) growthContainer.style.display = 'none';
        }

        renderPatientMixChart(branch_id, mode, data.patientMix);
        renderPeakHoursChart(branch_id, mode, data.peakHours);
        renderServicesBreakdownChart(branch_id, mode, data.servicesBreakdown);
        renderServicesBreakdownTable(branch_id, mode, data.servicesBreakdown);
        renderPromosTable(branch_id, mode, data.promosAvailed);
        renderPromosChart(branch_id, mode, data.promosAvailed);
    })
    .catch(err => console.error("Fetch error:", err));
}

function renderBranchGrowthTable(branch_id, mode, branchGrowthData) {
    const tbody = document.getElementById(`branchGrowthTableBody${branch_id}-${mode}`);
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!branchGrowthData || branchGrowthData.length === 0) {
        return; 
    }
    const formatOrBlank = (value) => {
        const num = Number(value);
        if (!value || isNaN(num) || num === 0) return '';
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    branchGrowthData.forEach(branch => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${branch.branch_name}</strong></td>
            <td style="text-align: right;">${formatOrBlank(branch.revenue)}</td>
            <td style="text-align: right;">${branch.percentage && Number(branch.percentage) !== 0 ? branch.percentage : ''}</td>
        `;
        tbody.appendChild(row);
    });
}

function renderBranchGrowthChart(branch_id, mode, branchGrowthChartData, chartType = 'line') {
    const ctx = document.getElementById(`branchGrowthChart${branch_id}-${mode}`);
    const key = `branchGrowth${branch_id}-${mode}`;
    
    if (!ctx) {
        console.error(`Canvas element not found: branchGrowthChart${branch_id}-${mode}`);
        return;
    }

    if (charts[key]) {
        charts[key].destroy();
        delete charts[key];
    }

    if (!branchGrowthChartData || !branchGrowthChartData.labels || !branchGrowthChartData.datasets) {
        console.warn(`No data for Branch Growth Chart (${mode})`);
        return;
    }

    const datasets = branchGrowthChartData.datasets.map((dataset, i) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: getBranchColor(dataset.label), 
        backgroundColor: 'rgba(0,0,0,0)', 
        tension: 0.3, 
        pointBackgroundColor: getBranchColor(dataset.label),
        pointRadius: 5, 
        fill: false 
    }));

    let xLabel = 'Date';
    if (mode === 'weekly') xLabel = 'Day of Week';
    else if (mode === 'monthly') xLabel = 'Month';

    charts[key] = new Chart(ctx, {
        type: chartType,
        data: {
            labels: branchGrowthChartData.labels,
            datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    offset: true,
                    title: {
                        display: true,
                        text: xLabel,
                        font: { size: 13, weight: 'bold' },
                        color: '#333'
                    },
                    ticks: {
                        color: '#333',
                        font: { size: 12, weight: 'bold' }
                    }
                },
                y: {
                    beginAtZero: true,
                    grace: '40%',
                    title: {
                        display: true,
                        text: 'Revenue (₱)',
                        font: { size: 13, weight: 'bold' },
                        color: '#333'
                    },
                    ticks: {
                        precision: 0,
                        callback: value => Math.round(value).toLocaleString('en-US')
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#333',
                        font: { size: 12, weight: 'bold' },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const label = ctx.dataset.label || '';
                            const value = ctx.parsed.y;
                            return `${label}: ₱${value.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: 8,
                    color: (context) => {
                        return context.dataset.pointBackgroundColor || context.dataset.borderColor;
                    },
                font: {
                    size: 15,
                    weight: 'bold',
                    family: 'SF Pro Text, sans-serif',
                },
                formatter: (value) => value !== 0 ? Number(value).toLocaleString() : null
            }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderDeclineTable(branch_id, mode, declineData) {
    const tbody = document.getElementById(`declineTableBody${branch_id}-${mode}`);
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!declineData.length) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#999">No data</td></tr>`;
        return;
    }
    declineData.forEach(b => {
    tbody.innerHTML += `
        <tr>
            <td><strong>${b.branch_name}</strong></td>
            <td>${b.previous_count}</td>
            <td>${b.current_count}</td>
            <td>${b.decline}</td>
            <td>${b.percentage}%</td>
        </tr>`;
    });
}

function renderDeclineChart(branch_id, mode, declineData, chartType = 'bar') {
    const ctx = document.getElementById(`declineChart${branch_id}-${mode}`);
    const key = `decline${branch_id}-${mode}`;
    if (!ctx || !declineData.length) return;

    if (charts[key]) {
        charts[key].destroy();
        delete charts[key];
    }

    const labels = declineData.map(b => b.branch_name);
    const data = declineData.map(b => b.decline);
    const bg = labels.map(branch => getBranchColor(branch));

    charts[key] = new Chart(ctx, {
        type: chartType,
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: bg,
                borderColor: '#ffffff',
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: chartType === 'bar' ? 'y' : undefined,
            scales: chartType === 'bar' ? {
                x: {
                    beginAtZero: true,
                    grace: '20%',
                    title: { display: true, text: 'Decline Count' }
                },
                y: { title: { display: true, text: 'Branch' } }
            } : {},
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: '#333',
                        font: { size: 12, weight: 'bold', family: 'Poppins, sans-serif' },
                        padding: 15,
                        generateLabels(chart) {
                            const bgc = chart.data.datasets[0].backgroundColor;
                            return chart.data.labels.map((label, i) => {
                                const val = chart.data.datasets[0].data[i];
                                const pct = declineData[i].percentage;
                                return {
                                    text: `${label}: ${val} (${pct}%)`,
                                    fillStyle: bgc[i],
                                    strokeStyle: '#ffffff',
                                    lineWidth: 2,
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const i = ctx.dataIndex;
                            const val = ctx.dataset.data[i];
                            const pct = declineData[i].percentage;
                            return `${ctx.label}: ${val} (${pct}%)`;
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#333',
                    font: { weight: 'bold' },
                    formatter(value) {
                        return `${value}`;
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    const btn = document.getElementById(`toggleDeclineChart${branch_id}-${mode}`);
    let currentType = chartType;
    btn.textContent = currentType === 'pie' ? 'Switch to Bar Chart' : 'Switch to Pie Chart';
    btn.onclick = () => {
        currentType = currentType === 'pie' ? 'bar' : 'pie';
        renderDeclineChart(branch_id, mode, declineData, currentType);
        btn.textContent = currentType === 'pie' ? 'Switch to Bar Chart' : 'Switch to Pie Chart';
    };
}
function updateKPI(branch_id, mode, kpi) {
    if (!kpi) return;
    const safeSet = (id, value) => {
        const el = document.getElementById(`${id}${branch_id}-${mode}`);
        if (el) el.textContent = value;
    };

    safeSet("totalServices", kpi.totalServices ?? 0);
    safeSet("topService", kpi.topService ?? "-");
    safeSet("newPatients", kpi.newPatients ?? 0);
    safeSet("avgServices", kpi.avgServices ?? 0);

    const newCountEl = document.getElementById(`newPatientCount${branch_id}-${mode}`);
    if (newCountEl) newCountEl.textContent = "New Patient Count: " + (kpi.newPatients ?? 0);
}

function renderAppointmentsChart(branch_id, mode, appointments) {
    const ctx = document.getElementById(`appointmentsChart${branch_id}-${mode}`);
    if (!ctx || !appointments) return;

    charts[`appointments${branch_id}-${mode}`] = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Booked", "Completed", "Cancelled"],
            datasets: [{
                data: [
                    appointments.booked || 0,
                    appointments.completed || 0,
                    appointments.cancelled || 0
                ],
                backgroundColor: ["#3498db", "#2ecc71", "#e74c3c"],
                borderColor: [
                    'rgba(52, 152, 219, 1)',
                    'rgba(46, 204, 113, 1)',
                    'rgba(231, 76, 60, 1)'
                ],
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: -6,
                    color: '#000',
                    font: {
                        size: 24,         
                        weight: 'bold',
                        family: 'Poppins, sans-serif'
                    },
                    formatter: (value) => value > 0 ? value : ''
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    grace: '20%', 
                    title: {
                        display: true,
                        text: 'Number of Appointments',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        precision: 0,
                        color: '#000000ff',
                        font: { size: 12 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                
                    title: {
                        display: true,
                        text: 'Appointment Status',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        color: '#333',
                        font: { size: 13, weight: 'bold' }
                    },
                    grid: {
                        display: true
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderServicesTrendChart(branch_id, mode, trend) {
    const ctx = document.getElementById(`servicesTrendChart${branch_id}-${mode}`);
    if (!ctx || !trend) return;

    let xLabel = 'Date';
    if (mode === 'weekly') xLabel = 'Days of the Week';
    else if (mode === 'monthly') xLabel = 'Days of the Month';

    charts[`servicesTrend${branch_id}-${mode}`] = new Chart(ctx, {
        type: "line",
        data: {
            labels: trend.labels,
            datasets: [{
                label: "Services",
                data: trend.services,
                borderColor: "#1d445dff",
                borderWidth: 3,
                tension: 0.3,
                fill: false,
                pointBackgroundColor: "#1d445dff",
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: 8,
                    color: '#000',
                    font: {
                        size: 24,
                        weight: 'bold',
                        family: 'Poppins, sans-serif'
                    },
                    formatter: (value) => value > 0 ? value : ''
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: (context) => {
                            return `${context[0].label}`;
                        },
                        label: (context) => {
                            return `Total Services: ${context.parsed.y}`;
                        },
                        afterLabel: (context) => {
                            const dataIndex = context.dataIndex;
                            const servicesBreakdown = trend.servicesBreakdown?.[dataIndex];
                            
                            if (servicesBreakdown && servicesBreakdown.length > 0) {
                                const formattedServices = servicesBreakdown.map(service => {
                                    if (typeof service === 'object' && service.name && service.count) {
                                        return `${service.name} (${service.count})`;
                                    }
                                    return service;
                                }).join('\n');
                                
                                return 'Services:\n' + formattedServices;
                            }
                            return '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '40%',
                    title: {
                        display: true,
                        text: 'Number of Services',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        precision: 0,
                        color: '#333',
                        font: { size: 13 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    offset: true,
                    ticks: {
                        precision: 0,
                        color: '#333',
                        font: { size: 13, weight: 'bold' }
                    },
                    title: {
                        display: true,
                        text: xLabel,
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderIncomeTrendChart(branch_id, mode, trend) {
    const ctx = document.getElementById(`incomeTrendChart${branch_id}-${mode}`);
    if (!ctx || !trend) return;

    let xLabel = 'Date';
    if (mode === 'weekly') xLabel = 'Days of the Week';
    else if (mode === 'monthly') xLabel = 'Days of the Month';

    charts[`incomeTrend${branch_id}-${mode}`] = new Chart(ctx, {
        type: "bar",
        data: {
            labels: trend.labels,
            datasets: [{
                label: "Income (₱)",
                data: trend.income,
                backgroundColor: "#2ecc71",
                borderColor: "rgba(46, 204, 113, 1)",
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: 6,
                    color: '#000',
                    font: {
                        size: 18,
                        weight: 'bold',
                        family: 'Poppins, sans-serif'
                    },
                    formatter: (value) => value > 0 ? Number(value).toLocaleString() : '',
                    display: (ctx) => ctx.dataset.data[ctx.dataIndex] > 0
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '20%',
                    title: {
                        display: true,
                        text: 'Total Revenue (₱)',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        precision: 0,
                        color: '#333',
                        font: { size: 13 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: xLabel,
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        color: '#333',
                        font: { size: 13, weight: 'bold' }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderStaffPerformanceTable(branch_id, mode, staffData) {
    const tbody = document.querySelector(`#staffPerformanceTable${branch_id}-${mode} tbody`);
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!staffData || staffData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No data available</td></tr>`;
        return;
    }

    staffData.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.dentist_name}</td>
            <td>${row.services_rendered}</td>
            <td style="text-align: right;">${Number(row.total_income).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderStaffPerformanceChart(branch_id, mode, staffData) {
    const ctx = document.getElementById(`staffPerformanceChart${branch_id}-${mode}`);
    if (!ctx || !staffData || staffData.length === 0) return;

    const topDentists = staffData.slice(0, 10); 

    const labels = topDentists.map(d => d.dentist_name);
    const incomes = topDentists.map(d => d.total_income);

    charts[`staffPerformance${branch_id}-${mode}`] = new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [{
                label: "Total Income (₱)",
                data: incomes,
                backgroundColor: "#3498db",
                borderColor: "#2980b9",
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    offset: 6,
                    color: '#000',
                    font: {
                        size: 18,
                        weight: 'bold',
                        family: 'Poppins, sans-serif'
                    },
                    formatter: (value) => Number(value).toLocaleString()
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '20%',
                    title: {
                        display: true,
                        text: 'Total Revenue (₱)',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        precision: 0,
                        color: '#333',
                        font: { size: 13 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Dentist',
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: {
                        color: '#333',
                        font: { size: 13, weight: 'bold' }
                    },
                    grid: { display: false }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderServicePricesTable(branch_id, mode, pricesData) {
    const tbody = document.querySelector(`#servicePricesTable${branch_id}-${mode} tbody`);
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!pricesData || pricesData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" style="text-align:center;">No services found</td></tr>`;
        return;
    }

    pricesData.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.service}</td>
            <td>₱${row.price}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderBranchComparisonChart(branch_id, mode, branchComparison) {
    const ctx = document.getElementById(`branchComparisonChart${branch_id}-${mode}`);
    if (!ctx || !branchComparison) return;

    let xLabel = 'Date';
    if (mode === 'weekly') xLabel = 'Days of the Week';
    else if (mode === 'monthly') xLabel = 'Days of the Month';
    
    charts[`branchComparison${branch_id}-${mode}`] = new Chart(ctx, {
        type: "bar",
        data: {
            labels: Object.keys(branchComparison),
            datasets: [{
                label: "Income (₱)",
                data: Object.values(branchComparison),
                backgroundColor: ["#8e44ad", "#2980b9", "#f39c12", "#16a085"],
                borderColor: [
                    "rgba(142, 68, 173, 1)",
                    "rgba(41, 128, 185, 1)",
                    "rgba(243, 156, 18, 1)",
                    "rgba(22, 160, 133, 1)"
                ],
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'end',
                align: 'end',
                offset: 6,
                color: '#000',
                font: {
                    size: 18,
                    weight: 'bold',
                    family: 'Poppins, sans-serif'
                },
                formatter: value => "₱" + Number(value).toLocaleString()
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grace: '20%',
                ticks: {
                    color: '#333',
                    font: { size: 13 }
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                },
                title: {
                    display: true,
                    text: 'Income (₱)',
                    color: '#333',
                    font: { size: 14, weight: 'bold' }
                }
            },
            x: {
                ticks: {
                    color: '#333',
                    font: { size: 13, weight: 'bold' }
                },
                grid: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Branch',
                    color: '#333',
                    font: { size: 14, weight: 'bold' }
                }
            }
        }
    },
        plugins: [ChartDataLabels]
    });
}

function renderGrowthTrendChart(branch_id, mode, growthData) {
    const ctx = document.getElementById(`growthTrendChart${branch_id}-${mode}`);
    if (!ctx || !growthData) return;

    let labels, currValues, prevValues;
    
    let xLabel = 'Date';
    if (mode === 'weekly') xLabel = 'Days of the Week';
    else if (mode === 'monthly') xLabel = 'Days of the Month';

    if (mode === 'weekly') {
        labels = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        const currByDay = {}, prevByDay = {};
        Object.entries(growthData.current).forEach(([date, rev]) => {
            const day = new Date(date).toLocaleDateString('en-US',{ weekday:'long' });
            currByDay[day] = rev;
        });
        Object.entries(growthData.previous).forEach(([date, rev]) => {
            const day = new Date(date).toLocaleDateString('en-US',{ weekday:'long' });
            prevByDay[day] = rev;
        });
        currValues = labels.map(d => currByDay[d] || 0);
        prevValues = labels.map(d => prevByDay[d] || 0);

    } else if (mode === 'monthly') {
        const daysInMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate();
        labels = Array.from({length: daysInMonth}, (_, i) => i + 1);
        const currByDay = {}, prevByDay = {};
        labels.forEach(day => {
            currByDay[day] = 0;
            prevByDay[day] = 0;
        });
        Object.entries(growthData.current).forEach(([date, rev]) => {
            const day = new Date(date).getDate();
            currByDay[day] = rev;
        });
        Object.entries(growthData.previous).forEach(([date, rev]) => {
            const day = new Date(date).getDate();
            prevByDay[day] = rev;
        });
        currValues = labels.map(d => currByDay[d]);
        prevValues = labels.map(d => prevByDay[d]);

    } else {
        labels = Object.keys(growthData.current);
        currValues = Object.values(growthData.current);
        prevValues = labels.map(d => growthData.previous[d] || 0);
    }

    if (ctx.chartInstance) ctx.chartInstance.destroy();
        ctx.chartInstance = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets:[
            {
                label:'Current',
                data:currValues,
                borderColor:'#1d445d',
                backgroundColor:'rgba(41,128,185,0.2)',
                fill:true, tension:0.3
            },
            {
                label:'Previous',
                data:prevValues,
                borderColor:'#fa2912',
                backgroundColor:'rgba(173, 75, 68, 0.2)',
                fill:true, tension:0.3
            }
        ]},
        options:{
            responsive:true,
            maintainAspectRatio:true,
            plugins:{
                legend:{ position:'bottom' },
                datalabels:{
                    anchor:'end',align:'top',
                    font:{ size:20, weight:'bold' },
                    formatter: v => v > 0 ? v.toLocaleString() : '',
                    color:ctx=>ctx.dataset.borderColor
                },
                tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const index = ctx.dataIndex;
                        const datasets = ctx.chart.data.datasets;

                        let result = [];

                    
                        if (datasets[0]) {
                            const currentVal = datasets[0].data[index];
                            result.push(
                                `${datasets[0].label || 'Current'}: ₱${Number(currentVal).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
                            );
                        }

                        
                        if (datasets[1]) {
                            const previousVal = datasets[1].data[index];
                            result.push(
                                `${datasets[1].label || 'Previous'}: ₱${Number(previousVal).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
                            );
                        }

                        return result;
                    }
                }
            }

            },
            scales:{
                x:{
                    offset:true,
                    title: {
                            display: true,
                            text: xLabel,
                            font: { size: 13, weight: 'bold' },
                            color: '#000000ff'
                    },
                },
                y:{
                    ticks: {
                        precision:0,
                        color: '#333',
                        font: { size: 13, weight: 'bold' }
                        },
                    beginAtZero:true,
                    grace:'20%',
                    title: {
                            display: true,
                            text: "Revenue (₱)",
                            font: { size: 13, weight: 'bold' },
                            color: '#000000ff'
                        },
                }
            }
        },
        plugins:[ChartDataLabels]
    });
}

function renderPatientMixChart(branch_id, mode, mix, chartType = 'bar') {
    const ctx = document.getElementById(`patientMixChart${branch_id}-${mode}`);
    const key = `patientMix${branch_id}-${mode}`;
    if (!ctx) return;
    if (charts[key]) {
        charts[key].destroy();
        delete charts[key];
    }
    const labels = ['New Patients', 'Returning Patients'];
    const data = [mix.new || 0, mix.returning || 0];
    const bg = ['#3498db', '#2ecc71'];

    charts[key] = new Chart(ctx, {
        type: chartType,
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: bg,
                borderColor: ['#2980b9', '#27ae60'],
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: '#333',
                        font: { size: 12, weight: 'bold', family: 'Poppins, sans-serif' },
                        padding: 15,
                        generateLabels(chart) {
                            const bgc = chart.data.datasets[0].backgroundColor;
                            return chart.data.labels.map((label, i) => {
                                const val = chart.data.datasets[0].data[i];
                                const total = data.reduce((a, b) => a + b, 0) || 1;
                                const pct = ((val / total) * 100).toFixed(1);
                                return {
                                    text: `${label}: ${val} (${pct}%)`,
                                    fillStyle: bgc[i],
                                    strokeStyle: '#ffffff',
                                    lineWidth: 2,
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                
                datalabels: {
                    anchor: "end",
                    align: "end",
                    offset: -4,
                    color: "#000",
                    font: { size: 20, weight: "bold", family: "Poppins, sans-serif" },
                    formatter: value => value > 0 ? value : ""
                },
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: "Number of Patients",
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    beginAtZero: true,
                    grace: 0.2,
                    ticks: { 
                        precision: 0,
                        color: '#333', 
                        font: { 
                            size: 13 
                        } 
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    title: {
                        display: true,
                        text: "Patient Type",
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    type: 'category',
                    ticks: { 
                        
                        color: '#333', 
                        font: { size: 13, 
                        weight: 'bold' } },
                    grid: { display: false }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function renderPeakHoursChart(branch_id, mode, hoursData) {
    const ctx = document.getElementById(`peakHoursChart${branch_id}-${mode}`);
    if (!ctx || !hoursData) return;


    const dataObj = Array.isArray(hoursData)
        ? Object.fromEntries(hoursData.map(r => [r.hour, r.count]))
        : hoursData;

    const labels = Object.keys(dataObj);
    const values = Object.values(dataObj);

    const chartKey = `peakHours${branch_id}-${mode}`;
    if (charts[chartKey]) {
        charts[chartKey].destroy();
    }

    charts[chartKey] = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Appointments",
                data: values,
                backgroundColor: "#1d445dff",
                borderColor: "#16384b",
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: "end",
                    align: "end",
                    offset: -4,
                    color: "#000",
                    font: { size: 20, weight: "bold", family: "Poppins, sans-serif" },
                    formatter: value => value > 0 ? value : ""
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const hour = ctx.label;
                            const count = ctx.parsed.y;
                            return `${hour}: ${count} appointments`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: "category",
                    title: {
                        display: true,
                        text: "Hour of Day",
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    ticks: { color: "#333", font: { size: 13, weight: "bold" } },
                    grid: { display: false }
                },
                y: {
                    title: {
                        display: true,
                        text: "Number of Appointments",
                        font: { size: 13, weight: 'bold' },
                        color: '#000000ff'
                    },
                    beginAtZero: true,
                    grace: "20%",
                    ticks: { precision: 0, color: "#333", font: { size: 13 } },
                    grid: { color: "rgba(0,0,0,0.05)" }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

async function loadServicesBreakdown(branch_id, mode) {
    try {
        const response = await fetch(`fetch_reports.php?branch_id=${branch_id}&mode=${mode}`);
        const data = await response.json();

        if (data.error) {
            console.error("Error loading services breakdown:", data.details);
            return;
        }

        const breakdownData = data.servicesBreakdown || [];
        renderServicesBreakdownTable(branch_id, mode, breakdownData);
        renderServicesBreakdownChart(branch_id, mode, breakdownData);

    } catch (err) {
        console.error("Fetch failed (Services Breakdown):", err);
    }
}

function renderServicesBreakdownTable(branch_id, mode, breakdownData) {
    const tbody = document.querySelector(`#servicesBreakdownTable${branch_id}-${mode} tbody`);
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!breakdownData || breakdownData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;">No data available</td></tr>`;
        return;
    }

    breakdownData.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.service}</td>
            <td>${row.service_count}</td>
            <td style="text-align:right;">${row.percent_total}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderServicesBreakdownChart(branch_id, mode, breakdownData) {
    const ctx = document.getElementById(`servicesBreakdownChart${branch_id}-${mode}`);
    if (!ctx) return;

    if (ctx.chartInstance) {
        ctx.chartInstance.destroy();
    }

    if (!breakdownData || breakdownData.length === 0) {
        return;
    }

    const labels = breakdownData.map(row => row.service);
    const counts = breakdownData.map(row => row.service_count);
    const percentages = breakdownData.map(row => row.percent_total);

    ctx.chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Service Breakdown',
                data: counts,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'bottom' 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const count = context.parsed || 0;
                            const percentage = percentages[context.dataIndex] || 0;
                            return `${label}: ${count} services (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 25
                    },
                    formatter: (value, context) => {
                        return percentages[context.dataIndex] + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels] 
    });
}

function renderPromosTable(branch_id, mode, promosData) {
    const tableBody = document.querySelector(`#promosTable${branch_id}-${mode} tbody`);
    const totalPromosEl = document.getElementById(`totalPromos${branch_id}-${mode}`);

    if (!tableBody || !totalPromosEl) return;

    tableBody.innerHTML = '';

    if (!promosData || promosData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="3">No promos availed</td></tr>';
        totalPromosEl.textContent = 'Total Promos Availed: 0';
        return;
    }

    let totalCount = 0;
    promosData.forEach(row => {
        totalCount += row.promo_count;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${row.promo_name}</td><td>${row.promo_count}</td><td style="text-align: right;">${row.percent_total}</td>`;
        tableBody.appendChild(tr);
    });

    totalPromosEl.textContent = `Total Promos Availed: ${totalCount}`;
}

function renderPromosChart(branch_id, mode, promosData) {
    const ctx = document.getElementById(`promosChart${branch_id}-${mode}`);
    if (!ctx) return;

    if (ctx.chartInstance) {
        ctx.chartInstance.destroy();
    }

    if (!promosData || promosData.length === 0) {
        return;
    }

    const labels = promosData.map(row => row.promo_name);
    const counts = promosData.map(row => row.promo_count);
    const percentages = promosData.map(row => row.percent_total);

    ctx.chartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Promo Breakdown',
                data: counts,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const count = context.parsed || 0;
                            const percentage = percentages[context.dataIndex] || 0;
                            return `${label}: ${count} availed (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 25
                    },
                    formatter: (value, context) => {
                        return percentages[context.dataIndex] + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

function destroyChart(key) {
    if (charts[key]) {
        charts[key].destroy();
        delete charts[key];
    }
}