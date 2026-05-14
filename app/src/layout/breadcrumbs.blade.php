@php
$user = $_SESSION['user'] ?? null;
$showBreadcrumbs = config('APP_SHOW_BREADCRUMBS', '1') === '1';

if (!$showBreadcrumbs) return;

$currentRoute = strtok($_SERVER['REQUEST_URI'], '?');
$cleanRoute = '/' . trim(str_replace($_ENV['ROOT_PATH'] ?? '', '', $currentRoute), '/');
$parts = array_filter(explode('/', $cleanRoute));
$path = '';
$breadcrumb_map = [
    '/user'     => '/users',
];
$disabled_segments = ['view', 'edit', 'add', 'manage'];
@endphp

<div class="flex items-center gap-1.5 text-ts mb-4 no-print">
    <a href="{{ url('/home') }}" class="flex items-center justify-center hover:text-accent transition-colors">
        <i class="ri-home-4-line text-xs"></i>
    </a>
    @foreach ($parts as $part)
        @php
            $path .= '/' . $part;
            $finalUrl = $breadcrumb_map[$path] ?? $path;
            $isDisabled = in_array($part, $disabled_segments);
        @endphp
        <i class="ri-arrow-right-s-line text-[9px] opacity-30"></i>
        @if ($isDisabled)
            <span class="text-[10px] font-bold uppercase text-ts/40">
                {{ ($loop->last && isset($breadcrumb_title)) ? $breadcrumb_title : str_replace(['-', '_'], ' ', $part) }}
            </span>
        @else
            <a
                href="{{ url($finalUrl) }}"
                class="text-[10px] font-bold uppercase transition-colors {{ $loop->last ? 'text-accent' : 'text-ts/60 hover:text-tp' }}"
            >
                {{ ($loop->last && isset($breadcrumb_title)) ? $breadcrumb_title : str_replace(['-', '_'], ' ', $part) }}
            </a>
        @endif
    @endforeach
</div>
