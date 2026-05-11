<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Telegram Cloud Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="no-block bg-gradient-to-br from-blue-600 to-blue-800 min-h-screen flex items-center justify-center p-4">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <a href="/lang/vi" class="px-3 py-1.5 rounded-lg text-white hover:bg-white/20 transition {{ ($currentLocale ?? 'vi') == 'vi' ? 'bg-white/30' : '' }}">
            🇻🇳 Tiếng Việt
        </a>
        <a href="/lang/en" class="px-3 py-1.5 rounded-lg text-white hover:bg-white/20 transition {{ ($currentLocale ?? 'vi') == 'en' ? 'bg-white/30' : '' }}">
            🇬🇧 English
        </a>
    </div>
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fab fa-telegram text-4xl text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Telegram Cloud Drive</h1>
            <p class="text-gray-500 mt-2">Đăng nhập để sử dụng</p>
        </div>
        
        <div id="error-message" class="bg-red-100 text-red-700 px-4 py-3 rounded-xl mb-4 hidden"></div>
        
        <form id="login-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" required
                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="email@example.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                <input type="password" id="password" required
                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="********">
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center">
                <input type="checkbox" id="remember" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</label>
            </div>
            
            <button type="submit" id="submit-btn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold transition flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i>
                <span>Đăng nhập</span>
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm">
                Chưa có tài khoản? <a href="/register" class="text-blue-600 hover:underline font-medium">Đăng ký</a>
            </p>
        </div>
        
        <div class="mt-8 pt-6 border-t text-center">
            <a href="/" class="text-blue-600 hover:underline text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Về trang chủ
            </a>
        </div>
    </div>

    <script>
        // Load saved credentials
        const savedEmail = localStorage.getItem('saved_email');
        const savedPassword = localStorage.getItem('saved_password');
        const rememberMe = localStorage.getItem('remember_me') === 'true';
        
        if (savedEmail && rememberMe) {
            document.getElementById('email').value = savedEmail;
            document.getElementById('password').value = savedPassword || '';
            document.getElementById('remember').checked = true;
        }
        
        // Check if already logged in
        const currentToken = localStorage.getItem('token');
        if (currentToken) {
            // Verify token and redirect
            fetch('/api/auth/me', {
                headers: { 'Authorization': `Bearer ${currentToken}`, 'Accept': 'application/json' }
            }).then(res => {
                if (res.ok) window.location.href = '/drive';
            }).catch(() => {});
        }
        
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('submit-btn');
            const errorDiv = document.getElementById('error-message');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            btn.disabled = true;
            errorDiv.classList.add('hidden');
            
            try {
                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await res.json();
                
                if (res.ok) {
                    // Save token
                    localStorage.setItem('token', data.accessToken);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    
                    // Remember me
                    if (remember) {
                        localStorage.setItem('saved_email', email);
                        localStorage.setItem('saved_password', password);
                        localStorage.setItem('remember_me', 'true');
                    } else {
                        localStorage.removeItem('saved_email');
                        localStorage.removeItem('saved_password');
                        localStorage.setItem('remember_me', 'false');
                    }
                    
                    // Cookie (30 days if remember)
                    const days = remember ? 30 : 7;
                    const expires = new Date();
                    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
                    document.cookie = `auth_token=${data.accessToken};expires=${expires.toUTCString()};path=/`;
                    
                    window.location.href = '/drive';
                } else {
                    errorDiv.textContent = data.error || 'Đăng nhập thất bại!';
                    errorDiv.classList.remove('hidden');
                    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Đăng nhập';
                    btn.disabled = false;
                }
            } catch (err) {
                errorDiv.textContent = 'Lỗi kết nối! Vui lòng thử lại.';
                errorDiv.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Đăng nhập';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>