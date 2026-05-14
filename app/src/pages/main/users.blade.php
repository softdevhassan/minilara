@extends ('layout.app')
@section ('content')
    @php
use App\DB; 

// Delete Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') { 
    $id = intval($_POST['id']); 
    $userToDeleteObj = DB::table('users')->where('id', $id)->first(); 
    $userToDelete = $userToDeleteObj ? (array)$userToDeleteObj : null;
    if ($userToDelete && $userToDelete['username'] !== 'admin') { 
        DB::table('users')->where('id', $id)->delete(); 
        set_alert('success', 'User account deleted successfully.'); 
    } else { 
        set_alert('error', 'Critical Error: Admin account is protected and cannot be deleted.'); 
    } 
    redirect('/users');
} 

// Status Toggle Controller
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && !empty($_GET['id'])) { 
    $id = intval($_GET['id']); 
    $userToUpdateObj = DB::table('users')->where('id', $id)->first(); 
    $userToUpdate = $userToUpdateObj ? (array)$userToUpdateObj : null;
    if ($userToUpdate && $userToUpdate['username'] !== 'admin') { 
        $newStatus = $userToUpdate['locked'] ? 0 : 1; 
        DB::table('users')->where('id', $id)->update(['locked' => $newStatus]); 
        set_alert('success', 'User status updated successfully.'); 
    } 
    redirect('/users');
} 

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit; 

$total_users = DB::table('users')->count();
$total_pages = ceil($total_users / $limit); 

$users = DB::table('users')
    ->orderBy('name', 'ASC')
    ->limit($limit)
    ->offset($offset)
    ->get()
    ->map(fn($x)=>(array)$x)
    ->all(); 

@endphp
    <div class="py-4 px-4 sm:px-5 max-w-7xl mx-auto">
    @include('layout.breadcrumbs')
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 bg-accent/10 border border-accent/20 rounded-xl flex items-center justify-center text-accent"
                    >
                        <i class="ri-user-settings-line text-xl"></i>
                    </div>
                    <h1 class="text-base font-semibold text-tp uppercase">Users</h1>
                </div>
                <p class="text-[10px] font-semibold uppercase text-ts opacity-70">System Security: Control User Access & Administrative Permissions</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/add/user') }}" class="luxury-button luxury-button-primary px-8 py-3 text-xs font-bold uppercase">
                    <i class="ri-user-add-line mr-2"></i> Add New User
                </a>
            </div>
        </div>

        <div class="luxury-card overflow-hidden bg-bs/30 border-ts/5">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-bs border-b border-ts/10 text-[11px] font-semibold text-ts uppercase">
                        <tr>
                            <th class="px-4 py-3">User Profile</th>
                            <th class="px-4 py-3">User Details</th>
                            <th class="px-4 py-3">Access Level</th>
                            <th class="px-4 py-3">Access Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ts/5">
                        @foreach ($users as $u)
                            <tr class="hover:bg-ts/5 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <img
                                                src="{{ asset($u['image'] ?: '/images/default-avatar.webp') }}"
                                                class="w-10 h-10 rounded-full border-2 border-ts/10 object-cover"
                                            />
                                            <div
                                                class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-bp {{ $u['locked'] ? 'bg-red-500' : 'bg-emerald-500 shadow-sm shadow-emerald-500/50' }}"
                                            ></div>
                                        </div>
                                        <div>
                                            <div class="font-bold font-semibold text-sm uppercase leading-none mb-1.5">{{ $u['name'] }}</div>
                                            <div class="text-[10px] text-ts font-bold uppercase opacity-70">{{ $u['email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs font-bold font-semibold uppercase mb-1">@ {{ $u['username'] }}</div>
                                    <div class="text-[10px] text-accent font-bold uppercase">{{ $u['phone'] }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-[9px] text-ts font-bold uppercase mb-1.5 opacity-50">Route Permissions</div>
                                    <div class="text-[10px] text-tp font-semibold uppercase truncate max-w-[220px]">
                                        {{ $u['username'] === 'admin' ? 'FULL UNRESTRICTED ACCESS' : ($u['allowed_routes'] ?: 'RESTRICTED (NONE)') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <a
                                        href="?action=update_status&id={{ $u['id'] }}"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[9px] font-bold border transition-all duration-300 {{ $u['locked'] ? 'bg-red-500/10 text-red-600 border-red-500/20' : 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 hover:bg-emerald-500/20' }}"
                                    >
                                        <i class="{{ $u['locked'] ? 'ri-lock-2-fill' : 'ri-shield-check-fill' }} text-xs"></i>
                                        {{ $u['locked'] ? 'ACCOUNT LOCKED' : 'ACCESS ACTIVE' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ url('/edit/user/' . $u['id']) }}"
                                            class="w-10 h-10 flex items-center justify-center text-ts hover:text-accent hover:bg-accent/5 rounded-xl border border-ts/10 hover:border-accent/30 transition-all duration-300"
                                            title="Edit User"
                                        >
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        @if ($u['username'] !== 'admin')
                                            <button
                                                data-id="{{ $u['id'] }}"
                                                onclick="deleteUser(this.dataset.id)"
                                                class="w-10 h-10 flex items-center justify-center text-ts hover:text-red-600 hover:bg-red-50 rounded-xl border border-ts/10 hover:border-red-500/30 transition-all duration-300"
                                                title="Revoke & Delete"
                                            >
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($total_pages > 1)
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-[11px] text-ts font-bold uppercase opacity-70">
                    Showing Users {{ $offset + 1 }} to {{ min($offset + $limit, $total_users) }} | System Total: {{ $total_users }} Users
                </div>
                <div class="flex gap-2">
                    @if ($page > 1)
                        <a href="?{{ http_build_query(array_merge($_GET, ['page' => $page - 1])) }}" class="luxury-button border-ts/10 text-tp hover:border-accent font-semibold text-[10px] px-6 py-2 bg-bp uppercase">Previous</a>
                    @endif
                    @if ($page < $total_pages)
                        <a href="?{{ http_build_query(array_merge($_GET, ['page' => $page + 1])) }}" class="luxury-button border-ts/10 text-tp hover:border-accent font-semibold text-[10px] px-6 py-2 bg-bp uppercase">Next Page</a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This cannot be undone.')) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(f);
                f.submit();
            }
        }
    </script>
@endsection
