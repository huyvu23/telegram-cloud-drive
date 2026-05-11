@extends('layouts.app')

@section('content')
<div x-data="driveApp()" x-init="init()" class="min-h-screen flex flex-col bg-gray-100">
    
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fab fa-telegram text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-gray-900">Drive</h1>
                        <p class="text-xs text-gray-500" x-text="user ? user.name : ''"></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button @click="goToSettings()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-cog text-gray-600"></i>
                    </button>
                    <button @click="logout()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-sign-out-alt text-gray-600"></i>
                    </button>
                </div>
            </div>
            
            <!-- Storage -->
            <div class="mt-3">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span x-text="formatSize(storageUsed) + ' / ' + formatSize(storageLimit)"></span>
                    <span x-text="Math.round(storageUsed/storageLimit*100) + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-blue-600 h-1.5 rounded-full" 
                        :style="'width:' + Math.min(storageUsed/storageLimit*100, 100) + '%'"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1 p-4 max-w-6xl mx-auto w-full">
        
        <!-- Alerts -->
        <div x-show="showSuccess" x-transition
             class="fixed top-20 left-1/2 -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span x-text="successMessage"></span>
            <button @click="showSuccess = false" class="ml-2 hover:bg-green-600 rounded p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div x-show="showError" x-transition
             class="fixed top-20 left-1/2 -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <span x-text="errorMessage"></span>
            <button @click="showError = false" class="ml-2 hover:bg-red-600 rounded p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Telegram Warning -->
        <div x-show="!hasTelegramConfig" 
             class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
            <div class="flex-1">
                <p class="font-medium text-amber-800">Chua cai dat Telegram!</p>
                <p class="text-sm text-amber-600">Vao Settings de bat dau upload file.</p>
            </div>
            <button @click="goToSettings()" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium">
                Cai dat
            </button>
        </div>
        
        <!-- Search & Sort Bar -->
        <div class="flex flex-wrap gap-2 mb-4 items-center">
            
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="searchQuery" 
                    placeholder="Tim kiem file..."
                    class="w-full pl-10 pr-4 py-2.5 border rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button x-show="searchQuery" @click="searchQuery = ''" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Sort Dropdown -->
            <div class="relative">
                <button @click="$refs.sortMenu.classList.toggle('hidden')"
                    class="px-4 py-2.5 bg-white border rounded-xl flex items-center gap-2 hover:bg-gray-50">
                    <i class="fas fa-sort text-gray-500"></i>
                    <span class="text-gray-700">
                        <span x-show="sortBy === 'date'">Ngay</span>
                        <span x-show="sortBy === 'name'">Ten</span>
                        <span x-show="sortBy === 'size'">Kich thuoc</span>
                    </span>
                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                </button>
                <div x-ref="sortMenu" class="hidden absolute right-0 mt-2 bg-white border rounded-xl shadow-lg py-2 z-20 min-w-[150px]">
                    <button @click="sortBy = 'date'; $refs.sortMenu.classList.add('hidden')"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center justify-between"
                        :class="sortBy === 'date' ? 'text-blue-600 font-medium' : 'text-gray-700'">
                        Ngay
                        <i x-show="sortBy === 'date'" class="fas fa-check text-blue-500"></i>
                    </button>
                    <button @click="sortBy = 'name'; $refs.sortMenu.classList.add('hidden')"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center justify-between"
                        :class="sortBy === 'name' ? 'text-blue-600 font-medium' : 'text-gray-700'">
                        Ten
                        <i x-show="sortBy === 'name'" class="fas fa-check text-blue-500"></i>
                    </button>
                    <button @click="sortBy = 'size'; $refs.sortMenu.classList.add('hidden')"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center justify-between"
                        :class="sortBy === 'size' ? 'text-blue-600 font-medium' : 'text-gray-700'">
                        Kich thuoc
                        <i x-show="sortBy === 'size'" class="fas fa-check text-blue-500"></i>
                    </button>
                    <div class="border-t my-1"></div>
                    <button @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; $refs.sortMenu.classList.add('hidden')"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-600 flex items-center gap-2">
                        <i class="fas" :class="sortOrder === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                        <span x-text="sortOrder === 'asc' ? 'Tang dan' : 'Giam dan'"></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button @click="triggerUpload()" 
                :disabled="!hasTelegramConfig"
                :class="hasTelegramConfig ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                class="text-white px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition">
                <i class="fas fa-upload"></i>
                <span>Upload</span>
            </button>
            
            <button @click="showNewFolder = true" 
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 transition">
                <i class="fas fa-folder-plus text-yellow-500"></i>
                <span>Thu muc</span>
            </button>
            
            <button @click="showTrash = !showTrash" 
                :class="showTrash ? 'bg-red-100 text-red-600 border-red-200' : 'bg-white border-gray-200 text-gray-600'"
                class="px-5 py-2.5 rounded-xl font-medium flex items-center gap-2 border transition">
                <i class="fas fa-trash"></i>
                <span>Thu muc rac</span>
            </button>
            
            <!-- View Toggle -->
            <div class="ml-auto flex bg-white border border-gray-200 rounded-xl overflow-hidden">
                <button @click="viewMode = 'grid'" 
                    :class="viewMode === 'grid' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                    class="px-3 py-2">
                    <i class="fas fa-th"></i>
                </button>
                <button @click="viewMode = 'list'"
                    :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                    class="px-3 py-2">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
            <button @click="goHome()" class="hover:text-blue-600">
                <i class="fas fa-home"></i>
            </button>
            <template x-for="(crumb, i) in breadcrumbs" :key="crumb.id">
                <span class="flex items-center gap-2">
                    <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                    <span @click="goToFolder(crumb.id)" class="hover:text-blue-600 cursor-pointer" x-text="crumb.name"></span>
                </span>
            </template>
        </div>
        
        <!-- Upload Progress -->
        <div x-show="uploadingFiles.length > 0" class="space-y-2 mb-4">
            <template x-for="upload in uploadingFiles" :key="upload.id">
                <div class="bg-white rounded-xl p-4 shadow-sm border">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="getFileBgColor(upload.mimeType)">
                            <i class="fas text-lg" :class="getFileIcon(upload.mimeType)"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate" x-text="upload.name"></p>
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all" 
                                         :style="'width:' + upload.progress + '%'"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span x-text="formatSize(upload.loaded) + ' / ' + formatSize(upload.total)"></span>
                                    <span x-show="upload.status === 'done'" class="text-green-600 font-medium">Hoan tat!</span>
                                    <span x-show="upload.status === 'error'" class="text-red-600 font-medium">Loi!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Files Grid/List -->
        <div x-show="!showTrash">
            
            <!-- Empty -->
            <div x-show="files.length === 0 && folders.length === 0" class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600 mb-2">Trong</h3>
                <p class="text-gray-400">Upload file hoac tao thu muc</p>
            </div>
            
            <!-- Folders & Files -->
            <div x-show="filteredFiles.length > 0 || filteredFolders.length > 0" 
                :class="viewMode === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3' : 'space-y-2'">
                
                <!-- Folders -->
                <template x-for="folder in filteredFolders" :key="'f-' + folder.id">
                    <div @dblclick="goToFolder(folder.id)"
                        :class="viewMode === 'grid' ? 'bg-white p-4 rounded-xl shadow-sm hover:shadow cursor-pointer group' : 'bg-white p-4 rounded-xl shadow-sm hover:shadow cursor-pointer flex items-center gap-3 group'"
                        class="border border-gray-100 transition">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-folder text-xl text-yellow-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p x-text="folder.name" class="font-medium text-gray-800 truncate"></p>
                            <p class="text-xs text-gray-400">Thu muc</p>
                        </div>
                        <button @click.stop="deleteFolder(folder.id)" 
                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </template>
                
                <!-- Files -->
                <template x-for="file in filteredFiles" :key="file.id">
                    <div :class="viewMode === 'grid' ? 'bg-white p-4 rounded-xl shadow-sm hover:shadow cursor-pointer group' : 'bg-white p-4 rounded-xl shadow-sm hover:shadow cursor-pointer flex items-center gap-3 group'"
                        class="border border-gray-100 transition">
                        <div @click="downloadFile(file.id)" class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="getFileBgColor(file.mime_type)">
                                <i class="fas text-lg" :class="getFileIcon(file.mime_type)"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p x-text="file.name" class="font-medium text-gray-800 truncate"></p>
                                <p class="text-xs text-gray-400" x-text="formatSize(file.size)"></p>
                            </div>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button @click.stop="shareFile(file)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Chia se">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button @click.stop="deleteFile(file.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Xoa">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Trash -->
        <div x-show="showTrash">
            <div x-show="trashFiles.length === 0" class="text-center py-16">
                <i class="fas fa-trash-alt text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Thu muc rac trong</p>
            </div>
            
            <div x-show="trashFiles.length > 0" class="space-y-2">
                <template x-for="file in trashFiles" :key="file.id">
                    <div class="bg-white p-4 rounded-xl shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas text-gray-400" :class="getFileIcon(file.mime_type)"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p x-text="file.name" class="font-medium text-gray-800 truncate"></p>
                            <p class="text-xs text-gray-400" x-text="formatSize(file.size)"></p>
                        </div>
                        <button @click="restoreFile(file.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                            <i class="fas fa-trash-restore"></i>
                        </button>
                    </div>
                </template>
                
                <button @click="emptyTrash()" x-show="trashFiles.length > 0"
                    class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-medium mt-4">
                    <i class="fas fa-trash-alt mr-2"></i>Xoa vinh vien
                </button>
            </div>
        </div>
    </main>
    
    <!-- File Input -->
    <input type="file" id="file-input" @change="uploadFile($event)" class="hidden" multiple>
    
    <!-- Create Folder Modal -->
    <div x-show="showNewFolder" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.away="showNewFolder = false" class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <h3 class="text-lg font-bold mb-4">Tao thu muc</h3>
            <input type="text" x-model="newFolderName" @keyup.enter="createFolder()"
                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 mb-4"
                placeholder="Ten thu muc">
            <div class="flex gap-3">
                <button @click="showNewFolder = false" class="flex-1 py-3 border rounded-xl">Huy</button>
                <button @click="createFolder()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl">Tao</button>
            </div>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div x-show="showShare" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.away="showShare = false" class="bg-white rounded-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Chia se file</h3>
                <button @click="showShare = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-4 truncate" x-text="shareFileData ? shareFileData.name : ''"></p>
            
            <div class="flex gap-2">
                <input type="text" x-model="shareLink" readonly
                    class="flex-1 px-4 py-3 bg-gray-100 border rounded-xl text-sm truncate">
                <button @click="copyLink()" class="px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 font-medium">
                    <i class="fas fa-copy"></i>
                    <span>Copy</span>
                </button>
            </div>
            
            <div x-show="shareMessage" x-transition
                 class="mt-4 p-3 rounded-xl text-center font-medium"
                 :class="shareMessage.includes('Thanh') || shareMessage.includes('thanh') || shareMessage.includes('Da') || shareMessage.includes('success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                <i class="fas" :class="shareMessage.includes('Thanh') || shareMessage.includes('Da') ? 'fa-check-circle text-green-600' : 'fa-exclamation-circle text-red-600'"></i>
                <span x-text="shareMessage"></span>
            </div>
        </div>
    </div>
</div>

<script>
function driveApp() {
    return {
        // State
        user: null,
        files: [],
        folders: [],
        trashFiles: [],
        currentFolder: null,
        breadcrumbs: [],
        viewMode: 'grid',
        showTrash: false,
        showNewFolder: false,
        showShare: false,
        newFolderName: '',
        shareFileData: null,
        shareLink: '',
        shareMessage: '',
        storageUsed: 0,
        storageLimit: 10737418240,
        hasTelegramConfig: false,
        uploadingFiles: [],
        showSuccess: false,
        showError: false,
        searchQuery: '',
        sortBy: 'date', // date, name, size
        sortOrder: 'desc', // desc, asc
        successMessage: '',
        errorMessage: '',

        // Init
        async init() {
            const savedToken = this.getCookie('auth_token') || localStorage.getItem('token');
            if (!savedToken) {
                window.location.href = '/login';
                return;
            }
            localStorage.setItem('token', savedToken);
            await this.checkAuth();
        },

        // Cookie
        getCookie(name) {
            const ca = document.cookie.split(';');
            for (let c of ca) {
                c = c.trim();
                if (c.indexOf(name + '=') === 0) return c.substring(name.length + 1);
            }
            return null;
        },

        // Auth
        async checkAuth() {
            const token = localStorage.getItem('token');
            try {
                const res = await fetch('/api/auth/me', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                const data = await res.json();
                this.user = data.user;
                this.storageUsed = data.user.storageUsed || 0;
                this.storageLimit = data.user.storageLimit || 10737418240;
                this.hasTelegramConfig = data.user.hasTelegramConfig || false;
                this.setCookie('auth_token', token);
                await Promise.all([this.loadFiles(), this.loadFolders(), this.loadTrash()]);
            } catch {
                this.clearSession();
            }
        },

        clearSession() {
            localStorage.removeItem('token');
            document.cookie = 'auth_token=; max-age=0; path=/';
            window.location.href = '/login';
        },

        setCookie(name, value) {
            document.cookie = name + '=' + value + '; path=/; max-age=' + (7 * 24 * 60 * 60);
        },

        logout() {
            this.clearSession();
        },

        // Notify
        notify(message, type = 'success') {
            if (type === 'success') {
                this.successMessage = message;
                this.showSuccess = true;
                setTimeout(() => { this.showSuccess = false; }, 4000);
            } else {
                this.errorMessage = message;
                this.showError = true;
                setTimeout(() => { this.showError = false; }, 6000);
            }
        },

        // Load
        // Computed filtered and sorted files
        get filteredFiles() {
            let result = [...this.files];
            
            // Search
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(f => f.name.toLowerCase().includes(q));
            }
            
            // Sort
            result.sort((a, b) => {
                let valA, valB;
                if (this.sortBy === 'name') {
                    valA = a.name.toLowerCase();
                    valB = b.name.toLowerCase();
                    return this.sortOrder === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                } else if (this.sortBy === 'size') {
                    valA = a.size || 0;
                    valB = b.size || 0;
                } else { // date
                    valA = new Date(a.created_at || 0);
                    valB = new Date(b.created_at || 0);
                }
                return this.sortOrder === 'asc' ? valA - valB : valB - valA;
            });
            
            return result;
        },

        get filteredFolders() {
            let result = [...this.folders];
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(f => f.name.toLowerCase().includes(q));
            }
            return result;
        },

        async loadFiles() {
            const token = localStorage.getItem('token');
            const url = this.currentFolder ? `/api/files?folder_id=${this.currentFolder}` : '/api/files';
            const res = await fetch(url, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                this.files = data.files || [];
            }
        },

        async loadFolders() {
            const token = localStorage.getItem('token');
            const res = await fetch('/api/folders', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                this.folders = data.folders || [];
            }
            if (this.currentFolder) this.loadBreadcrumbs();
        },

        async loadBreadcrumbs() {
            const token = localStorage.getItem('token');
            const res = await fetch(`/api/folders/${this.currentFolder}/breadcrumbs`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                this.breadcrumbs = data.breadcrumbs || [];
            }
        },

        async loadTrash() {
            const token = localStorage.getItem('token');
            const res = await fetch('/api/files/trash', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                this.trashFiles = data.files || [];
            }
        },

        // Nav
        goHome() {
            this.currentFolder = null;
            this.breadcrumbs = [];
            this.loadFiles();
            this.loadFolders();
        },

        goToFolder(id) {
            this.currentFolder = id;
            this.loadFiles();
            this.loadBreadcrumbs();
        },

        goToSettings() {
            window.location.href = '/settings';
        },

        // Upload
        triggerUpload() {
            if (!this.hasTelegramConfig) {
                this.notify('Vui long cai dat Telegram truoc!', 'error');
                return;
            }
            document.getElementById('file-input').click();
        },

        async uploadFile(e) {
            const files = e.target.files;
            if (!files.length) return;
            const token = localStorage.getItem('token');

            for (const file of files) {
                if (file.size > 50 * 1024 * 1024) {
                    this.notify(`File "${file.name}" qua lon!`, 'error');
                    continue;
                }
                
                const uploadId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.uploadingFiles.push({
                    id: uploadId, name: file.name, size: file.size,
                    mimeType: file.type || 'application/octet-stream',
                    progress: 0, loaded: 0, total: file.size, status: 'uploading'
                });

                await this.uploadSingle(file, uploadId, token);
            }
            e.target.value = '';
        },

        async uploadSingle(file, uploadId, token) {
            const upload = this.uploadingFiles.find(u => u.id === uploadId);
            
            return new Promise((resolve) => {
                const fd = new FormData();
                fd.append('file', file);
                if (this.currentFolder) fd.append('folder_id', this.currentFolder);

                const xhr = new XMLHttpRequest();
                
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        upload.progress = Math.round((e.loaded / e.total) * 100);
                        upload.loaded = e.loaded;
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 201) {
                        const data = JSON.parse(xhr.responseText);
                        upload.status = 'done';
                        upload.progress = 100;
                        this.storageUsed += data.file.size;
                        this.files.unshift(data.file);
                        this.notify('Upload thanh cong!');
                        setTimeout(() => {
                            this.uploadingFiles = this.uploadingFiles.filter(u => u.id !== uploadId);
                        }, 3000);
                    } else {
                        upload.status = 'error';
                        try {
                            const data = JSON.parse(xhr.responseText);
                            this.notify(data.error || 'Upload that bai', 'error');
                        } catch {
                            this.notify('Upload that bai', 'error');
                        }
                        setTimeout(() => {
                            this.uploadingFiles = this.uploadingFiles.filter(u => u.id !== uploadId);
                        }, 5000);
                    }
                    resolve();
                };

                xhr.onerror = () => {
                    upload.status = 'error';
                    this.notify('Loi ket noi', 'error');
                    resolve();
                };

                xhr.open('POST', '/api/files/upload');
                xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                xhr.send(fd);
            });
        },

        downloadFile(id) {
            window.open(`/api/files/${id}/download?token=${localStorage.getItem('token')}`, '_blank');
        },

        async deleteFile(id) {
            if (!confirm('Xoa file?')) return;
            const token = localStorage.getItem('token');
            const res = await fetch(`/api/files/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                this.files = this.files.filter(f => f.id !== id);
                this.notify('Da xoa!');
            }
        },

        async restoreFile(id) {
            const token = localStorage.getItem('token');
            const res = await fetch(`/api/files/${id}/restore`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                this.trashFiles = this.trashFiles.filter(f => f.id !== id);
                await this.loadFiles();
                this.notify('Da khoi phuc!');
            }
        },

        async emptyTrash() {
            if (!confirm('Xoa tat ca thung rac?')) return;
            const token = localStorage.getItem('token');
            await fetch('/api/files/empty-trash', {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            this.trashFiles = [];
            this.notify('Da xoa thung rac!');
        },

        // Folder
        async createFolder() {
            if (!this.newFolderName.trim()) return;
            const token = localStorage.getItem('token');
            const body = { name: this.newFolderName };
            if (this.currentFolder) body.parent_id = this.currentFolder;
            
            const res = await fetch('/api/folders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify(body)
            });
            
            if (res.ok) {
                this.newFolderName = '';
                this.showNewFolder = false;
                await this.loadFolders();
                this.notify('Da tao thu muc!');
            }
        },

        async deleteFolder(id) {
            if (!confirm('Xoa thu muc?')) return;
            const token = localStorage.getItem('token');
            const res = await fetch(`/api/folders/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                this.folders = this.folders.filter(f => f.id !== id);
                this.notify('Da xoa thu muc!');
            }
        },

        // Share
        async shareFile(file) {
            this.shareFileData = file;
            this.shareLink = '';
            this.shareMessage = 'Dang tao link...';
            this.showShare = true;
            
            const token = localStorage.getItem('token');
            try {
                const res = await fetch('/api/share', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                    body: JSON.stringify({ file_id: file.id })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.shareLink = data.link;
                    this.shareMessage = '';
                } else {
                    this.shareMessage = 'Loi tao link';
                }
            } catch {
                this.shareMessage = 'Loi ket noi';
            }
        },

        copyLink() {
            if (!this.shareLink) {
                this.shareMessage = 'Chua co link!';
                return;
            }
            navigator.clipboard.writeText(this.shareLink).then(() => {
                this.shareMessage = 'Da copy link! Thanh cong!';
            }).catch(() => {
                // Fallback for older browsers
                const input = document.createElement('input');
                input.value = this.shareLink;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                this.shareMessage = 'Da copy link! Thanh cong!';
            });
        },

        // Utils
        formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        getFileIcon(mime) {
            if (!mime) return 'fa-file';
            if (mime.startsWith('image/')) return 'fa-image';
            if (mime.startsWith('video/')) return 'fa-video';
            if (mime.startsWith('audio/')) return 'fa-music';
            if (mime.includes('pdf')) return 'fa-file-pdf';
            if (mime.includes('word')) return 'fa-file-word';
            if (mime.includes('excel')) return 'fa-file-excel';
            if (mime.includes('zip')) return 'fa-file-archive';
            if (mime.startsWith('text/')) return 'fa-file-alt';
            return 'fa-file';
        },

        getFileBgColor(mime) {
            if (!mime) return 'bg-gray-100';
            if (mime.startsWith('image/')) return 'bg-purple-100';
            if (mime.startsWith('video/')) return 'bg-red-100';
            if (mime.startsWith('audio/')) return 'bg-orange-100';
            if (mime.includes('pdf')) return 'bg-red-100';
  if (mime.includes('word')) return 'bg-blue-100';
            if (mime.includes('excel')) return 'bg-green-100';
            return 'bg-gray-100';
        }
    };
}
</script>
@endsection