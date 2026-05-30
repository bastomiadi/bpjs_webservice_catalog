<?php
/**
 * Dynamic Header Component
 * Usage: include 'inc/header.php';
 * 
 * Variables required:
 * - $consId: BPJS Consumer ID
 * - $isDevMode: boolean for DEV/PROD mode
 * - $apiDomainVersion: 'v1' or 'v2'
 * - $pageTitle: page title (optional, defaults to 'BPJS Web Service Catalog')
 * - $showConsId: whether to show Cons ID section (default: true)
 * - $showModeSelector: whether to show mode selector (default: true)
 * - $showVersionSelector: whether to show API version selector (default: true)
 * - $showThemeToggle: whether to show theme toggle (default: true)
 */

$pageTitle = $pageTitle ?? 'BPJS Web Service Catalog';
$showConsId = $showConsId ?? true;
$showModeSelector = $showModeSelector ?? true;
$showVersionSelector = $showVersionSelector ?? true;
$showThemeToggle = $showThemeToggle ?? true;

// Mask Cons ID for security
$maskedConsId = $showConsId ? str_repeat('*', strlen($consId)) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bpjs: {
                            50:  '#e6f0ff',
                            100: '#b3c9ff',
                            200: '#80a2ff',
                            300: '#4d7bff',
                            400: '#1a5cff',
                            500: '#0047e6',
                            600: '#0039b3',
                            700: '#002b80',
                            800: '#001d4d',
                            900: '#000f1a',
                        }
                    }
                }
            }
        }

        // Switch API Version (V1 or V2)
        function switchApiVersion(version) {
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = "bpjs_api_version=" + version + ";" + expires + ";path=/";
            window.location.reload();
        }

        // Switch Mode (DEV/PRODUCTION)
        function switchMode(isDev) {
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            document.cookie = "bpjs_dev_mode=" + (isDev ? 'true' : 'false') + ";" + expires + ";path=/";
            window.location.reload();
        }

        // Copy format to clipboard
        function copyFormat(button) {
            const pre = button.closest('.bg-slate-800\\/60').querySelector('code');
            const text = pre.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            });
        }

        // Theme toggle
        function toggleTheme() {
            const isDark = document.body.classList.contains('dark-theme');
            const newTheme = isDark ? 'light' : 'dark';
            const d = new Date();
            d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
            document.cookie = "bpjs_theme=" + newTheme + ";expires=" + d.toUTCString() + ";path=/";
            applyTheme(newTheme);
        }

        function applyTheme(theme) {
            const body = document.getElementById('body');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (theme === 'light') {
                body.classList.remove('dark-theme');
                body.classList.add('light-theme');
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Light';
            } else {
                body.classList.remove('light-theme');
                body.classList.add('dark-theme');
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Dark';
            }
        }

        // Check for saved theme preference
        function getSavedTheme() {
            const cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i].trim();
                var parts = cookie.split('=');
                if (parts[0] === 'bpjs_theme') {
                    return parts[1];
                }
            }
            return 'dark';
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyTheme(getSavedTheme());
        });
    </script>
    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        .light-theme .sidebar-scroll::-webkit-scrollbar-track { background: #e2e8f0; }
        .light-theme .sidebar-scroll::-webkit-scrollbar-thumb { background: #94a3b8; }
        .method-get { color: #22c55e; }
        .method-post { color: #3b82f6; }
        .method-put { color: #f59e0b; }
        .method-delete { color: #ef4444; }
        .method-patch { color: #a855f7; }
        .badge-get { background: #dcfce7; color: #166534; }
        .badge-post { background: #dbeafe; color: #1e40af; }
        .badge-put { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }
        .badge-patch { background: #f3e8ff; color: #6b21a8; }
        pre { white-space: pre-wrap; word-break: break-all; }
        
        /* Light theme styles */
        .light-theme { background-color: #f1f5f9 !important; color: #1e293b !important; }
        .light-theme .bg-slate-900 { background-color: #f8fafc !important; }
        .light-theme .bg-slate-800 { background-color: #f1f5f9 !important; }
        .light-theme .bg-slate-700 { background-color: #e2e8f0 !important; }
        .light-theme .text-slate-100 { color: #1e293b !important; }
        .light-theme .text-slate-200 { color: #1e293b !important; }
        .light-theme .text-slate-300 { color: #334155 !important; }
        .light-theme .text-slate-400 { color: #64748b !important; }
        .light-theme .text-slate-500 { color: #64748b !important; }
        .light-theme .text-slate-600 { color: #475569 !important; }
        .light-theme .border-slate-700 { border-color: #e2e8f0 !important; }
        .light-theme .border-slate-600 { border-color: #cbd5e1 !important; }
        .light-theme .bg-bpjs-700 { background-color: #ffffff !important; }
        .light-theme .text-bpjs-100 { color: #1e40af !important; }
        .light-theme .text-bpjs-200 { color: #1d4ed8 !important; }
        .light-theme .text-bpjs-300 { color: #2563eb !important; }
        .light-theme .bg-bpjs-600 { background-color: #dc2626 !important; }
        .light-theme .text-white { color: #1e293b !important; }
        .light-theme .text-green-400 { color: #16a34a !important; }
        .light-theme .bg-slate-800-60 { background-color: #f1f5f999 !important; }
        .light-theme .text-amber-400 { color: #d97706 !important; }
        .light-theme .bg-white-20 { background-color: #ffffff !important; }
        .light-theme .text-bpjs-500-30 { color: #0047e6 !important; }
        .light-theme .bg-bpjs-500-30 { background-color: #0047e6 !important; }
        .light-theme .border-bpjs-400-50 { border-color: #0047e6 !important; }
        .light-theme .border-bpjs-400-40 { border-color: #0047e6 !important; }
        .light-theme .bg-bpjs-600-40 { background-color: #dc2626 !important; }
        .light-theme .shadow-bpjs-900-30 { box-shadow: 0 10px 15px -3px #000f1a4d, 0 4px 6px -2px #000f1a1a !important; }
        .light-theme .shadow-bpjs-900-40 { box-shadow: 0 10px 15px -3px #000f1a66, 0 4px 6px -2px #000f1a20 !important; }
        .light-theme .bg-slate-800-50 { background-color: #f1f5f980 !important; }
        .light-theme .bg-slate-700-60 { background-color: #e2e8f099 !important; }
        .light-theme .bg-slate-700-30 { background-color: #e2e8f04d !important; }
        .light-theme .bg-slate-700-50 { background-color: #e2e8f080 !important; }
        .light-theme .bg-red-900-30 { background-color: #fef2f2 !important; }
        .light-theme .border-red-700 { border-color: #fecaca !important; }
        .light-theme .text-red-400 { color: #dc2626 !important; }
        .light-theme .text-red-300 { color: #dc2626 !important; }
        .light-theme .bg-green-900-40 { background-color: #f0fdf4 !important; }
        .light-theme .border-green-700 { border-color: #bbf7d0 !important; }
        .light-theme .text-green-400 { color: #16a34a !important; }
        .light-theme .bg-yellow-900-40 { background-color: #fefce8 !important; }
        .light-theme .border-yellow-700 { border-color: #fef08a !important; }
        .light-theme .text-yellow-400 { color: #ca8a04 !important; }
        .light-theme input, .light-theme select, .light-theme textarea {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }
        .light-theme .placeholder-slate-500::placeholder { color: #94a3b8 !important; }
        .light-theme .hover-bg-slate-700:hover { background-color: #e2e8f0 !important; }
        .light-theme .hover-text-slate-200:hover { color: #1e293b !important; }
        .light-theme .hover-text-bpjs-200:hover { color: #80a2ff !important; }
        .light-theme .hover-text-bpjs-100:hover { color: #b3c9ff !important; }
        .light-theme .hover-bg-slate-700-60:hover { background-color: #e2e8f099 !important; }
        .light-theme .hover-border-slate-600:hover { border-color: #cbd5e1 !important; }
        .light-theme pre { color: #334155 !important; }
        .light-theme code { color: #334155 !important; }
        /* Header in light mode */
        .light-theme header {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        .light-theme header.bg-bpjs-700 {
            background-color: #ffffff !important;
        }
        .light-theme header .bg-bpjs-700 {
            background-color: #ffffff !important;
        }
        .light-theme header.border-bpjs-500 {
            border-color: #e2e8f0 !important;
        }
        .light-theme header .border-bpjs-500 {
            border-color: #e2e8f0 !important;
        }
        .light-theme header .bg-white {
            background-color: #f8fafc !important;
        }
        .light-theme header .text-bpjs-700 {
            color: #1e40af !important;
        }
        .light-theme header .bg-white.rounded-lg {
            background-color: #f1f5f9 !important;
        }
        .light-theme header .text-bpjs-700.font-bold {
            color: #1e40af !important;
        }
        .light-theme header .text-bpjs-100 {
            color: #1e40af !important;
        }
        .light-theme header .text-white {
            color: #1e293b !important;
        }
        .light-theme header .text-slate-400 {
            color: #475569 !important;
        }
        .light-theme header .text-green-400 {
            color: #16a34a !important;
        }
        .light-theme header h1.text-white.font-bold.text-lg.leading-tight {
            color: #1e293b !important;
        }
        .light-theme header p.text-xs.text-bpjs-100 {
            color: #1e40af !important;
        }
        .light-theme header .text-right.p-2 .text-xs.text-bpjs-100,
        .light-theme header .text-right .text-xs.text-bpjs-100 {
            color: #1e40af !important;
        }
        .light-theme header .text-white.text-sm.font-mono.font-semibold {
            color: #1e293b !important;
        }
        .light-theme #themeToggle {
            color: #475569 !important;
        }
        .light-theme #themeToggle:hover {
            color: #1e293b !important;
        }
        .light-theme header select {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        .light-theme header select option {
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        /* Header dividers */
        .light-theme header .w-px.h-8.bg-bpjs-500 {
            background-color: #e2e8f0 !important;
        }
        /* Module header in light mode */
        .light-theme .bg-gradient-to-r.from-bpjs-700.to-bpjs-600 {
            background: linear-gradient(to right, #ffffff, #f8fafc) !important;
            border-color: #e2e8f0 !important;
        }
        .light-theme .bg-gradient-to-r.from-bpjs-700.to-bpjs-600 .text-white {
            color: #1e293b !important;
        }
        .light-theme .bg-gradient-to-r.from-bpjs-700.to-bpjs-600 .text-bpjs-100 {
            color: #1e40af !important;
        }
        .light-theme .bg-gradient-to-r.from-bpjs-700.to-bpjs-600 .text-bpjs-200 {
            color: #2563eb !important;
        }
        .light-theme .bg-gradient-to-r.from-bpjs-700.to-bpjs-600 .bg-white-20 {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        /* Submit button in light mode */
        .light-theme .bg-gradient-to-r.from-bpjs-600.to-bpjs-500 {
            background: linear-gradient(to right, #dc2626, #ef4444) !important;
        }
        .light-theme .bg-gradient-to-r.from-bpjs-600.to-bpjs-500:hover {
            background: linear-gradient(to right, #ef4444, #f85a5a) !important;
        }
        /* Sidebar in light mode */
        .light-theme aside {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .light-theme .bg-slate-800 {
            background-color: #f1f5f9 !important;
        }
        .light-theme .border-slate-700 {
            border-color: #e2e8f0 !important;
        }
        .light-theme .text-slate-300 {
            color: #475569 !important;
        }
        .light-theme .text-slate-400 {
            color: #64748b !important;
        }
        .light-theme .text-slate-500 {
            color: #94a3b8 !important;
        }
        .light-theme .bg-slate-700 {
            background-color: #e2e8f0 !important;
        }
        .light-theme .bg-slate-900 {
            background-color: #f8fafc !important;
        }
        .light-theme .bg-slate-600 {
            background-color: #cbd5e1 !important;
        }
        .light-theme .hover-bg-slate-700:hover {
            background-color: #e2e8f0 !important;
        }
        .light-theme .hover-text-slate-200:hover {
            color: #1e293b !important;
        }
        /* Sidebar search input in light mode */
        .light-theme input#sidebarSearch {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
            placeholder-color: #94a3b8 !important;
        }
        /* Sidebar footer in light mode */
        .light-theme .text-slate-500.text-center {
            color: #64748b !important;
        }
        /* Main content in light mode */
        .light-theme main {
            background-color: #f8fafc !important;
        }
        /* Endpoint list header in light mode */
        .light-theme h3.text-xs.font-bold.text-slate-500.uppercase.tracking-wider.mb-3 {
            color: #64748b !important;
        }
        /* Endpoint item in light mode */
        .light-theme .text-slate-400.hover-bg-slate-700-60:hover {
            color: #475569 !important;
            background-color: #e2e8f0 !important;
        }
        /* Module count badge in light mode */
        .light-theme .text-xs.bg-slate-700.text-slate-400 {
            background-color: #e2e8f0 !important;
            color: #64748b !important;
        }
        /* Endpoints count badge in light mode */
        .light-theme .bg-white-20.text-white.text-xs.px-3.py-1.rounded-full {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        /* Form container in light mode */
        .light-theme .bg-slate-800-60.border-slate-700.rounded-xl {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }
        /* Form section header in light mode */
        .light-theme .text-sm.font-bold.text-slate-300 {
            color: #475569 !important;
        }
        .light-theme .text-sm.font-bold.text-slate-300 span {
            color: #475569 !important;
        }
        /* Form labels in light mode */
        .light-theme .block.text-xs.text-slate-400.mb-1.font-mono {
            color: #475569 !important;
        }
        /* Form inputs in light mode */
        .light-theme .bg-slate-900.border-slate-600.rounded-lg.px-3.py-2.text-sm.text-slate-200 {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        .light-theme .bg-slate-900.border-slate-600.rounded-lg.px-3.py-2.text-sm.text-slate-200::placeholder {
            color: #94a3b8 !important;
        }
        /* Form body container in light mode */
        .light-theme .bg-slate-800-30.border-slate-700.rounded-xl.p-4 {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .light-theme .text-xs.text-slate-300 {
            color: #475569 !important;
        }
        /* Debug info section in light mode */
        .light-theme .text-amber-400.mb-3 {
            color: #ca8a04 !important;
        }
        .light-theme .text-xs.text-slate-400 {
            color: #64748b !important;
        }
        /* Request body container in light mode */
        .light-theme .bg-slate-800-50.border-slate-700.rounded-xl.p-4 {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }
        /* Response section in light mode */
        .light-theme .text-xs.text-slate-400.mt-3 {
            color: #64748b !important;
        }
        /* Landing page module grid in light mode */
        .light-theme .text-lg.font-bold.text-white.group-hover\:text-bpjs-200 {
            color: #1e293b !important;
        }
        .light-theme .text-sm.text-slate-400.mt-1 {
            color: #475569 !important;
        }
        .light-theme .text-xs.text-bpjs-300.group-hover\:text-bpjs-200.font-medium {
            color: #1e40af !important;
        }
        .light-theme .bg-slate-800\/60.hover\:bg-slate-700\/60 {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }
        .light-theme .border-slate-700.hover\:border-bpjs-400\/50 {
            border-color: #e2e8f0 !important;
        }
        /* Module card icon in light mode */
        .light-theme .text-xl.text-white.group-hover\:text-bpjs-200 {
            color: #1e293b !important;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 h-screen overflow-hidden flex flex-col transition-colors duration-300 dark-theme" id="body">

    <!-- ===== TOP HEADER ===== -->
    <header class="bg-bpjs-700 border-b border-bpjs-500 px-6 py-3 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center text-bpjs-700 font-bold text-lg">B</div>
            <div>
                <h1 class="text-white font-bold text-lg leading-tight"><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="text-bpjs-100 text-xs">API Documentation & Testing Tool</p>
            </div>
        </div>
        <?php if ($showConsId || $showModeSelector || $showVersionSelector || $showThemeToggle): ?>
        <div class="flex items-center gap-4">
            <?php if ($showConsId): ?>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Cons ID</p>
                <p class="text-white text-sm font-mono font-semibold"><?= htmlspecialchars($maskedConsId) ?></p>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <?php endif; ?>
            <?php if ($showModeSelector): ?>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Mode</p>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" onchange="switchMode(this.checked)" <?= $isDevMode ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-bpjs-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-bpjs-500"></div>
                    <span class="ml-2 text-sm font-semibold <?= $isDevMode ? 'text-orange-400' : 'text-slate-400' ?>"><?= $isDevMode ? 'DEV' : 'PROD' ?></span>
                </label>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <?php endif; ?>
            <?php if ($showVersionSelector): ?>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">API Version</p>
                <select onchange="switchApiVersion(this.value)" class="text-white text-sm font-semibold bg-bpjs-600 border border-bpjs-500 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-bpjs-400 <?= $isDevMode ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $isDevMode ? 'disabled' : '' ?>>
                    <option value="v1" <?= $apiDomainVersion === 'v1' ? 'selected' : '' ?> class="bg-slate-900">V1 (apijkn.bpjs-kesehatan.go.id)</option>
                    <option value="v2" <?= $apiDomainVersion === 'v2' ? 'selected' : '' ?> class="bg-slate-900">V2 (new-apijkn.bpjs-kesehatan.go.id)</option>
                </select>
            </div>
            <div class="w-px h-8 bg-bpjs-500"></div>
            <?php endif; ?>
            <?php if ($showThemeToggle): ?>
            <div class="text-right">
                <p class="text-xs text-bpjs-100">Theme</p>
                <button onclick="toggleTheme()" id="themeToggle" class="flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText">Dark</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </header>