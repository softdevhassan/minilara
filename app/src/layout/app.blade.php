@php
$user = $_SESSION['user'] ?? null;
$settings_raw = App\DB::table('settings')->get()->toArray();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s->key] = $s->value1;
}
$currentRoute = strtok($_SERVER['REQUEST_URI'], '?');
$cleanRoute = '/' . trim(str_replace($_ENV['ROOT_PATH'] ?? '', '', $currentRoute), '/');
$userColors = null;
if ($user) {
    // Refresh user data from DB to ensure name/colors are latest
    $freshUser = App\DB::getInstance()->get('users', '*', ['id' => $user['id']]);
    if ($freshUser) {
        $user = $freshUser;
        $_SESSION['user'] = $freshUser; // Sync back to session
    }
    $userColors = json_decode($user['color_scheme'] ?? '{}', true)['colors'] ?? null;
}

if (!$userColors) {
    // If no user colors (or not logged in), fetch Admin (ID 1) colors for branding
    $admin = App\DB::getInstance()->get('users', '*', ['id' => 1]);
    if ($admin) {
        $userColors = json_decode($admin['color_scheme'] ?? '{}', true)['colors'] ?? null;
    }
}

// Global System Fallback (Modern Slate Gray Dark)
if (!$userColors) {
    $userColors = [ 
        'tp' => '#0f172a', 'ts' => '#4b5563', 'bp' => '#ffffff', 'bs' => '#f9fafb', 
        'sb' => '#2563eb', 'sl' => '#eff6ff', 'sh' => '#ffffff', 
        'sa' => '#ffffff', 'st' => '#2563eb', 
        'accent' => '#2563eb', 'accent_light' => '#60a5fa', 'accent_dark' => '#1d4ed8'
    ];
}
$iconText = $settings['APP_ICON_TEXT'] ?? 'PA';
$svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="20" fill="' . $userColors['bp'] . '" /><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="' . $userColors['accent'] . '" font-family="Rubik, sans-serif" font-weight="900" font-size="50">' . $iconText . '</text></svg>';
$useDynamic = ($settings['APP_USE_DYNAMIC_FAVICON'] ?? '0') === '1';
$faviconData = $useDynamic ? 'data:image/svg+xml;base64,' . base64_encode(trim($svgIcon)) : asset($settings['APP_FAVICON'] ?? ''); $siteName = $settings['APP_NAME'] ?? 'Mini Lara';
$separator = ' | ';
$pageTitle = $title ?? ucwords(trim(str_replace(['/', '-', '_'], [' ', ' ', ' '], $cleanRoute)));
$isSpecialPage = strpos($cleanRoute, '/auth/') === 0 || in_array($cleanRoute, ['/404', '/500']);
$showLayout = $showLayout ?? (!$isSpecialPage);
$fullTitle = ($cleanRoute === '/' || $cleanRoute === '/home') ? $siteName : $pageTitle . $separator . $siteName; 
$navbarPath = BASE_PATH . '/app/src/layout/navbar.json';
$menu = file_exists($navbarPath) ? json_decode(file_get_contents($navbarPath), true) : [];
$isActive = function($url) use ($cleanRoute) {
    if ($url === '/home' && $cleanRoute === '/') return true;
    if ($url === '/' && $cleanRoute !== '/') return false;
    return strpos($cleanRoute, $url) !== false;
};
$isMenuOpen = function($item) use ($isActive) { 
    if (isset($item['url']) && $isActive($item['url'])) return true; 
    if (isset($item['children'])) { 
        foreach ($item['children'] as $child) { 
            if ($isActive($child['url'])) return true; 
        } 
    } 
    return false;
};
$flatMenu = [];
foreach($menu as $item) { if(isset($item['children'])) { foreach($item['children'] as $child) { $flatMenu[] = ['title' => $child['title'], 'url' => url($child['url']), 'parent' => $item['title']]; } } else { $flatMenu[] = ['title' => $item['title'], 'url' => url($item['url']), 'parent' => null]; }
}
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ $settings['APP_TAGLINE'] ?? 'Professional Business Management System' }}" />
    <meta name="keywords" content="ERP, CRM, Business Management, PHP, Tailwind, AlpineJS" />
    <meta name="author" content="{{ get_setting('DEVELOPER_NAME', 'Hassan Ali') }}" />
    <meta name="view-transition" content="same-origin" />
    <title>{{ $fullTitle }}</title>
    <link rel="icon" href="{{ $faviconData }}" />
    @vite (['app/src/assets/css/app.css', 'app/src/assets/js/app.js'])
    <script>
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : null;
        }
        const colors = {
            tp: '{{ $userColors["tp"] }}',
            ts: '{{ $userColors["ts"] }}',
            bp: '{{ $userColors["bp"] }}',
            bs: '{{ $userColors["bs"] }}',
            sb: '{{ $userColors["sb"] ?? $userColors["bs"] }}',
            sl: '{{ $userColors["sl"] ?? $userColors["ts"] }}',
            sh: '{{ $userColors["sh"] ?? $userColors["tp"] }}',
            sa: '{{ $userColors["sa"] ?? "rgba(var(--accent-rgb), 0.1)" }}',
            st: '{{ $userColors["st"] ?? $userColors["accent"] }}',
            accent: '{{ $userColors["accent"] }}',
            accent_dark: '{{ $userColors["accent_dark"] }}',
            'accent-light': '{{ $userColors["accent_light"] }}',
        };
        const root = document.documentElement;
        Object.keys(colors).forEach((key) => {
            root.style.setProperty(`--${key}`, colors[key]);
            root.style.setProperty(`--${key}-rgb`, hexToRgb(colors[key]));
        });
    </script>
    <style>
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
            }
            .bg-black {
                background-color: #000000 !important;
                color: #ffffff !important;
            }
            .text-white {
                color: #ffffff !important;
            }
        }
    </style>
    @stack('head')
</head>
@php $isPrint = strpos($cleanRoute, '/print/') === 0; $bodyClass = $isPrint ? 'bg-white text-slate-900' : 'bg-bp text-tp';
@endphp
<body
    class="{{ $bodyClass }} antialiased font-rubik selection:bg-accent/30 {{ $isPrint ? 'overflow-visible' : 'h-full overflow-x-hidden no-scrollbar' }}"
    x-data='{ 
        mobileOpen: false, 
        searchOpen: false, 
        searchQuery: "", 
        searchIndex: -1, 
        searchItems: {!! json_encode($flatMenu) !!}, 
        dynamicResults: [], 
        isSearching: false, 
        get filteredItems() { 
            if (this.searchQuery === "") return []; 
            const local = this.searchItems.filter(item => 
                item.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                (item.parent && item.parent.toLowerCase().includes(this.searchQuery.toLowerCase())) 
            ).slice(0, 5); 
            return [...local, ...this.dynamicResults]; 
        }, 
        toggleSearch() { 
            this.searchOpen = !this.searchOpen; 
            if (this.searchOpen) { 
                this.searchIndex = -1;
                this.$nextTick(() => { this.$refs.searchInput.focus(); }); 
            } 
        }, 
        handleKeydown(e) { 
            if ((e.ctrlKey || e.metaKey) && e.key === "k") { 
                e.preventDefault(); 
                this.toggleSearch(); 
            } 
            if (e.key === "Escape") this.searchOpen = false; 

            if (this.searchOpen && this.filteredItems.length > 0) {
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    this.searchIndex = (this.searchIndex + 1) % this.filteredItems.length;
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    this.searchIndex = (this.searchIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
                } else if (e.key === "Enter" && this.searchIndex >= 0) {
                    e.preventDefault();
                    window.location.href = this.filteredItems[this.searchIndex].url;
                }
            }
        },
        init() {
            this.$watch("searchQuery", (value) => {
                this.searchIndex = -1;
                if (value.length < 2) {
                    this.dynamicResults = [];
                    return;
                }
                this.isSearching = true;
                fetch("{{ url("/api/search") }}?q=" + encodeURIComponent(value))
                    .then(res => res.json())
                    .then(data => {
                        this.dynamicResults = data;
                        this.isSearching = false;
                    })
                    .catch(() => { this.isSearching = false; });
            });
        }
    }'
    @keydown.window="handleKeydown($event)"
>
    <!-- SEARCH MODAL -->
    <div
        x-show="searchOpen"
        x-cloak
        class="fixed inset-0 z-[100] overflow-y-auto p-4 sm:p-6 md:p-20"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="searchOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60"
            @click="searchOpen = false"
        ></div>
        <div
            x-show="searchOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-[101] mx-auto max-w-2xl transform divide-y divide-ts/10 overflow-hidden rounded-2xl bg-bs ring-1 ring-ts/10 transition-colors"
        >
            <div class="relative">
                <i class="ri-search-2-line absolute left-4 top-3.5 text-ts text-xl" x-show="!isSearching"></i>
                <i
                    class="ri-loader-4-line absolute left-4 top-3.5 text-accent text-xl animate-spin"
                    x-show="isSearching"
                ></i>
                <input
                    x-ref="searchInput"
                    type="text"
                    x-model="searchQuery"
                    class="h-12 w-full border-0 bg-transparent pl-12 pr-4 text-tp placeholder:text-ts focus:ring-0 sm:text-sm font-medium"
                    placeholder="Search routes, properties, contacts or features..."
                />
            </div>
            <ul
                x-show="filteredItems.length > 0"
                class="max-h-96 scroll-py-3 overflow-y-auto p-3 no-scrollbar divide-y divide-ts/5"
            >
                <template x-for="(item, index) in filteredItems" :key="index">
                    <li class="group">
                        <div
                            :class="searchIndex === index
                                ? 'bg-accent/10 border-accent/30'
                                : 'hover:bg-accent/5 border-transparent'"
                            class="flex items-center rounded-xl p-2.5 transition-colors border"
                        >
                            <a :href="item.url" class="flex-auto flex items-center min-w-0">
                                <div
                                    class="flex h-10 w-10 flex-none items-center justify-center rounded-lg border border-ts/10 bg-bs group-hover:border-accent/40"
                                >
                                    <i class="ri-arrow-right-line text-ts group-hover:text-accent"></i>
                                </div>
                                <div class="ml-4 flex-auto min-w-0">
                                    <p class="text-sm font-semibold text-tp truncate" x-text="item.title"></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <p
                                            class="text-[9px] font-bold text-accent uppercase"
                                            x-text="item.parent || 'System'"
                                        ></p>
                                        <template x-if="item.subtitle">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1 h-1 bg-ts/20 rounded-full"></span>
                                                <p
                                                    class="text-[10px] font-bold text-tp truncate"
                                                    x-text="item.subtitle"
                                                ></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </a>
                            <div class="flex items-center gap-1.5 ml-4 no-print">
                                <template x-if="item.edit_url">
                                    <a
                                        :href="item.edit_url"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-ts/10 text-ts hover:border-accent hover:text-accent hover:bg-accent/5 transition-colors"
                                        title="Edit"
                                    >
                                        <i class="ri-edit-line text-sm"></i>
                                    </a>
                                </template>
                                <template x-if="item.print_url">
                                    <a
                                        :href="item.print_url"
                                        target="_blank"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-ts/10 text-ts hover:border-accent hover:text-accent hover:bg-accent/5 transition-colors"
                                        title="Print"
                                    >
                                        <i class="ri-printer-line text-sm"></i>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
            <div
                x-show="searchQuery !== '' && filteredItems.length === 0 && !isSearching"
                class="py-14 text-center sm:px-14 bg-bs/20"
            >
                <p class="text-tp font-semibold text-lg opacity-100">No results found.</p>
                <p class="text-[10px] font-semibold text-ts uppercase mt-2">Try searching for something else</p>
            </div>
        </div>
    </div>
    <div class="{{ $isPrint ? '' : 'h-full flex overflow-hidden w-full' }}">
        @if ($showLayout && !$isPrint)
            <!-- SIDEBAR -->
            <!-- Mobile backdrop overlay -->
            <div
                x-show="mobileOpen"
                @click="mobileOpen = false"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-black/60 md:hidden"
                x-cloak
            ></div>
            <aside
                :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-50 w-[240px] max-w-[85vw] bg-[var(--sb)] text-tp transition-transform md:translate-x-0 flex flex-col border-r border-white/5"
            >
                <!-- Logo -->
                <div class="h-16 flex items-center justify-between px-3 border-b border-white/5 relative bg-[var(--sb)] flex-shrink-0">
                    <a href="{{ url('/home') }}" class="flex items-center flex-1 justify-center">
                        @if (!empty($settings['APP_LOGO']))
                            <img src="{{ asset($settings['APP_LOGO']) }}" class="max-h-16 w-auto object-contain" />
                        @else
                            <span class="text-base font-bold text-white">{{ $settings['APP_NAME'] ?? 'Mini Lara' }}</span>
                        @endif
                    </a>
                    <!-- Close button - mobile only -->
                    <button
                        @click="mobileOpen = false"
                        class="md:hidden flex-shrink-0 w-7 h-7 rounded-lg bg-white/10 hover:bg-red-500/80 text-white flex items-center justify-center transition-colors"
                    >
                        <i class="ri-close-line text-sm"></i>
                    </button>
                </div>
                <!-- Nav -->
                <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5 no-scrollbar">
                    @foreach ($menu as $item)
                        @php
                            $showItem = false;
                            if (isset($item['children'])) {
                                foreach ($item['children'] as $child) {
                                    if (App\Auth::hasAccess($child['permission'] ?? '')) {
                                        $showItem = true;
                                        break;
                                    }
                                }
                            } else {
                                $showItem = App\Auth::hasAccess($item['permission'] ?? '');
                            }
                        @endphp
                        @if ($showItem)
                            @if (isset($item['children']))
                                <div x-data="{ open: {{ $isMenuOpen($item) ? 'true' : 'false' }} }">
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-colors duration-200 group text-left"
                                        :class="open
                                            ? 'bg-[var(--sa)] text-[var(--st)]'
                                            : 'text-[var(--sl)] hover:bg-white/10 hover:text-[var(--sh)]'"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <i
                                                class="{{ $item['icon'] }} text-base transition-colors"
                                                :class="open
                                                    ? 'text-[var(--st)]'
                                                    : 'text-[var(--sl)] group-hover:text-[var(--sh)]'"
                                            ></i>
                                            <span class="font-medium text-[13px]">{{ $item['title'] }}</span>
                                        </div>
                                        <i
                                            class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                                            :class="open ? 'rotate-180 text-[var(--st)]' : 'text-[var(--sl)]'"
                                        ></i>
                                    </button>
                                    <div x-show="open" x-collapse class="mt-0.5 space-y-0.5 py-0.5">
                                        @foreach ($item['children'] as $child)
                                            @if (App\Auth::hasAccess($child['permission'] ?? ''))
                                                <a
                                                    href="{{ url($child['url']) }}"
                                                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-md text-[12px] font-medium transition-colors {{ ($isActive($child['url']) ? 'text-[var(--st)] bg-[var(--sa)]' : 'text-[var(--sl)] hover:text-[var(--sh)] hover:bg-white/5') }}"
                                                >
                                                    <i class="{{ $child['icon'] }} text-sm opacity-80"></i>
                                                    <span>{{ $child['title'] }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a
                                    href="{{ url($item['url']) }}"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors duration-200 group {{ ($isActive($item['url']) ? 'bg-[var(--sa)] text-[var(--st)]' : 'text-[var(--sl)] hover:bg-white/10 hover:text-[var(--sh)]') }}"
                                >
                                    <i
                                        class="{{ $item['icon'] }} text-base transition-colors {{ ($isActive($item['url']) ? 'text-[var(--st)]' : 'text-[var(--sl)] group-hover:text-[var(--sh)]') }}"
                                    ></i>
                                    <span class="font-medium text-[13px]">{{ $item['title'] }}</span>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
                <!-- Footer -->
                <div class="flex-shrink-0 px-3 py-3 border-t border-white/5 bg-[var(--sb)]">
                    <div class="flex flex-col items-center justify-center gap-1">
                        <span class="text-white/70 text-[9px] font-medium uppercase"
                            >&copy; {{ date('Y') }} {{ get_setting('APP_NAME', 'Mini Lara') }}</span>
                        
                        @php
                            $devName = $_ENV['DEVELOPER_NAME'] ?? get_setting('DEVELOPER_NAME', '');
                            $devUrl = $_ENV['DEVELOPER_URL'] ?? get_setting('DEVELOPER_URL', '#');
                            $compName = $_ENV['DEVELOPED_BY_COMPANY'] ?? get_setting('DEVELOPED_BY_COMPANY', '');
                            $compUrl = $_ENV['DEVELOPED_BY_COMPANY_URL'] ?? get_setting('DEVELOPED_BY_COMPANY_URL', '#');
                        @endphp

                        <span class="text-[8px] text-white/50 uppercase font-medium">
                            Developed by 
                            @if ($devName && $compName)
                                <a href="{{ $devUrl }}" target="_blank" class="text-white/70 hover:underline">{{ $devName }}</a>
                                at 
                                <a href="{{ $compUrl }}" target="_blank" class="text-white/70 hover:underline">{{ $compName }}</a>
                            @elseif ($devName)
                                <a href="{{ $devUrl }}" target="_blank" class="text-white/70 hover:underline">{{ $devName }}</a>
                            @elseif ($compName)
                                <a href="{{ $compUrl }}" target="_blank" class="text-white/70 hover:underline">{{ $compName }}</a>
                            @else
                                <span class="text-white/30">System Admin</span>
                            @endif
                        </span>
                    </div>
                </div>
            </aside>
        @endif
        <!-- CONTENT AREA -->
        <div
            class="flex-1 flex flex-col min-w-0 {{ ($isPrint ? 'bg-white' : 'bg-bp') }} relative {{ ($showLayout && !$isPrint) ? 'md:ml-[240px]' : '' }}"
        >
            @if ($showLayout && !$isPrint)
                <!-- TOPBAR -->
                <header
                    class="bg-bs/80 backdrop-blur-md border-b border-ts/10 h-14 flex-shrink-0 z-40 fixed top-0 right-0 left-0 md:left-[240px]"
                >
                    <div class="px-3 sm:px-5 h-full flex items-center gap-3">
                        <!-- Left: Hamburger (mobile) -->
                        <button
                            @click="mobileOpen = true"
                            class="md:hidden flex-shrink-0 p-2 rounded-md text-ts hover:bg-bp"
                        >
                            <i class="ri-menu-2-line text-2xl"></i>
                        </button>
                        <!-- Center: Search Bar (constrained width) -->
                        <div class="w-full max-w-xs relative group">
                            <i
                                class="ri-search-2-line absolute left-3 top-1/2 -translate-y-1/2 text-ts/50 group-hover:text-accent transition-colors text-sm"
                            ></i>
                            <input
                                type="text"
                                @click="toggleSearch()"
                                readonly
                                class="w-full h-8 bg-bs/60 border border-ts/10 rounded-lg pl-9 pr-24 text-xs font-medium text-tp placeholder:text-ts/40 cursor-pointer hover:border-accent/40 hover:bg-bs transition-all focus:ring-0"
                                placeholder="Quick Search..."
                            />
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                <span
                                    class="text-[8px] font-bold text-ts/40 bg-bp px-1 py-0.5 rounded border border-ts/10"
                                    >Ctrl</span
                                >
                                <span
                                    class="text-[8px] font-bold text-ts/40 bg-bp px-1 py-0.5 rounded border border-ts/10"
                                    >K</span
                                >
                            </div>
                        </div>
                        <!-- Right: Profile (pushed to far right, space reserved for future) -->
                        <div class="flex items-center gap-3 flex-shrink-0 ml-auto">
                            <div class="h-8 w-px bg-white/5"></div>
                            <div x-data="{ profileOpen: false }" class="relative">
                                <button
                                    @click="profileOpen = !profileOpen"
                                    class="flex items-center justify-center rounded-full hover:ring-2 hover:ring-accent/10 transition-all duration-300"
                                >
                                    <div
                                        class="w-9 h-9 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center text-accent overflow-hidden shadow-sm"
                                    >
                                        @if (!empty($user['image']))
                                            <img src="{{ asset($user['image']) }}" class="w-full h-full object-cover" />
                                        @else
                                            <i class="ri-user-star-line text-base"></i>
                                        @endif
                                    </div>
                                </button>
                                <div
                                    x-show="profileOpen"
                                    @click.away="profileOpen = false"
                                    x-cloak
                                    class="absolute right-0 mt-3 w-56 bg-bs border border-ts/10 rounded-2xl py-2 z-50 shadow-2xl ring-1 ring-white/5"
                                >
                                    <!-- User Info Header -->
                                    <div class="px-4 py-3 border-b border-ts/5 mb-1">
                                        <div class="text-[14px] font-bold text-tp truncate">{{ $user['name'] ?? 'Admin' }}</div>
                                        <div class="text-[12px] font-bold text-ts tracking-tight mt-0.5"><span>@</span>{{ $user['username'] ?? 'username' }}</div>
                                    </div>

                                    <a
                                        href="{{ url('/profile') }}"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-ts hover:bg-bp hover:text-tp transition-colors group"
                                    >
                                        <i class="ri-user-settings-line group-hover:text-accent"></i>
                                        <span class="font-semibold">My Account</span>
                                    </a>
                                    <div class="h-px bg-white/5 my-1"></div>
                                    <a
                                        href="{{ url('/auth/logout') }}"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-500/10 transition-colors group"
                                    >
                                        <i class="ri-logout-circle-r-line"></i>
                                        <span class="font-semibold">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            @endif
            <main
                class="flex-1 overflow-y-auto {{ ($isPrint || !$showLayout) ? 'pt-0' : 'pt-14' }} scroll-smooth animate-page-fade"
            >
                @yield ('content')
            </main>
        </div>
    </div>
    {{ App\View::renderAlert() }}
    @stack('scripts')
</body>
</html>
