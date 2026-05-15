@extends('layout.app')
@section ('content')
<div class="max-w-7xl mx-auto p-4 py-4 space-y-6 animate-page-fade">
    <!-- Compact Welcome Section -->
    <div class="flex items-center gap-4 border-b border-ts/5 pb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-accent/5 rounded-full text-accent flex items-center justify-center shadow-inner">
            <i class="ri-rocket-2-fill text-2xl"></i>
        </div>
        <div>
            <h1 class="text-2xl font-black text-tp leading-none">Dashboard</h1>
            <p class="text-ts text-[10px] font-bold uppercase opacity-70 mt-1">System Overview & Quick Access</p>
        </div>
    </div>

    <!-- Compact Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="luxury-card p-5">
            <h2 class="text-[13px] font-bold text-ts uppercase mb-4">User Growth</h2>
            <div class="h-[360px]">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
        <div class="luxury-card p-5">
            <h2 class="text-[13px] font-bold text-ts uppercase mb-4">Activity</h2>
            <div class="h-[360px]">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Compact Print Demos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ url('/print/reports/dummy') }}" target="_blank" class="luxury-card p-4 flex items-center gap-4 hover:border-accent hover:bg-accent/5 transition-all group border-ts/10">
            <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center text-accent group-hover:scale-110 transition-transform">
                <i class="ri-file-list-3-line text-xl"></i>
            </div>
            <div>
                <h3 class="text-[13px] font-bold text-tp">Business Report</h3>
                <p class="text-[9px] text-ts uppercase font-semibold">Summary View</p>
            </div>
        </a>
        <a href="{{ url('/print/single/dummy') }}" target="_blank" class="luxury-card p-4 flex items-center gap-4 hover:border-emerald-500/40 hover:bg-emerald-500/5 transition-all group border-ts/10">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                <i class="ri-file-paper-line text-xl"></i>
            </div>
            <div>
                <h3 class="text-[13px] font-bold text-tp">Single Invoice</h3>
                <p class="text-[9px] text-ts uppercase font-semibold">Receipt View</p>
            </div>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9 } } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });

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
            plugins: { legend: { position: 'right', labels: { padding: 10, font: { size: 9, weight: 'bold' } } } } 
        }
    });
});
</script>
@endsection