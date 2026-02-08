{{-- resources/views/admin/dashboard/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
    .stat-card {
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .system-status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-connected { background-color: #28a745; }
    .status-disconnected { background-color: #dc3545; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary" id="refreshDashboard">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#systemHealthModal">
                <i class="fas fa-heartbeat"></i> System Health
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> {{ $stats['new_users_today'] }} today</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_users']) }}</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="{{ $stats['login_success_rate'] > 95 ? 'text-success' : 'text-warning' }} mr-2">
                                    <i class="fas fa-sign-in-alt"></i> {{ $stats['login_success_rate'] }}% success rate
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Sessions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_sessions']) }}</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-info mr-2">
                                    <i class="fas fa-clock"></i> {{ $stats['system_uptime'] }}
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-network-wired fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Storage Usage</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $systemStatus['storage']['percentage'] }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>{{ $systemStatus['storage']['used'] }} / {{ $systemStatus['storage']['total'] }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Graphs -->
    <div class="row">
        <!-- User Registration Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Registrations (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="userRegistrationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="system-status-indicator status-{{ $systemStatus['database']['status'] }}"></span>
                        <strong>Database:</strong> 
                        {{ ucfirst($systemStatus['database']['status']) }}
                        @if(isset($systemStatus['database']['latency']))
                            <span class="text-muted">({{ $systemStatus['database']['latency'] }}ms)</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <span class="system-status-indicator status-{{ $systemStatus['cache']['status'] }}"></span>
                        <strong>Cache:</strong> {{ ucfirst($systemStatus['cache']['status']) }}
                    </div>
                    <div class="mb-3">
                        <span class="system-status-indicator status-{{ $systemStatus['queue']['status'] }}"></span>
                        <strong>Queue:</strong> {{ ucfirst($systemStatus['queue']['status']) }}
                        @if($systemStatus['queue']['status'] === 'running')
                            <span class="badge badge-{{ $systemStatus['queue']['pending_jobs'] > 0 ? 'warning' : 'success' }}">
                                {{ $systemStatus['queue']['pending_jobs'] }} jobs
                            </span>
                        @endif
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-{{ $systemStatus['storage']['percentage'] > 90 ? 'danger' : ($systemStatus['storage']['percentage'] > 70 ? 'warning' : 'success') }}" 
                             role="progressbar" 
                             style="width: {{ $systemStatus['storage']['percentage'] }}%"
                             aria-valuenow="{{ $systemStatus['storage']['percentage'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $systemStatus['storage']['percentage'] }}%
                        </div>
                    </div>
                    <small class="text-muted">Storage: {{ $systemStatus['storage']['used'] }} of {{ $systemStatus['storage']['total'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent System Activities</h6>
                    <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if($activity->user)
                                            <a href="{{ route('admin.users.details', $activity->user) }}">
                                                {{ $activity->user->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $this->getActivityBadgeColor($activity->action) }}">
                                            {{ str_replace('_', ' ', ucfirst($activity->action)) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $activity->ip_address }}</code></td>
                                    <td>
                                        <small class="text-muted">
                                            @if($activity->details)
                                                {{ substr(json_decode($activity->details, true)['model'] ?? '', 11) }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent activities</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Health Modal -->
<div class="modal fade" id="systemHealthModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Health Check</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Database</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="system-status-indicator status-{{ $systemStatus['database']['status'] }}"></span>
                                        <span class="font-weight-bold">{{ ucfirst($systemStatus['database']['status']) }}</span>
                                    </div>
                                    @if($systemStatus['database']['status'] === 'connected')
                                        <span class="badge badge-success">{{ $systemStatus['database']['latency'] ?? 'N/A' }}ms</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add more system health checks here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // User Registration Chart
    var ctx = document.getElementById('userRegistrationChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($userRegistrations->pluck('date')) !!},
            datasets: [{
                label: 'New Registrations',
                data: {!! json_encode($userRegistrations->pluck('count')) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#4e73df',
                pointRadius: 3,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Refresh dashboard
    $('#refreshDashboard').click(function() {
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            location.reload();
        }, 500);
    });
});

// Helper function for activity badge colors
function getActivityBadgeColor(action) {
    const colors = {
        'created': 'success',
        'updated': 'primary',
        'deleted': 'danger',
        'login': 'info',
        'failed_login': 'warning',
        'password_changed': 'success'
    };
    return colors[action] || 'secondary';
}
</script>
@endsection