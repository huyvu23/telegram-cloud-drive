<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cài đặt Telegram - Telegram Cloud Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-purple-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 w-full max-w-lg">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fab fa-telegram text-3xl text-blue-500"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Cài đặt Telegram</h1>
            <p class="text-gray-500 text-sm mt-1">Mỗi user có channel & bot riêng</p>
        </div>

        <!-- Guide -->
        <div class="bg-blue-50 rounded-xl p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Hướng dẫn setup
            </h3>
            <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                <li>Tạo bot mới qua <strong>@BotFather</strong> → /newbot</li>
                <li>Tạo Channel riêng trên Telegram</li>
                <li>Thêm bot vào Channel với quyền <strong>Admin</strong></li>
                <li>Copy <strong>Bot Token</strong> và <strong>Channel ID</strong></li>
                <li>Channel ID có dạng: <code class="bg-blue-100 px-1 rounded">-1001234567890</code></li>
            </ol>
        </div>

        <!-- Status -->
        <div id="status-box" class="mb-4 p-3 rounded-xl text-sm hidden">
            <i class="fas mr-2"></i>
            <span id="status-text"></span>
        </div>

        <!-- Form -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-robot text-blue-500 mr-1"></i> Bot Token
                </label>
                <input type="text" id="bot-token" 
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-bullseye text-blue-500 mr-1"></i> Channel ID
                </label>
                <input type="text" id="channel-id" 
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    placeholder="-1001234567890">
            </div>

            <button onclick="setupTelegram()" id="btn-setup"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3 rounded-xl font-semibold transition shadow-lg flex items-center justify-center gap-2">
                <i class="fas fa-check"></i> Xác nhận & Lưu
            </button>

            <a href="/drive" class="block text-center text-gray-500 hover:text-gray-700 py-2">
                ← Quay lại Drive
            </a>
        </div>
    </div>

    <script>
    async function checkConfig() {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        const res = await fetch('/api/auth/telegram/config', {
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (data.configured) {
            document.getElementById('channel-id').value = data.channel_id || '';
            showStatus('success', `Đã kết nối: ${data.channel_name || 'Channel'}`);
        }
    }

    async function setupTelegram() {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        const botToken = document.getElementById('bot-token').value.trim();
        const channelId = document.getElementById('channel-id').value.trim();
        const btn = document.getElementById('btn-setup');
        const statusBox = document.getElementById('status-box');

        if (!botToken || !channelId) {
            showStatus('error', 'Vui lòng nhập đầy đủ thông tin');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang kiểm tra...';
        statusBox.classList.add('hidden');

        try {
            const res = await fetch('/api/auth/telegram/setup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ bot_token: botToken, channel_id: channelId })
            });

            const data = await res.json();

            if (res.ok) {
                showStatus('success', '✓ ' + data.message + ' - ' + data.channel);
                setTimeout(() => {
                    window.location.href = '/drive';
                }, 2000);
            } else {
                showStatus('error', '✗ ' + (data.error || 'Có lỗi xảy ra'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận & Lưu';
            }
        } catch (e) {
            showStatus('error', '✗ Lỗi kết nối: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận & Lưu';
        }
    }

    function showStatus(type, text) {
        const box = document.getElementById('status-box');
        box.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
        
        if (type === 'success') {
            box.classList.add('bg-green-100', 'text-green-700');
        } else {
            box.classList.add('bg-red-100', 'text-red-700');
        }
        
        box.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${text}`;
    }

    checkConfig();
    </script>
</body>
</html>