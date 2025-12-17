<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($user_level)) {
    $user_level = $_SESSION['level'] ?? '';
    $user_nama = $_SESSION['nama'] ?? '';
    $user_email = $_SESSION['email'] ?? '';
}
?>

<div id="offcanvasOverlay" class="offcanvas-overlay" onclick="closeOffcanvas()"></div>

<div id="offcanvasMenu" class="offcanvas">
    <div class="border-b border-gray-200 px-6 py-6">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Menu</h3>
            <button onclick="closeOffcanvas()" class="text-gray-400 hover:text-gray-500 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="mt-4 flex items-center">
            <div class="bg-blue-100 p-2 rounded-full">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user_nama); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user_email); ?></p>
            </div>
        </div>
    </div>
    
    <div class="flex-1 px-6 py-4 overflow-y-auto">
        <div class="space-y-1">
            <a href="dashboard.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <?php if ($user_level == 'admin'): ?>
                <a href="kelola_user.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'kelola_user.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'kelola_user.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    <span>Kelola User</span>
                </a>
            <?php endif; ?>
            
            <?php if ($user_level == 'petugas' || $user_level == 'admin'): ?>
                <a href="laporan.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                    </svg>
                    <span>Laporan</span>
                </a>
            <?php endif; ?>
            
            <?php if ($user_level == 'admin'): ?>
                <a href="logs.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'logs.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'logs.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Activity Logs</span>
                </a>
            <?php endif; ?>
            
            <?php if ($user_level == 'masyarakat'): ?>
                <a href="pengaduan_baru.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'pengaduan_baru.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'pengaduan_baru.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Ajukan Pengaduan</span>
                </a>
                
                <a href="pengaduan_saya.php" class="flex items-center px-4 py-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'pengaduan_saya.php') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3 <?php echo (basename($_SERVER['PHP_SELF']) == 'pengaduan_saya.php') ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Pengaduan Saya</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="border-t border-gray-200 px-6 py-4">
        <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="font-medium">Keluar</span>
        </a>
    </div>
</div>

<nav class="bg-white border-b border-gray-200 py-5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 8.25h9m-9 3h5.25M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25-9 3.694-9 8.25c0 2.09.82 3.995 2.18 5.433L3 20.25l3.07-.82A10.13 10.13 0 0 0 12 20.25Z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-xl font-bold text-gray-900">SuaraWarga</h1>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars(ucfirst($user_level)); ?> Dashboard</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <button onclick="openOffcanvas()" 
                       class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>