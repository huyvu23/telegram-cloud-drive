<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký - Telegram Cloud Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <a href="/lang/vi" class="px-3 py-1.5 rounded-lg text-white hover:bg-white/20 transition {{ ($currentLocale ?? 'vi') == 'vi' ? 'bg-white/30' : '' }}">
            🇻🇳 Tiếng Việt
        </a>
        <a href="/lang/en" class="px-3 py-1.5 rounded-lg text-white hover:bg-white/20 transition {{ ($currentLocale ?? 'vi') == 'en' ? 'bg-white/30' : '' }}">
            🇬🇧 English
        </a>
    </div>
    
    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-sm">
        <div class="text-center mb-6 sm:mb-8">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fab fa-telegram text-4xl sm:text-5xl text-blue-500"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Telegram Cloud Drive</h1>
            <p class="text-gray-500 mt-2 text-sm sm:text-base">Tạo tài khoản mới</p>
        </div>

        <div id="form-container">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Họ và tên</label>
                <input type="text" id="name" required
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    placeholder="Nguyễn Văn A">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" required
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    placeholder="your@email.com">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Mật khẩu</label>
                <input type="password" id="password" required minlength="6"
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    placeholder="Tối thiểu 6 ký tự">
            </div>

            <div id="error-msg" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm hidden"></div>
            <div id="success-msg" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm hidden"></div>

            <button type="button" id="btn-register" onclick="doRegister()"
                class="w-full bg-blue-600 text-white py-3 sm:py-4 rounded-xl font-semibold hover:bg-blue-700 transition text-base">
                <span id="btn-text">Đăng ký</span>
            </button>
        </div>

        <p class="text-center mt-6 text-gray-600 text-sm">
            Đã có tài khoản? 
            <a href="/login" class="text-blue-600 hover:underline font-medium">Đăng nhập ngay</a>
        </p>
    </div>

    <script>
    function doRegister() {
        var name = document.getElementById('name').value;
        var email = document.getElementById('email').value;
        var password = document.getElementById('password').value;
        var errorEl = document.getElementById('error-msg');
        var successEl = document.getElementById('success-msg');
        var btn = document.getElementById('btn-register');
        var btnText = document.getElementById('btn-text');
        
        if (!name || !email || !password) {
            errorEl.textContent = 'Vui lòng nhập đầy đủ thông tin';
            errorEl.classList.remove('hidden');
            return;
        }
        
        if (password.length < 6) {
            errorEl.textContent = 'Mật khẩu phải từ 6 ký tự';
            errorEl.classList.remove('hidden');
            return;
        }
        
        errorEl.classList.add('hidden');
        successEl.classList.add('hidden');
        btn.disabled = true;
        btnText.textContent = 'Đang xử lý...';
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/auth/register', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Accept', 'application/json');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btnText.textContent = 'Đăng ký';
                
                if (xhr.status === 200 || xhr.status === 201) {
                    var data = JSON.parse(xhr.responseText);
                    localStorage.setItem('token', data.accessToken);
                    successEl.textContent = 'Đăng ký thành công! Đang chuyển...';
                    successEl.classList.remove('hidden');
                    setTimeout(function() {
                        window.location.href = '/drive';
                    }, 1000);
                } else {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        errorEl.textContent = data.error || 'Đăng ký thất bại';
                    } catch(e) {
                        errorEl.textContent = 'Đăng ký thất bại (lỗi server)';
                    }
                    errorEl.classList.remove('hidden');
                }
            }
        };
        
        xhr.send(JSON.stringify({ name: name, email: email, password: password }));
    }
    </script>
</body>
</html>