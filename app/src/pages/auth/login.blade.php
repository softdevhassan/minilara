@extends ('layout.app')
@section ('content')
@php
use App\Auth;
use App\View;
if (Auth::isLoggedIn()) {
redirect('/home');
}
$prefill_username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['LoginTrigger'])) {
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$result = Auth::login($username, $password, $remember);
if ($result['success']) {
View::alert('success', 'Welcome, @' . e($result['user']['username']));
redirect('/home');
} else {
View::alert('error', $result['message']);
$prefill_username = $username;
}
}
@endphp
<div class="min-h-screen flex items-center justify-center bg-bs p-4">
    <div class="w-full max-w-[340px] sm:max-w-[420px]">
        <div class="luxury-card p-4 xs:p-8 sm:p-10 ring-1 ring-white/5">
            <!-- Logo -->
            <div class="text-center mb-5">
                <span
                    class="text-xl font-semibold text-accent uppercase">{{ e(get_setting('APP_NAME', 'MINI LARA')) }}</span>
            </div>
            <!-- Login Form -->
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-[10px] font-semibold text-ts uppercase mb-2 px-1">Username</label>
                    <div class="relative">
                        <i class="ri-user-line absolute left-4 top-1/2 -translate-y-1/2 text-ts"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            placeholder="Enter username"
                            value="{{ e($prefill_username) }}"
                            class="luxury-input w-full pl-12 font-semibold text-sm" />
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-[10px] font-semibold text-ts uppercase mb-2 px-1">Password</label>
                    <div class="relative">
                        <i class="ri-lock-line absolute left-4 top-1/2 -translate-y-1/2 text-ts"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="luxury-input w-full pl-12 font-semibold text-sm" />
                    </div>
                </div>
                <div class="flex items-center px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded border-white/10 bg-bp text-accent focus:ring-accent/40" />
                        <span class="text-[11px] font-semibold text-ts uppercase">Remember me</span>
                    </label>
                </div>
                <button
                    type="submit"
                    name="LoginTrigger"
                    class="luxury-button luxury-button-primary w-full py-4 text-sm font-semibold">
                    Login
                </button>
            </form>
        </div>
        <div class="mt-8 text-center text-ts text-[9px] font-semibold uppercase">
            &copy; {{ date('Y') }} {{ e(get_setting('APP_NAME', 'Mini Lara')) }} | Developed by
            <a href="{{ get_setting('DEVELOPER_URL', 'https://linktr.ee/softdevhassan') }}" target="_blank" class="hover:text-accent transition-colors">
                {{ e(get_setting('DEVELOPER_NAME', 'Hassan Ali')) }}
            </a>
        </div>
    </div>
</div>
@endsection