@extends('layout.app')
@section ('content')
<div class="max-w-6xl mx-auto p-6 py-12 space-y-12 animate-page-fade">
    <!-- Welcome Section -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-accent/5 rounded-full text-accent mb-4 shadow-inner">
            <i class="ri-rocket-2-fill text-4xl"></i>
        </div>
        <h1 class="text-5xl font-black text-tp">Get Started</h1>
        <p class="text-ts text-[11px] font-bold uppercase opacity-70">Welcome to {{ get_setting('APP_NAME', 'Mini Lara') }} Core</p>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="luxury-card p-8">
            <h2 class="text-[10px] font-bold text-ts uppercase mb-6">User Growth Trends</h2>
            <div class="h-[250px]">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
        <div class="luxury-card p-8 text-center">
            <h2 class="text-[10px] font-bold text-ts uppercase mb-6">Activity Overview</h2>
            <div class="h-[250px]">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Growth Chart
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 2
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Activity Chart
    new Chart(document.getElementById('activityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Idle', 'Inactive'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '75%', 
            plugins: { legend: { position: 'bottom', labels: { padding: 20, font: { size: 11, weight: 'bold' } } } } 
        }
    });
});
</script>
@endsection