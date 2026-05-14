@extends('layout.app')
@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center p-4">
    <div class="text-center space-y-8 max-w-lg">
        <!-- Visual Icon -->
        <div class="relative inline-block">
            <div class="w-32 h-32 bg-accent/5 rounded-full flex items-center justify-center border border-accent/10 animate-pulse">
                <i class="ri-compass-3-line text-7xl text-accent/20"></i>
            </div>
            <div class="absolute -bottom-2 -right-2 w-12 h-10 bg-bs border border-ts/10 rounded-xl flex items-center justify-center text-accent shadow-xl">
                <i class="ri-question-line text-2xl"></i>
            </div>
        </div>

        <div class="space-y-3">
            <h1 class="text-8xl font-black text-tp opacity-10">404</h1>
            <h2 class="text-base font-semibold text-tp uppercase">Lost in the Portfolio?</h2>
            <p class="text-ts font-medium text-sm max-w-sm mx-auto leading-relaxed opacity-70">
                The property or page you are looking for has been moved, renamed, or simply doesn't exist in our inventory.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8">
            <a href="{{ url('/') }}" class="luxury-button luxury-button-primary px-10 py-4">
                <i class="ri-home-4-line mr-2"></i> Back to Dashboard
            </a>
            <button onclick="window.history.back()" class="luxury-button border border-ts/10 text-ts hover:bg-bs px-10 py-4">
                <i class="ri-arrow-left-line mr-2"></i> Go Back
            </button>
        </div>

        <div class="pt-16">
            <p class="text-[10px] font-semibold text-ts uppercase opacity-40">Mini Lara Business Management</p>
        </div>
    </div>
</div>
@endsection
