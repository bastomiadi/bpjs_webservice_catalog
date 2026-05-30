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
 */

$pageTitle = $pageTitle ?? 'BPJS Web Service Catalog';
$showConsId = $showConsId ?? true;
$showModeSelector = $showModeSelector ?? true;
$showVersionSelector = $showVersionSelector ?? true;

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
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        bpjs: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        gray: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        'lg': '0.75rem',
                        'xl': '1rem',
                        '2xl': '1.5rem',
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
            const pre = button.closest('.bg-gray-50').querySelector('code');
            const text = pre.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
            });
        }
    </script>
    <style>
        /* Method badges */
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
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        
        /* Sidebar transition */
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col transition-colors duration-300" id="body">

    <!-- ===== TOP HEADER ===== -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-primary-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-xl leading-tight text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="text-sm text-gray-500">API Documentation & Testing Tool</p>
            </div>
        </div>
        <?php if ($showConsId || $showModeSelector || $showVersionSelector): ?>
        <div class="flex items-center gap-4">
            <?php if ($showConsId): ?>
            <div class="text-right">
                <p class="text-sm text-gray-500">Cons ID</p>
                <p class="text-base font-mono font-semibold text-gray-900"><?= htmlspecialchars($maskedConsId) ?></p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <?php endif; ?>
            <?php if ($showModeSelector): ?>
            <div class="text-right">
                <p class="text-sm text-gray-500">Mode</p>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" onchange="switchMode(this.checked)" <?= $isDevMode ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-12 h-7 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-500"></div>
                    <span class="ml-2.5 text-base font-semibold <?= $isDevMode ? 'text-orange-500' : 'text-gray-400' ?>"><?= $isDevMode ? 'DEV' : 'PROD' ?></span>
                </label>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <?php endif; ?>
            <?php if ($showVersionSelector): ?>
            <div class="text-right">
                <p class="text-sm text-gray-500">API Version</p>
                <select onchange="switchApiVersion(this.value)" class="text-base font-semibold bg-white text-gray-900 border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-400 <?= $isDevMode ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $isDevMode ? 'disabled' : '' ?>>
                    <option value="v1" <?= $apiDomainVersion === 'v1' ? 'selected' : '' ?>>V1</option>
                    <option value="v2" <?= $apiDomainVersion === 'v2' ? 'selected' : '' ?>>V2</option>
                </select>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </header>