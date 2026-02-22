// Fetch report data and create chart
fetch("../api/reports.php", {
    credentials: "include"
})
.then(res => res.json())
.then(data => {
    if (!data || data.length === 0) {
        document.getElementById("calorieChart").parentElement.innerHTML = 
            '<div class="card"><p style="text-align: center; color: #999;">No data available yet. Start logging your meals!</p></div>';
        return;
    }

    const labels = data.map(item => formatDate(item.entry_date));
    const calories = data.map(item => item.total_calories || 0);

    // Calculate average for target line
    const average = calories.length > 0 
        ? Math.round(calories.reduce((a, b) => a + b, 0) / calories.length)
        : 0;

    new Chart(document.getElementById("calorieChart"), {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Daily Calories",
                    data: calories,
                    borderColor: "#667eea",
                    backgroundColor: "rgba(102, 126, 234, 0.1)",
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 6,
                    pointBackgroundColor: "#667eea",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    tension: 0.4,
                    pointHoverRadius: 8
                },
                {
                    label: "Average Intake",
                    data: Array(labels.length).fill(average),
                    borderColor: "#ff9f43",
                    borderDash: [5, 5],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        font: { size: 14, weight: "bold" },
                        color: "#333",
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    padding: 12,
                    titleFont: { size: 14, weight: "bold" },
                    bodyFont: { size: 13 },
                    borderColor: "#667eea",
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ": " + context.parsed.y + " kcal";
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 12 },
                        color: "#666",
                        callback: function(value) {
                            return value + " kcal";
                        }
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.05)",
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: { size: 12 },
                        color: "#666"
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });
})
.catch(error => {
    console.error("Error loading chart data:", error);
    document.getElementById("calorieChart").parentElement.innerHTML = 
        '<div class="card"><p style="text-align: center; color: #ff6b6b;">Error loading data. Please refresh the page.</p></div>';
});

function formatDate(dateString) {
    const options = { weekday: 'short', month: 'short', day: 'numeric' };
    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', options);
}