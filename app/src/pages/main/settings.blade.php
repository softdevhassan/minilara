@extends ('layout.app')

@php
use App\DB; 
use App\Auth; 

$user = Auth::user(); 
$superUserList = array_map('trim', explode(',', $_ENV['SUPER_USERS'] ?? 'admin'));
$isSuperUser = in_array($user['username'] ?? '', $superUserList); 

$defaultTab = $isSuperUser ? 'general' : 'appearance'; 
$print_keys = ['PRINT_PROPERTY', 'PRINT_INVENTORY', 'PRINT_CONTACTS']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isSuperUser) {
        $exclude = ['color_scheme_mode', 'color_scheme_data', 'current_tab'];

        // Branding Uploads
        $uploadDir = BASE_PATH . '/app/public/uploads/branding/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES['APP_LOGO_FILE']) && $_FILES['APP_LOGO_FILE']['error'] === 0) {
            $ext = pathinfo($_FILES['APP_LOGO_FILE']['name'], PATHINFO_EXTENSION);
            $logoName = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['APP_LOGO_FILE']['tmp_name'], $uploadDir . $logoName)) {
                $_POST['APP_LOGO'] = 'uploads/branding/' . $logoName;
            }
        }

        if (isset($_FILES['APP_FAVICON_FILE']) && $_FILES['APP_FAVICON_FILE']['error'] === 0) {
            $ext = pathinfo($_FILES['APP_FAVICON_FILE']['name'], PATHINFO_EXTENSION);
            $favName = 'favicon_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['APP_FAVICON_FILE']['tmp_name'], $uploadDir . $favName)) {
                $_POST['APP_FAVICON'] = 'uploads/branding/' . $favName;
            }
        }

        $_POST['APP_USE_DYNAMIC_FAVICON'] = isset($_POST['APP_USE_DYNAMIC_FAVICON']) ? '1' : '0';
        $_POST['APP_ENABLE_ALERTS'] = isset($_POST['APP_ENABLE_ALERTS']) ? '1' : '0';
        $_POST['APP_SHOW_BREADCRUMBS'] = isset($_POST['APP_SHOW_BREADCRUMBS']) ? '1' : '0';

        foreach ($_POST as $key => $val) {
            if (in_array($key, $exclude)) continue;
            
            $existingObj = DB::table('settings')->where('key', $key)->first();
            $existing = $existingObj ? (array)$existingObj : null;
            if ($existing) {
                DB::table('settings')->where('key', $key)->update(['value1' => $val]);
            } else {
                DB::table('settings')->insert(['key' => $key, 'value1' => $val]);
            }
        }
    }

    // Global Commission settings
    $commission_keys = ['DEFAULT_COMMISSION_TYPE', 'DEFAULT_COMMISSION_VALUE', 'DEFAULT_COMMISSION_FIXED'];
    foreach ($commission_keys as $key) {
        if (isset($_POST[$key])) {
            $val = $_POST[$key];
            $existingObj = DB::table('settings')->where('key', $key)->first();
            $existing = $existingObj ? (array)$existingObj : null;
            if ($existing) {
                DB::table('settings')->where('key', $key)->update(['value1' => $val]);
            } else {
                DB::table('settings')->insert(['key' => $key, 'value1' => $val]);
            }
        }
    }

    // Appearance (Personal or System Default)
    if (isset($_POST['color_scheme_mode']) && isset($_POST['color_scheme_data'])) {
        $colorSchemeData = json_decode($_POST['color_scheme_data'], true);
        $colorScheme = [
            'mode' => $_POST['color_scheme_mode'],
            'colors' => $colorSchemeData,
        ];
        
        DB::table('users')->where('id', $user['id'])->update(['color_scheme' => json_encode($colorScheme)]);

        if ($isSuperUser) {
            $checkDefObj = DB::table('settings')->where('key', 'SYSTEM_DEFAULT_COLORS')->first();
            $checkDef = $checkDefObj ? (array)$checkDefObj : null;
            if ($checkDef) {
                DB::table('settings')->where('key', 'SYSTEM_DEFAULT_COLORS')->update(['value1' => json_encode($colorSchemeData)]);
            } else {
                DB::table('settings')->insert(['key' => 'SYSTEM_DEFAULT_COLORS', 'value1' => json_encode($colorSchemeData)]);
            }
        }
        
        // Refresh session
        $_SESSION['user'] = (array)DB::table('users')->where('id', $user['id'])->first();
    }

    set_alert('success', 'Settings updated successfully.');
    $currentTab = $_POST['current_tab'] ?? $defaultTab;
    redirect('/settings#' . $currentTab);
}

$settings_raw = DB::table('settings')->get()->map(fn($x)=>(array)$x)->all();
$settings = [];
foreach ($settings_raw as $s) { $settings[$s['key']] = $s['value1']; }

$colorScheme = json_decode($user['color_scheme'] ?? '{}', true); 
$presets = [ 
    [ 'name' => 'Enterprise White (Ocean Blue)', 'tp' => '#0f172a', 'ts' => '#4b5563', 'bp' => '#ffffff', 'bs' => '#f9fafb', 'sb' => '#2563eb', 'sl' => '#eff6ff', 'sh' => '#ffffff', 'sa' => '#ffffff', 'st' => '#2563eb', 'accent' => '#2563eb', 'accent_light' => '#60a5fa', 'accent_dark' => '#1d4ed8' ],
    [ 'name' => 'Slate Dark (Emerald)', 'tp' => '#f1f5f9', 'ts' => '#94a3b8', 'bp' => '#0f172a', 'bs' => '#1e293b', 'sb' => '#111827', 'sl' => '#94a3b8', 'sh' => '#ffffff', 'sa' => 'rgba(16, 185, 129, 0.1)', 'st' => '#10b981', 'accent' => '#10b981', 'accent_light' => '#34d399', 'accent_dark' => '#064e3b' ], 
    [ 'name' => 'Slate Gray (Modern Dark) - Default', 'tp' => '#f9fafb', 'ts' => '#9ca3af', 'bp' => '#1f2937', 'bs' => '#374151', 'sb' => '#111827', 'sl' => '#9ca3af', 'sh' => '#ffffff', 'sa' => 'rgba(255, 255, 255, 0.05)', 'st' => '#f9fafb', 'accent' => '#3b82f6', 'accent_light' => '#60a5fa', 'accent_dark' => '#1d4ed8' ],
    [ 'name' => 'Enterprise White (Royal Purple)', 'tp' => '#0f172a', 'ts' => '#4b5563', 'bp' => '#ffffff', 'bs' => '#f9fafb', 'sb' => '#8b5cf6', 'sl' => '#f5f3ff', 'sh' => '#ffffff', 'sa' => '#ffffff', 'st' => '#8b5cf6', 'accent' => '#8b5cf6', 'accent_light' => '#c084fc', 'accent_dark' => '#7e22ce' ],
    [ 'name' => 'Enterprise White (Noir)', 'tp' => '#0f172a', 'ts' => '#4b5563', 'bp' => '#ffffff', 'bs' => '#f9fafb', 'sb' => '#18181b', 'sl' => '#f4f4f5', 'sh' => '#ffffff', 'sa' => '#ffffff', 'st' => '#18181b', 'accent' => '#18181b', 'accent_light' => '#3f3f46', 'accent_dark' => '#000000' ],
    [ 'name' => 'Noir Luxury (Gold)', 'tp' => '#ffffff', 'ts' => '#71717a', 'bp' => '#000000', 'bs' => '#0c0c0e', 'sb' => '#000000', 'sl' => '#71717a', 'sh' => '#ffffff', 'sa' => 'rgba(212, 175, 55, 0.1)', 'st' => '#d4af37', 'accent' => '#d4af37', 'accent_light' => '#f9e076', 'accent_dark' => '#996515' ]
]; 
$initialColors = $colorScheme['colors'] ?? $presets[0];

@endphp

@push('head')
    <script>
        window.setupSettingsDashboard = function() {
            try {
                const initial = {!! json_encode($initialColors ?? []) !!};
                const defaults = {!! json_encode($presets[0]) !!};
                const colors = Object.assign({}, defaults, initial);
                
                return {
                    tab: '{{ $defaultTab }}',
                    colorMode: '{{ $colorScheme['mode'] ?? 'preset' }}',
                    customColors: colors,
                    setTab(t) {
                        this.tab = t;
                        history.replaceState(null, null, '#' + t);
                    },
                    applyPreset(p) {
                        this.customColors = Object.assign({}, p);
                        this.colorMode = 'preset';
                    }
                };
            } catch (e) {
                console.error('Settings UI Error:', e);
                return { tab: 'general', customColors: {} };
            }
        };
    </script>
@endpush

@section ('content')
    <div class="max-w-full py-4 px-4 sm:px-5 pb-10" x-data="setupSettingsDashboard()" x-init="if (window.location.hash) tab = window.location.hash.substring(1)">
        @include('layout.breadcrumbs')
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 mb-5">
            <div>
                <h1 class="text-base font-semibold text-tp uppercase">Settings</h1>
                <p class="text-[10px] font-semibold text-ts uppercase opacity-70 mt-2">System Settings</p>
            </div>
            <div class="flex gap-2 p-1 bg-bs/50 rounded-xl border border-ts/10 overflow-x-auto max-w-full no-scrollbar">
                @if ($isSuperUser)
                    <button
                        type="button"
                        @click="setTab('general')"
                        :class="tab === 'general' ? 'bg-bp text-accent font-bold border border-accent/20' : 'text-ts font-semibold'"
                        class="px-4 py-2 rounded-lg text-[10px] uppercase transition-colors whitespace-nowrap"
                    >
                        General
                    </button>
                    <button
                        type="button"
                        @click="setTab('branding')"
                        :class="tab === 'branding' ? 'bg-bp text-accent font-bold border border-accent/20' : 'text-ts font-semibold'"
                        class="px-4 py-2 rounded-lg text-[10px] uppercase transition-colors whitespace-nowrap"
                    >
                        Branding
                    </button>
                    <button
                        type="button"
                        @click="setTab('print')"
                        :class="tab === 'print' ? 'bg-bp text-accent font-bold border border-accent/20' : 'text-ts font-semibold'"
                        class="px-4 py-2 rounded-lg text-[10px] uppercase transition-colors whitespace-nowrap"
                    >
                        Print Setup
                    </button>
                @endif
                <button
                    type="button"
                    @click="setTab('commission')"
                    :class="tab === 'commission' ? 'bg-bp text-accent font-bold border border-accent/20' : 'text-ts font-semibold'"
                    class="px-4 py-2 rounded-lg text-[10px] uppercase transition-colors whitespace-nowrap"
                >
                    Commission
                </button>
                <button
                    type="button"
                    @click="setTab('appearance')"
                    :class="tab === 'appearance' ? 'bg-bp text-accent font-bold border border-accent/20' : 'text-ts font-semibold'"
                    class="px-4 py-2 rounded-lg text-[10px] uppercase transition-colors whitespace-nowrap"
                >
                    Appearance
                </button>
            </div>
        </div>

        <form method="POST" class="space-y-8" enctype="multipart/form-data">
            <input type="hidden" name="current_tab" :value="tab" />

            @if ($isSuperUser)
                <!-- General -->
                <div x-show="tab === 'general'" x-transition class="luxury-card p-10 space-y-8 bg-bs/20">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="col-span-2 space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Full Company Name</label>
                            <input type="text" name="APP_FULL_NAME" value="{{ $settings['APP_FULL_NAME'] ?? 'Mini Lara' }}" class="luxury-input w-full font-bold h-10" required />
                        </div>
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Short Name</label>
                            <input type="text" name="APP_SHORT_NAME" value="{{ $settings['APP_SHORT_NAME'] ?? 'MiniLara' }}" class="luxury-input w-full font-bold h-10" required />
                        </div>
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Tagline</label>
                            <input type="text" name="APP_TAGLINE" value="{{ $settings['APP_TAGLINE'] ?? 'Real Estate Management' }}" class="luxury-input w-full font-bold h-10" />
                        </div>
                    </div>
                    <div class="pt-8 border-t border-ts/5 grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Primary Phone</label>
                            <input type="text" name="APP_PHONE1" value="{{ $settings['APP_PHONE1'] ?? '' }}" class="luxury-input w-full font-bold h-10" />
                        </div>
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Secondary Phone</label>
                            <input type="text" name="APP_PHONE2" value="{{ $settings['APP_PHONE2'] ?? '' }}" class="luxury-input w-full font-bold h-10" />
                        </div>
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Email</label>
                            <input type="email" name="APP_EMAIL" value="{{ $settings['APP_EMAIL'] ?? '' }}" class="luxury-input w-full font-bold h-10" />
                        </div>
                        <div class="col-span-1 space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Website URL</label>
                            <input type="text" name="APP_WEBSITE" value="{{ $settings['APP_WEBSITE'] ?? '' }}" class="luxury-input w-full font-bold h-10" placeholder="https://..." />
                        </div>
                        <div class="col-span-2 space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Office Address</label>
                            <textarea name="APP_ADDRESS" class="luxury-input w-full font-bold h-24 p-4 resize-none">{{ $settings['APP_ADDRESS'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-ts/5">
                        <h3 class="text-xs font-semibold text-tp uppercase mb-6">Developer Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-2.5">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Developer Name</label>
                                <input type="text" name="DEVELOPER_NAME" value="{{ $settings['DEVELOPER_NAME'] ?? '' }}" class="luxury-input w-full font-bold h-10" />
                            </div>
                            <div class="space-y-2.5">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Developer Portfolio URL</label>
                                <input type="text" name="DEVELOPER_URL" value="{{ $settings['DEVELOPER_URL'] ?? '' }}" class="luxury-input w-full font-bold h-10" placeholder="https://..." />
                            </div>
                            <div class="space-y-2.5">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Company Name</label>
                                <input type="text" name="DEVELOPED_BY_COMPANY" value="{{ $settings['DEVELOPED_BY_COMPANY'] ?? '' }}" class="luxury-input w-full font-bold h-10" />
                            </div>
                            <div class="space-y-2.5">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Company Website URL</label>
                                <input type="text" name="DEVELOPED_BY_COMPANY_URL" value="{{ $settings['DEVELOPED_BY_COMPANY_URL'] ?? '' }}" class="luxury-input w-full font-bold h-10" placeholder="https://..." />
                            </div>
                        </div>
                    </div>
                    <div class="pt-8 border-t border-ts/5">
                        <h3 class="text-xs font-semibold text-tp uppercase mb-6">System Behavior</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div class="flex items-center justify-between p-4 bg-bp border border-ts/5 rounded-2xl">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase">Enable System Alerts</div>
                                    <div class="text-[9px] font-bold text-ts opacity-70 uppercase">Toast notifications for CRUD actions</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="APP_ENABLE_ALERTS" value="1" class="sr-only peer" {{ ($settings['APP_ENABLE_ALERTS'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-ts/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-bp border border-ts/5 rounded-2xl">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase">Page Breadcrumbs</div>
                                    <div class="text-[9px] font-bold text-ts opacity-70 uppercase">Show navigation path inside pages</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="APP_SHOW_BREADCRUMBS" value="1" class="sr-only peer" {{ ($settings['APP_SHOW_BREADCRUMBS'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-ts/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Branding -->
                <div x-show="tab === 'branding'" x-transition class="luxury-card p-10 space-y-10 bg-bs/20" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-4">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Company Logo</label>
                            <div class="p-6 bg-bp border border-ts/5 rounded-3xl flex items-center gap-6">
                                <div class="w-24 h-24 bg-bs rounded-2xl border border-ts/10 flex items-center justify-center overflow-hidden">
                                    @if (!empty($settings['APP_LOGO']))
                                        <img src="{{ asset($settings['APP_LOGO']) }}" class="max-w-full max-h-full object-contain" />
                                    @else
                                        <i class="ri-image-2-line text-3xl text-ts/30"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="APP_LOGO_FILE" class="hidden" id="logo_upload" />
                                    <label for="logo_upload" class="luxury-button luxury-button-primary py-3 text-[9px] font-bold cursor-pointer">CHANGE LOGO</label>
                                    <p class="text-[8px] text-ts mt-3 font-bold uppercase opacity-50">PNG / SVG RECOMMENDED</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Site Favicon</label>
                            <div class="p-6 bg-bp border border-ts/5 rounded-3xl flex items-center gap-6">
                                <div class="w-24 h-24 bg-bs rounded-2xl border border-ts/10 flex items-center justify-center overflow-hidden">
                                    @if (($settings['APP_USE_DYNAMIC_FAVICON'] ?? '1') === '1')
                                        <div class="w-12 h-10 bg-accent rounded-xl flex items-center justify-center text-bp font-bold text-lg uppercase">
                                            {{ $settings['APP_ICON_TEXT'] ?? 'PA' }}
                                        </div>
                                    @else
                                        @if (!empty($settings['APP_FAVICON']))
                                            <img src="{{ asset($settings['APP_FAVICON']) }}" class="w-12 h-10 object-contain" />
                                        @else
                                            <i class="ri-compass-3-line text-3xl text-ts/30"></i>
                                        @endif
                                    @endif
                                </div>
                                <div class="flex-1 space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" name="APP_USE_DYNAMIC_FAVICON" value="1" {{ ($settings['APP_USE_DYNAMIC_FAVICON'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded-lg border-ts/20 text-accent focus:ring-accent/30" />
                                        <span class="text-[10px] font-semibold uppercase group-hover:text-accent">Dynamic Theme SVG</span>
                                    </label>
                                    <input type="file" name="APP_FAVICON_FILE" class="hidden" id="fav_upload" />
                                    <label for="fav_upload" class="luxury-button border border-ts/10 text-ts py-3 text-[9px] font-bold cursor-pointer">UPLOAD CUSTOM</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 pt-8 border-t border-ts/5">
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Favicon Initials</label>
                            <input type="text" name="APP_ICON_TEXT" value="{{ $settings['APP_ICON_TEXT'] ?? 'PA' }}" class="luxury-input w-full font-bold h-10 text-center text-accent" />
                        </div>
                        <div class="space-y-2.5">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Title Separator</label>
                            <input type="text" name="TITLE_SEPERATOR" value="{{ $settings['TITLE_SEPERATOR'] ?? ' | ' }}" class="luxury-input w-full font-bold h-10 text-center" />
                        </div>
                    </div>
                </div>
                <!-- Print Setup -->
                <div x-show="tab === 'print'" x-transition class="luxury-card p-10 space-y-8 bg-bs/20" x-cloak>
                    @foreach ($print_keys as $key)
                        <div class="border-b border-ts/5 pb-10 last:border-0 last:pb-0">
                            <h3 class="text-[11px] font-semibold text-tp uppercase mb-6">{{ str_replace('_', ' ', $key) }} CONFIGURATION</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-semibold text-ts uppercase px-1">Margin Top (e.g. 1.5in)</label>
                                    <input type="text" name="{{ $key }}_MARGIN_TOP" value="{{ $settings[$key . '_MARGIN_TOP'] ?? '1.5in' }}" class="luxury-input w-full font-bold h-10" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-semibold text-ts uppercase px-1">Padding Top (e.g. 0.2in)</label>
                                    <input type="text" name="{{ $key }}_PADDING_TOP" value="{{ $settings[$key . '_PADDING_TOP'] ?? '0.2in' }}" class="luxury-input w-full font-bold h-10" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Commission -->
            <div x-show="tab === 'commission'" x-transition class="luxury-card p-10 space-y-10 bg-bs/20" x-cloak>
                 <div class="max-w-2xl">
                    <h3 class="text-base font-semibold text-tp uppercase mb-2">Default Commission Model</h3>
                    <p class="text-[10px] font-semibold text-ts uppercase opacity-70 mb-5">Standard values for new property listings</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-4">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Policy Type</label>
                            <div class="flex gap-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="DEFAULT_COMMISSION_TYPE" value="PERCENTAGE" class="sr-only peer" {{ ($settings['DEFAULT_COMMISSION_TYPE'] ?? 'PERCENTAGE') === 'PERCENTAGE' ? 'checked' : '' }} />
                                    <div class="p-6 rounded-2xl border-2 border-ts/5 text-center transition-all peer-checked:border-accent peer-checked:bg-accent/5 peer-checked:text-accent">
                                        <i class="ri-percent-line text-2xl mb-2 block"></i>
                                        <span class="text-[10px] font-semibold uppercase">Percentage</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="DEFAULT_COMMISSION_TYPE" value="FIXED" class="sr-only peer" {{ ($settings['DEFAULT_COMMISSION_TYPE'] ?? 'PERCENTAGE') === 'FIXED' ? 'checked' : '' }} />
                                    <div class="p-6 rounded-2xl border-2 border-ts/5 text-center transition-all peer-checked:border-accent peer-checked:bg-accent/5 peer-checked:text-accent">
                                        <i class="ri-bank-card-line text-2xl mb-2 block"></i>
                                        <span class="text-[10px] font-semibold uppercase">Fixed</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-6 pt-2">
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Default Percentage (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="DEFAULT_COMMISSION_VALUE" value="{{ $settings['DEFAULT_COMMISSION_VALUE'] ?? '1.00' }}" class="luxury-input w-full font-bold h-10 pr-12" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ts font-bold text-xs">%</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Default Fixed Amount (PKR)</label>
                                <div class="relative">
                                    <input type="number" name="DEFAULT_COMMISSION_FIXED" value="{{ $settings['DEFAULT_COMMISSION_FIXED'] ?? '50000' }}" class="luxury-input w-full font-bold h-10 pr-14" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ts font-bold text-[10px]">PKR</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appearance -->
            <div x-show="tab === 'appearance'" x-transition class="luxury-card p-10 space-y-10 bg-bs/20" x-cloak>
                <div class="flex gap-4 mb-8">
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" x-model="colorMode" value="preset" class="hidden" />
                        <div :class="colorMode === 'preset' ? 'border-accent bg-accent/5' : 'border-ts/10'" class="p-6 rounded-2xl border-2 transition-colors text-center">
                            <i class="ri-palette-line text-2xl mb-2 block" :class="colorMode === 'preset' ? 'text-accent' : 'text-ts'"></i>
                            <span class="text-[10px] font-semibold uppercase block">Signature Themes</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" x-model="colorMode" value="custom" class="hidden" />
                        <div :class="colorMode === 'custom' ? 'border-accent bg-accent/5' : 'border-ts/10'" class="p-6 rounded-2xl border-2 transition-colors text-center">
                            <i class="ri-equalizer-line text-2xl mb-2 block" :class="colorMode === 'custom' ? 'text-accent' : 'text-ts'"></i>
                            <span class="text-[10px] font-semibold uppercase block">Custom Spectrum</span>
                        </div>
                    </label>
                </div>

                <!-- Presets Grid -->
                <div x-show="colorMode === 'preset'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-fade-in">
                    @foreach ($presets as $p)
                        <div @click='applyPreset({!! e(json_encode($p)) !!})' :class='customColors.name === {!! e(json_encode($p["name"])) !!} ? "ring-2 ring-accent border-accent" : "border-ts/10"' class="luxury-card p-4 cursor-pointer hover:border-accent/40 transition-all bg-bp">
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-8 h-8 rounded-lg shadow-lg border border-white/10" style="background-color: {{ $p['accent'] }}"></div>
                                <span class="text-[10px] font-semibold uppercase">{{ $p['name'] }}</span>
                            </div>
                            <div class="flex gap-1.5 h-3.5 rounded-full overflow-hidden border border-ts/10 shadow-inner">
                                <div class="flex-1" style="background-color: {{ $p['tp'] }}"></div>
                                <div class="flex-1" style="background-color: {{ $p['accent_light'] }}"></div>
                                <div class="flex-1" style="background-color: {{ $p['bp'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Custom Pickers -->
                <div x-show="colorMode === 'custom'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 animate-fade-in">
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-semibold text-ts uppercase border-b border-ts/5 pb-3">Colors & Typography</h4>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Main Text</span>
                            <input type="color" x-model="customColors.tp" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Soft Text</span>
                            <input type="color" x-model="customColors.ts" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Page Background</span>
                            <input type="color" x-model="customColors.bp" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Surface Base</span>
                            <input type="color" x-model="customColors.bs" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-semibold text-ts uppercase border-b border-ts/5 pb-3">Interface Highlights</h4>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">System Accent</span>
                            <input type="color" x-model="customColors.accent" @input="customColors.name = 'Custom'" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Active Text</span>
                            <input type="color" x-model="customColors.st" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-semibold text-ts uppercase border-b border-ts/5 pb-3">Sidebar Profile</h4>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Sidebar Base</span>
                            <input type="color" x-model="customColors.sb" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                        <div class="flex items-center justify-between p-2 hover:bg-ts/5 rounded-xl transition-colors">
                            <span class="text-[11px] font-semibold uppercase">Sidebar Text</span>
                            <input type="color" x-model="customColors.sl" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0 bg-transparent" />
                        </div>
                    </div>
                </div>
                <input type="hidden" name="color_scheme_mode" :value="colorMode" />
                <input type="hidden" name="color_scheme_data" :value="JSON.stringify(customColors)" />
            </div>

            <div class="flex justify-end pt-6 pb-4 border-t border-ts/5 mt-6">
                <button type="submit" class="luxury-button luxury-button-primary px-10 py-2.5 font-bold text-xs uppercase">
                    <i class="ri-save-line mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
        input[type='color']::-webkit-color-swatch-wrapper { padding: 0; }
        input[type='color']::-webkit-color-swatch { border: 2px solid rgba(0,0,0,0.1); border-radius: 12px; }
    </style>
@endsection
