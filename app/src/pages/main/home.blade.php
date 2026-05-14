@extends('layout.app')
@section ('content')
@php
/**
* Note: You can define a page title by setting $title before @extends
* Example: $title = 'Page Name | ' . get_setting('APP_NAME');
*/
use App\DB;

$total_users = DB::table('users')->count();

$recent_users = DB::table('users')
->select([
"id",
"name",
"username",
"email"
])
->orderBy('id', 'DESC')
->limit(5)
->get()
->map(function($x){ return (array)$x; })
->all();
@endphp
<div class="max-w-full py-4 px-4 sm:px-5">
    @include('layout.breadcrumbs')
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-5">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-10 xs:w-16 xs:h-16 bg-accent rounded-xl xs:rounded-2xl flex items-center justify-center text-bp border border-white/10">
                <i class="ri-dashboard-3-line text-2xl xs:text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl xs:text-lg font-semibold text-tp">Dashboard</h1>
                <p class="text-[9px] xs:text-[10px] font-semibold uppercase text-ts opacity-70">{{ get_setting('APP_NAME', 'Mini Lara') }} Overview</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="luxury-card p-3 sm:p-4 group">
            <div class="flex items-center gap-5">
                <div
                    class="flex-shrink-0 w-11 h-11 bg-blue-500/5 border border-blue-500/20 rounded-2xl flex items-center justify-center text-blue-600 transition-colors duration-300">
                    <i class="ri-user-settings-line text-2xl"></i>
                </div>
                <div>
                    <div class="text-lg font-semibold text-tp mb-0.5 leading-none">
                        {{ $total_users }}
                    </div>
                    <div class="text-[11px] font-semibold text-ts uppercase">System Users</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="luxury-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold uppercase text-ts tracking-wider">User Growth Analytics</h2>
                <i class="ri-bar-chart-fill text-accent text-xl"></i>
            </div>
            <div class="h-[250px] w-full">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
        <div class="luxury-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold uppercase text-ts tracking-wider">Activity Distribution</h2>
                <i class="ri-pie-chart-fill text-accent text-xl"></i>
            </div>
            <div class="h-[250px] w-full flex justify-center">
                <canvas id="activityPieChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Growth Chart
            new Chart(document.getElementById('userGrowthChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [12, 19, 15, 25, 22, 30],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Pie Chart
            new Chart(document.getElementById('activityPieChart'), {
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
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Users -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-tp">Recent Users</h2>
                <a href="{{ url('/users') }}" class="text-xs font-semibold text-accent hover:underline uppercase">View All Users</a>
            </div>
            <div class="luxury-card overflow-hidden">
                <table class="w-full text-left">
                    <thead
                        class="bg-bs border-b border-ts/10 text-[10px] sm:text-[11px] font-semibold text-ts uppercase">
                        <tr>
                            <th class="px-3 sm:px-4 py-3">Name</th>
                            <th class="px-3 sm:px-4 py-3">Username</th>
                            <th class="px-3 sm:px-4 py-3 text-right">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ts/10">
                        @foreach ($recent_users as $ru)
                        <tr class="hover:bg-bs/50 transition-colors cursor-pointer">
                            <td class="px-3 sm:px-4 py-3">
                                <a href="{{ url('/edit/user/') }}/{{ $ru['id'] }}"
                                    class="text-[12px] sm:text-[13px] font-bold font-semibold uppercase mb-0.5 text-tp">
                                    {{ $ru['name'] }}
                                </a>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-ts text-xs font-medium uppercase">
                                {{ $ru['username'] }}
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-right font-semibold text-accent">
                                <div class="text-xs sm:text-sm">
                                    {{ $ru['email'] }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if (empty($recent_users))
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-ts font-medium">
                                No recent users found.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-tp leading-none">Quick Actions</h2>
            <div class="grid grid-cols-1 gap-4">
                <a href="{{ url('/add/user') }}" class="luxury-card p-4 flex items-center gap-5 group transition-colors">
                    <div
                        class="flex-shrink-0 w-12 h-10 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-600 flex items-center justify-center transition-colors duration-300 group-hover:bg-emerald-500 group-hover:text-white group-hover:border-transparent">
                        <i class="ri-user-add-line text-2xl"></i>
                    </div>
                    <div>
                        <div
                            class="font-semibold text-tp text-sm mb-0.5 group-hover:text-emerald-500 transition-colors">
                            New User
                        </div>
                        <div class="text-[10px] text-ts font-semibold uppercase opacity-70">
                            Create access
                        </div>
                    </div>
                </a>
                <a href="{{ url('/settings') }}" class="luxury-card p-4 flex items-center gap-5 group transition-colors">
                    <div
                        class="flex-shrink-0 w-12 h-10 bg-gray-500/10 border border-gray-500/20 rounded-2xl text-gray-600 flex items-center justify-center transition-colors duration-300 group-hover:bg-gray-500 group-hover:text-white group-hover:border-transparent">
                        <i class="ri-settings-4-line text-2xl"></i>
                    </div>
                    <div>
                        <div
                            class="font-semibold text-tp text-sm mb-0.5 group-hover:text-gray-500 transition-colors">
                            Settings
                        </div>
                        <div class="text-[10px] text-ts font-semibold uppercase opacity-70">
                            Global configuration
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection