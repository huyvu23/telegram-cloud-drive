<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Telegram Cloud Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white shadow-lg sticky top-0 z-50">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Admin Dashboard</h1>
                    <p class="text-xs text-purple-200">Telegram Cloud Drive Management</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/drive" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm transition">
                    <i class="fas fa-home mr-2"></i> Quay lại Drive
                </a>
                <button onclick="logout()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                </button>
            </div>
        </div>
    </header>

    <main class="p-6 max-w-7xl mx-auto">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs">Tổng Users</p>
                        <p id="stat-users" class="text-3xl font-bold text-blue-600">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-lg text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs">Đã cài Telegram</p>
                        <p id="stat-with-config" class="text-3xl font-bold text-green-600">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-lg text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs">Chưa cài Telegram</p>
                        <p id="stat-without-config" class="text-3xl font-bold text-red-600">0</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-lg text-red-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs">Tổng Files</p>
                        <p id="stat-files" class="text-3xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file text-lg text-yellow-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs">Tổng Storage</p>
                        <p id="stat-storage" class="text-xl font-bold text-purple-600">0 B</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-database text-lg text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Bot Config -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                <i class="fas fa-robot text-purple-600"></i> Accounts Bot (Master Bot)
            </h2>
            <p class="text-sm text-gray-500 mb-4">Bot này dùng cho users chưa có Telegram riêng. Admin cài đặt ở đây.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bot Token</label>
                    <input type="text" id="accounts-bot-token" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                        placeholder="123456789:ABCdef...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Channel ID</label>
                    <input type="text" id="accounts-channel-id" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                        placeholder="-1001234567890">
                </div>
                <div class="flex items-end gap-2">
                    <button onclick="saveAccountsBot()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-save mr-2"></i> Lưu
                    </button>
                    <button onclick="loadAccountsBot()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition text-sm">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div id="accounts-status" class="mt-3 text-sm hidden"></div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <i class="fas fa-users text-blue-600"></i> Quản lý Users
                    <span id="total-users" class="text-sm font-normal text-gray-500">(0 users)</span>
                </h2>
                <button onclick="loadAll()" class="px-3 py-1.5 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200 transition">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Storage</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Files</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telegram</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table" class="divide-y divide-gray-200">
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit User Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b flex items-center justify-between sticky top-0 bg-white">
                <h2 class="text-lg font-bold">Chỉnh sửa User</h2>
                <button onclick="closeEditModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-400"></i>
                </button>
            </div>
            <div class="p-4 space-y-4">
                <input type="hidden" id="edit-user-id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
                    <input type="text" id="edit-name" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="edit-role" class="w-full px-4 py-2 border rounded-xl">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Storage Limit</label>
                    <div class="flex gap-2">
                        <input type="number" id="edit-storage-limit" class="flex-1 px-4 py-2 border rounded-xl" placeholder="10737418240">
                        <select id="edit-storage-unit" class="px-3 py-2 border rounded-xl" onchange="convertStorage()">
                            <option value="gb">GB</option>
                            <option value="mb">MB</option>
                            <option value="kb">KB</option>
                            <option value="bytes">Bytes</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">VD: 10GB = 10737418240 bytes</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit-verified" class="w-4 h-4">
                    <label for="edit-verified" class="text-sm">Đã xác minh email</label>
                </div>
                
                <hr class="my-4">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fab fa-telegram text-blue-500"></i> Telegram Config
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bot Token</label>
                    <input type="text" id="edit-bot-token" class="w-full px-4 py-2 border rounded-xl text-sm" placeholder="123456789:ABCdef...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Channel ID</label>
                    <input type="text" id="edit-channel-id" class="w-full px-4 py-2 border rounded-xl text-sm" placeholder="-1001234567890">
                </div>
                
                <div id="edit-current-telegram" class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg hidden"></div>
                
                <div class="flex gap-3 pt-4">
                    <button onclick="closeEditModal()" class="flex-1 px-4 py-2 border rounded-xl hover:bg-gray-50">Hủy</button>
                    <button onclick="saveUser()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentUserData = [];

    async function checkAdmin() {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const res = await fetch('/api/auth/me', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            if (!data.user || data.user.role !== 'admin') {
                alert('Bạn không có quyền truy cập trang Admin!');
                window.location.href = '/drive';
                return;
            }

            loadAll();
        } catch {
            window.location.href = '/login';
        }
    }

    function loadAll() {
        loadStats();
        loadUsers();
        loadAccountsBot();
    }

    async function loadStats() {
        const token = localStorage.getItem('token');
        const res = await fetch('/api/admin/stats', {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        const data = await res.json();
        
        document.getElementById('stat-users').textContent = data.totalUsers || 0;
        document.getElementById('stat-with-config').textContent = data.usersWithConfig || 0;
        document.getElementById('stat-without-config').textContent = data.usersWithoutConfig || 0;
        document.getElementById('stat-files').textContent = data.totalFiles || 0;
        document.getElementById('stat-storage').textContent = formatSize(data.totalStorage || 0);
    }

    async function loadUsers() {
        const token = localStorage.getItem('token');
        const res = await fetch('/api/admin/users', {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        const data = await res.json();
        currentUserData = data.users;
        
        document.getElementById('total-users').textContent = `(${data.users.length} users)`;
        
        const tbody = document.getElementById('users-table');
        tbody.innerHTML = data.users.map((user, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400 text-sm">${index + 1}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">${user.name}</p>
                            <p class="text-xs text-gray-500">${user.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs">
                    <div class="text-gray-600">${formatSize(user.storage_used)}</div>
                    <div class="text-gray-400">/${formatSize(user.storage_limit)}</div>
                </td>
                <td class="px-4 py-3 text-sm text-center">${user.file_count}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 text-xs rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'}">
                        ${user.role}
                    </span>
                </td>
                <td class="px-4 py-3">
                    ${user.telegram_configured 
                        ? `<span class="text-green-600 text-xs"><i class="fas fa-check-circle"></i> OK</span>
                           <span class="text-xs text-gray-400 block">${user.telegram_channel_id || ''}</span>` 
                        : `<span class="text-red-500 text-xs"><i class="fas fa-times-circle"></i> Chưa</span>
                           <button onclick="quickSetTelegram(${user.id})" class="text-xs text-blue-500 hover:underline">+ Set ngay</button>`}
                </td>
                <td class="px-4 py-3">
                    <button onclick="editUser(${user.id})" class="p-2 hover:bg-blue-100 rounded-lg text-blue-600 transition" title="Sửa">
                        <i class="fas fa-edit text-sm"></i>
                    </button>
                    <button onclick="deleteUser(${user.id}, '${user.name.replace(/'/g, "\\'")}')" class="p-2 hover:bg-red-100 rounded-lg text-red-600 transition" title="Xóa">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async function loadAccountsBot() {
        const res = await fetch('/api/admin/accounts/config');
        const data = await res.json();
        
        document.getElementById('accounts-bot-token').value = '';
        document.getElementById('accounts-channel-id').value = data.channel_id || '';
        
        const status = document.getElementById('accounts-status');
        if (data.configured) {
            status.className = 'mt-3 text-sm text-green-600 bg-green-50 p-2 rounded-lg';
            status.innerHTML = `<i class="fas fa-check-circle"></i> Đã cấu hình: <strong>${data.channel_name || data.channel_id}</strong>`;
        } else {
            status.className = 'mt-3 text-sm text-yellow-600 bg-yellow-50 p-2 rounded-lg';
            status.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Chưa cấu hình. Users chưa có Telegram sẽ không upload được.`;
        }
        status.classList.remove('hidden');
    }

    async function saveAccountsBot() {
        const token = localStorage.getItem('token');
        const botToken = document.getElementById('accounts-bot-token').value.trim();
        const channelId = document.getElementById('accounts-channel-id').value.trim();
        const status = document.getElementById('accounts-status');

        if (!botToken || !channelId) {
            status.className = 'mt-3 text-sm text-red-600 bg-red-50 p-2 rounded-lg';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> Vui lòng nhập đầy đủ Bot Token và Channel ID';
            status.classList.remove('hidden');
            return;
        }

        status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
        status.className = 'mt-3 text-sm text-blue-600 bg-blue-50 p-2 rounded-lg';
        status.classList.remove('hidden');

        try {
            const res = await fetch('/api/admin/accounts/setup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ bot_token: botToken, channel_id: channelId })
            });

            const data = await res.json();

            if (res.ok) {
                status.className = 'mt-3 text-sm text-green-600 bg-green-50 p-2 rounded-lg';
                status.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message} - Channel: ${data.channel}`;
                loadUsers();
            } else {
                status.className = 'mt-3 text-sm text-red-600 bg-red-50 p-2 rounded-lg';
                status.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Lỗi'}`;
            }
        } catch (e) {
            status.className = 'mt-3 text-sm text-red-600 bg-red-50 p-2 rounded-lg';
            status.innerHTML = `<i class="fas fa-exclamation-circle"></i> Lỗi kết nối: ${e.message}`;
        }
    }

    async function quickSetTelegram(userId) {
        const accountsToken = document.getElementById('accounts-bot-token').value.trim();
        const accountsChannel = document.getElementById('accounts-channel-id').value.trim();
        
        if (!accountsToken || !accountsChannel) {
            alert('Chưa cấu hình Accounts Bot! Vui lòng cài đặt Accounts Bot trước.');
            return;
        }
        
        if (!confirm('Set Telegram cho user này dùng Accounts Bot?')) return;
        
        const token = localStorage.getItem('token');
        const res = await fetch(`/api/admin/users/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                telegram_bot_token: accountsToken,
                telegram_channel_id: accountsChannel,
                telegram_channel_name: 'Shared Channel'
            })
        });

        if (res.ok) {
            loadStats();
            loadUsers();
            alert('Đã set Telegram cho user!');
        } else {
            const data = await res.json();
            alert(data.error || 'Lỗi');
        }
    }

    function editUser(id) {
        const user = currentUserData.find(u => u.id === id);
        if (!user) return;

        document.getElementById('edit-user-id').value = user.id;
        document.getElementById('edit-name').value = user.name || '';
        document.getElementById('edit-role').value = user.role || 'user';
        document.getElementById('edit-storage-limit').value = user.storage_limit || 10737418240;
        document.getElementById('edit-verified').checked = user.is_verified;
        document.getElementById('edit-bot-token').value = user.telegram_bot_token || '';
        document.getElementById('edit-channel-id').value = user.telegram_channel_id || '';
        
        const currentInfo = document.getElementById('edit-current-telegram');
        if (user.telegram_configured) {
            currentInfo.innerHTML = `<strong>Hiện tại:</strong> ${user.telegram_channel_name || user.telegram_channel_id}`;
            currentInfo.classList.remove('hidden');
        } else {
            currentInfo.classList.add('hidden');
        }
        
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    function convertStorage() {
        const value = parseInt(document.getElementById('edit-storage-limit').value) || 0;
        const unit = document.getElementById('edit-storage-unit').value;
        let bytes = value;
        
        if (unit === 'gb') bytes = value * 1073741824;
        else if (unit === 'mb') bytes = value * 1048576;
        else if (unit === 'kb') bytes = value * 1024;
        
        document.getElementById('edit-storage-limit').value = bytes;
    }

    async function saveUser() {
        const token = localStorage.getItem('token');
        const id = document.getElementById('edit-user-id').value;
        const name = document.getElementById('edit-name').value;
        const role = document.getElementById('edit-role').value;
        const storage_limit = document.getElementById('edit-storage-limit').value;
        const is_verified = document.getElementById('edit-verified').checked;
        const telegram_bot_token = document.getElementById('edit-bot-token').value.trim() || null;
        const telegram_channel_id = document.getElementById('edit-channel-id').value.trim() || null;

        const res = await fetch(`/api/admin/users/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ 
                name, role, storage_limit, is_verified,
                telegram_bot_token,
                telegram_channel_id
            })
        });

        if (res.ok) {
            closeEditModal();
            loadAll();
            alert('Đã lưu thành công!');
        } else {
            const data = await res.json();
            alert(data.error || 'Lỗi khi lưu');
        }
    }

    async function deleteUser(id, name) {
        if (!confirm(`Xóa user "${name}"?\n\n⚠️ Hành động này sẽ xóa tất cả files và không thể hoàn tác.`)) return;

        const token = localStorage.getItem('token');
        const res = await fetch(`/api/admin/users/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (res.ok) {
            loadAll();
            alert('Đã xóa user!');
        } else {
            const data = await res.json();
            alert(data.error || 'Lỗi khi xóa user');
        }
    }

    function formatSize(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function logout() {
        localStorage.removeItem('token');
        window.location.href = '/login';
    }

    checkAdmin();
    </script>
</body>
</html>