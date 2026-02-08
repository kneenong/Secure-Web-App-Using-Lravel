{{-- resources/views/user/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Dashboard')

@section('styles')
<style>
    .profile-completion {
        height: 10px;
        border-radius: 5px;
        overflow: hidden;
    }
    .activity-timeline {
        position: relative;
        padding-left: 30px;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e3e6f0;
    }
    .activity-item {
        position: relative;
        margin-bottom: 20px;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #4e73df;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Welcome back, {{ auth()->user()->name }}!</h1>
        <div class="btn-group">
            <a href="{{ route('user.profile') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
            <a href="{{ route('user.security') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-shield-alt"></i> Security
            </a>
        </div>
    </div>

    <!-- Profile Completion Alert -->
    @if($accountStats['profile_completion'] < 100)
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        Your profile is {{ $accountStats['profile_completion'] }}% complete.
        <a href="{{ route('user.profile') }}" class="alert-link">Complete your profile</a> to get the best experience.
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Account Age</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $accountStats['account_age'] }}</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Member since {{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Profile Completion</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $accountStats['profile_completion'] }}%</div>
                            <div class="mt-2">
                                <div class="profile-completion bg-light">
                                    <div class="bg-success" style="height: 100%; width: {{ $accountStats['profile_completion'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Logins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $accountStats['total_logins'] }}</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Last: {{ $accountStats['last_login'] }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Account Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Role: {{ ucfirst($user->role) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    <a href="{{ route('user.activity') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @forelse($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ str_replace('_', ' ', ucfirst($activity->action)) }}</strong>
                                    <p class="mb-1 text-muted">{{ $activity->created_at->format('M d, Y h:i A') }}</p>
                                    @if($activity->details)
                                        <small>{{ json_decode($activity->details, true)['model'] ?? '' }}</small>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    <div><code class="small">{{ $activity->ip_address }}</code></div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent activities</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Login History -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('user.profile') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-edit mr-2"></i>Edit Profile</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('user.security') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-lock mr-2"></i>Change Password</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('user.settings') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-cog mr-2"></i>Settings</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('user.data.download') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-download mr-2"></i>Download Data</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Logins -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Logins</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($loginHistory as $login)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $login->created_at->format('M d') }}</strong>
                                    <div class="small text-muted">{{ $login->created_at->format('h:i A') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="small">
                                        @if($login->action === 'failed_login')
                                            <span class="text-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                        @else
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Success</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">{{ $login->ip_address }}</div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">No login history</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection