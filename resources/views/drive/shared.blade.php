@extends('layouts.app')

@section('content')
<div x-data="sharedPage()" class="min-h-screen flex flex-col bg-gradient-to-br from-blue-500 to-purple-600">
    <header class="bg-white/10 backdrop-blur-md text-white py-4 px-4">
        <div class="container mx-auto flex items-center gap-3">
            <i class="fab fa-telegram text-3xl sm:text-4xl"></i>
            <div>
                <h1 class="text-lg sm:text-xl font-bold">Telegram Cloud Drive</h1>
                <p class="text-xs sm:text-sm text-blue-200">File được chia sẻ</p>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4">
        <div x-show="loading" class="text-center text-white">
            <i class="fas fa-spinner fa-spin text-4xl sm:text-5xl mb-4"></i>
            <p class="text-base sm:text-lg">Đang tải...</p>
        </div>

        <div x-show="!loading && error" class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-3xl sm:text-4xl text-red-500"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-2">Link không hợp lệ</h2>
            <p class="text-gray-500 text-sm mb-6" x-text="error"></p>
            <a href="/login" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition text-sm sm:text-base">
                Đăng nhập để tiếp tục
            </a>
        </div>

        <div x-show="!loading && !error && file" class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full">
            <div class="text-center mb-6">
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas text-4xl sm:text-5xl" :class="getFileIcon(file.mime_type)" style="color: #3b82f6"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 truncate" x-text="file.name"></h2>
                <p class="text-gray-500 text-sm" x-text="formatSize(file.size)"></p>
            </div>

            <div x-show="downloadsRemaining !== null" class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Lượt tải còn lại</span>
                    <span class="font-semibold" x-text="downloadsRemaining"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" 
                        :style="'width: ' + (downloadsRemaining / 100 * 100) + '%'"></div>
                </div>
            </div>

            <button @click="downloadFile()" 
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition shadow-lg flex items-center justify-center gap-2 text-base">
                <i class="fas fa-download"></i> Tải xuống
            </button>
        </div>
    </main>
    
    <!-- Mobile bottom padding -->
    <div class="h-4 sm:hidden"></div>
</div>

<script>
function sharedPage() {
    return {
        token: '{{ $token }}',
        file: null,
        loading: true,
        error: null,
        downloadsRemaining: null,

        async init() {
            await this.loadSharedFile();
        },

        async loadSharedFile() {
            try {
                const res = await fetch(`/api/share/${this.token}`);
                const data = await res.json();
                
                if (!res.ok) {
                    this.error = data.error || 'Không thể tải file';
                    return;
                }
                
                this.file = data.file;
                this.downloadsRemaining = data.downloads_remaining;
            } catch (err) {
                this.error = 'Không thể kết nối';
            }
            this.loading = false;
        },

        async downloadFile() {
            window.location.href = `/api/share/${this.token}/download`;
        },

        formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        getFileIcon(mimeType) {
            if (!mimeType) return 'fa-file';
            if (mimeType.startsWith('image/')) return 'fa-image text-blue-500';
            if (mimeType.startsWith('video/')) return 'fa-video text-red-500';
            if (mimeType.startsWith('audio/')) return 'fa-music text-purple-500';
            if (mimeType.includes('pdf')) return 'fa-file-pdf text-red-600';
            return 'fa-file text-gray-500';
        }
    }
}
</script>
@endsection