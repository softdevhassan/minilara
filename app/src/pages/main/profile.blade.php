@extends ('layout.app')
@section ('content')
    @php
use App\DB;
use App\Auth;

$user = Auth::user();
$userId = $user['id'];
$userDataObj = DB::table('users')->where('id', $userId)->first();
$userData = $userDataObj ? (array)$userDataObj : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'details' => $_POST['details'] ?? '',
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = BASE_PATH . '/app/public/uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $data['image'] = '/uploads/avatars/' . $fileName;
        }
    }

    DB::table('users')->where('id', $userId)->update($data);
    $_SESSION['user'] = (array)DB::table('users')->where('id', $userId)->first();
    set_alert('success', 'Profile updated successfully.');
    redirect('/profile');
}

@endphp

    <div class="max-w-full py-4 px-4 sm:px-5">
        @include('layout.breadcrumbs')
        <div class="mb-10">
            <h1 class="text-base font-semibold text-tp uppercase">My Profile</h1>
            <p class="text-[10px] font-semibold text-ts uppercase opacity-70 mt-2">Manage account and security</p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Avatar Column -->
                <div class="lg:col-span-1">
                    <div class="luxury-card p-4 text-center">
                        <div class="relative inline-block group">
                            <div class="w-32 h-32 rounded-full bg-bs border-2 border-ts/10 overflow-hidden mb-4 mx-auto flex items-center justify-center">
                                @if(!empty($userData['image']))
                                    <img id="avatar_preview" src="{{ asset($userData['image']) }}" class="w-full h-full object-cover" />
                                @else
                                    <div id="avatar_placeholder" class="flex items-center justify-center w-full h-full">
                                        <i class="ri-user-star-line text-4xl text-ts/30"></i>
                                    </div>
                                    <img id="avatar_preview" class="w-full h-full object-cover hidden" />
                                @endif
                            </div>
                            <input type="file" name="image" id="avatar_upload" class="hidden" accept="image/*" onchange="previewAvatar(this)" />
                            <label for="avatar_upload" class="absolute bottom-2 right-2 w-10 h-10 bg-accent text-bp rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-transform">
                                <i class="ri-pencil-line text-lg"></i>
                            </label>
                        </div>
                        <h3 class="text-sm font-semibold text-tp uppercase">{{ $userData['name'] ?? 'Unknown User' }}</h3>
                        <p class="text-[10px] font-semibold text-accent uppercase mt-1">Account User</p>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="luxury-card p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Full Name</label>
                                <input type="text" name="name" value="{{ $userData['name'] ?? '' }}" class="luxury-input w-full font-bold" required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Email Address</label>
                                <input type="email" name="email" value="{{ $userData['email'] ?? '' }}" class="luxury-input w-full font-bold" required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Phone Number</label>
                                <input type="text" name="phone" value="{{ $userData['phone'] ?? '' }}" class="luxury-input w-full font-bold" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Password <span class="text-[8px] opacity-50">(Leave blank to keep current)</span></label>
                                <input type="password" name="password" class="luxury-input w-full font-bold" placeholder="••••••••" />
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] font-semibold text-ts uppercase px-1">Profile Notes</label>
                                <textarea name="details" class="luxury-input w-full font-bold h-24 p-4 resize-none">{{ $userData['details'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="submit" class="luxury-button luxury-button-primary px-10 py-3 text-[11px] font-semibold uppercase">
                                Save Changes
                            </button>
                        </div>
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
