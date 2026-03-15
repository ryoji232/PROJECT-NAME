/**
 * dashboard.js — Dashboard real-time updates & chart
 */

let borrowedBooksChart = null;

document.addEventListener('DOMContentLoaded', function () {
    // Seed chart immediately with server-side data if available
    initChart(window.__initialChartData || null);
    // Then start polling for live updates
    updateDashboard();
    setInterval(updateDashboard, 30000);
});

function updateDashboard() {
    fetch('/borrowings/realtime-data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        updateStats(data);
        updateRecentList(data.recent_borrowings);
        updateChart(data.chart_data);
    })
    .catch(err => console.error('Dashboard update error:', err));
}

function updateStats(data) {
    const badges = document.querySelectorAll('.stats-badge');
    if (badges.length >= 3) {
        badges[0].textContent = data.total_books;
        badges[1].textContent = data.current_borrowings;
        badges[2].textContent = data.overdue_count || 0;
    }
}

function updateRecentList(borrowings) {
    const list = document.querySelector('#recentBorrowingsList');
    if (!list) return;

    if (!borrowings?.length) {
        list.innerHTML = `<li class="list-group-item text-center text-muted py-3">
            <h5>No recent borrowings</h5><p class="mb-0">Books borrowed will appear here</p>
        </li>`;
        return;
    }

    list.innerHTML = borrowings.map(b => {
        const date = new Date(b.borrowed_at).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
        return `<li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>${b.student_name}</strong> borrowed <em>"${b.book_title}"</em></div>
                <small class="text-muted">${date}</small>
            </div>
        </li>`;
    }).join('');
}

function initChart(seedData) {
    const ctx = document.getElementById('borrowedBooksChart');
    if (!ctx) return;

    const colors = ['#198754','#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0'];

    const labels = seedData?.labels?.length ? seedData.labels : ['No data'];
    const data   = seedData?.data?.length   ? seedData.data   : [0];
    const bgColors = seedData?.labels?.length ? colors : ['#6c757d'];

    borrowedBooksChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Times Borrowed',
                data,
                backgroundColor: bgColors,
                borderColor: bgColors.map(c => c),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: {
                    ticks: {
                        callback: function(v, i) {
                            const l = borrowedBooksChart.data.labels[i];
                            return l?.length > 15 ? l.slice(0, 15) + '…' : l;
                        }
                    }
                }
            }
        }
    });
}

function updateChart(chartData) {
    if (!borrowedBooksChart) return;

    const colors = ['#198754','#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0'];

    if (chartData?.labels?.length) {
        borrowedBooksChart.data.labels = chartData.labels;
        borrowedBooksChart.data.datasets[0].data = chartData.data;
        borrowedBooksChart.data.datasets[0].backgroundColor = colors;
    } else {
        borrowedBooksChart.data.labels = ['No borrowing data'];
        borrowedBooksChart.data.datasets[0].data = [0];
        borrowedBooksChart.data.datasets[0].backgroundColor = ['#6c757d'];
    }

    borrowedBooksChart.update();
}