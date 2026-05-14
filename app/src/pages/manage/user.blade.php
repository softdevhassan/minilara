@extends ('layout.app')
@section ('content')
    @php
use App\DB; 

$userId = $_GET['id'] ?? null;
$userObj = $userId ? DB::table('users')->where('id', $userId)->first() : null; 
$user = $userObj ? (array)$userObj : null;

// Load dynamic permissions from config
$permissions_config = require BASE_PATH . '/app/config/permissions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_path = $user['image'] ?? '/uploads/avatars/default-avatar.webp';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = BASE_PATH . '/app/public/uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $image_path = '/uploads/avatars/' . $fileName;
        }
    }

    $data = [
        'name' => $_POST['name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'details' => $_POST['details'] ?? '',
        'locked' => isset($_POST['locked']) ? 1 : 0,
        'image' => $image_path,
        'allowed_routes' => json_encode($_POST['permissions'] ?? [])
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($userId) {
        DB::table('users')->where('id', $userId)->update($data);
        set_alert('success', 'User profile updated successfully.');
    } else {
        DB::table('users')->insert($data);
        set_alert('success', 'New user account created successfully.');
    }
    redirect('/users');
}

$user_perms = json_decode($user['allowed_routes'] ?? '[]', true) ?: [];
@endphp

    <div class="max-w-full py-4 px-4 sm:px-5 space-y-10" x-data="{ locked: <?= ($user['locked'] ?? 0) ? 'true' : 'false' ?> }">
        @include('layout.breadcrumbs')
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-base font-semibold text-tp uppercase">{{ $userId ? 'Edit User' : 'Add New User' }}</h1>
                <p class="text-[10px] font-semibold text-ts uppercase opacity-70 mt-2">Manage system users and access levels</p>
            </div>
            <a href="{{ url('/users') }}" class="luxury-button border border-ts/10 text-ts text-xs font-bold uppercase">
                <i class="ri-arrow-left-line mr-2"></i> Back to Users
            </a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Left Column: Avatar & Account Status -->
            <div class="lg:col-span-4 space-y-8">
                <div class="luxury-card p-10 text-center relative overflow-hidden group">
                    <div class="absolute top-4 right-4 z-10">
                         <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="locked" value="1" class="sr-only peer" x-model="locked">
                            <div class="w-11 h-6 bg-ts/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                    
                    <div class="relative inline-block mb-6">
                        <div class="w-32 h-32 rounded-full bg-bs border border-ts/10 flex items-center justify-center overflow-hidden mx-auto">
                            @if(!empty($user['image']))
                                <img id="avatar_preview" src="{{ asset($user['image']) }}" class="w-full h-full object-cover" />
                            @else
                                <div id="avatar_placeholder" class="flex items-center justify-center w-full h-full">
                                    <i class="ri-user-star-line text-4xl text-ts/30"></i>
                                </div>
                                <img id="avatar_preview" class="w-full h-full object-cover hidden" />
                            @endif
                        </div>
                        <input type="file" name="image" id="avatar_input" class="hidden" accept="image/*" onchange="previewAvatar(this)" />
                        <label for="avatar_input" class="absolute -bottom-2 -right-2 w-10 h-10 bg-accent text-bp rounded-full flex items-center justify-center cursor-pointer shadow-xl hover:scale-110 transition-transform">
                            <i class="ri-pencil-line text-lg"></i>
                        </label>
                    </div>

                    <h3 class="text-base font-semibold text-tp uppercase">{{ $user['name'] ?? 'Account Preview' }}</h3>
                    <p class="text-[10px] font-semibold text-accent uppercase mt-1 opacity-80">{{ $user['username'] ?? 'username' }}</p>
                    
                    <div x-show="locked" x-cloak class="mt-4 p-2 bg-red-500/10 border border-red-500/20 rounded-lg">
                        <span class="text-[9px] font-bold text-red-500 uppercase">Account Locked</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Data & Permissions -->
            <div class="lg:col-span-8 space-y-8">
                <div class="luxury-card p-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Full Name</label>
                            <input type="text" name="name" value="{{ $user['name'] ?? '' }}" class="luxury-input w-full font-bold" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Username</label>
                            <input type="text" name="username" value="{{ $user['username'] ?? '' }}" class="luxury-input w-full font-bold" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Email Address</label>
                            <input type="email" name="email" value="{{ $user['email'] ?? '' }}" class="luxury-input w-full font-bold" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ $user['phone'] ?? '' }}" class="luxury-input w-full font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Password</label>
                            <input type="password" name="password" class="luxury-input w-full font-bold" placeholder="{{ $userId ? '•••••••• (Leave blank to keep)' : 'Enter Password' }}" {{ $userId ? '' : 'required' }} />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-semibold text-ts uppercase px-1">Address / Notes</label>
                            <textarea name="details" class="luxury-input w-full font-bold h-24 p-4 resize-none">{{ $user['details'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t border-ts/10">
                        <p class="text-[10px] font-semibold text-accent uppercase opacity-80 mb-6">User Permissions</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($permissions_config as $slug => $routes)
                                @php if($slug === 'home') continue; @endphp
                                <label class="flex items-center gap-3 p-3 bg-bs/50 border border-ts/5 rounded-xl cursor-pointer hover:border-accent/30 transition-all group">
                                    <input type="checkbox" name="permissions[]" value="{{ $slug }}" class="w-4 h-4 rounded border-ts/20 text-accent focus:ring-accent/30" {{ in_array($slug, $user_perms) ? 'checked' : '' }} />
                                    <span class="text-[10px] font-bold uppercase group-hover:text-accent transition-colors">{{ str_replace('-', ' ', $slug) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end pt-10 mt-10 border-t border-ts/10">
                        <button type="submit" class="luxury-button luxury-button-primary px-16 py-4 text-xs font-bold uppercase shadow-xl shadow-accent/20">
                            {{ $userId ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('avatar_preview');
                var placeholder = document.getElementById('avatar_placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
@endsection
