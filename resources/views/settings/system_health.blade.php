@extends('layout')
@section('title', 'Welcome to Taskify')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-label-success py-3">
                        <h5 class="text-success fw-bold mb-0">
                            <i class="bx bx-check-circle me-2"></i>Welcome to Taskify
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="bx bx-rocket display-4 text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-3">System Ready!</h4>
                        <p class="text-muted mb-4">
                            Your Taskify project management system is ready to use. No purchase code validation required.
                        </p>
                        <a href="{{ route('home.index') }}" class="btn btn-primary btn-lg">
                            <i class="bx bx-home me-1"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
