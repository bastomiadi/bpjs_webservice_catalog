<?php


/*
|--------------------------------------------------------------------------
| INITIAL SETUP
|--------------------------------------------------------------------------
*/

$basePath = __DIR__ . '/..';

// Initialize bootstrap
require_once $basePath . '/app/Bootstrap.php';
BPJSBootstrap::init($basePath);

// Get configuration
$credentials = BPJSBootstrap::getCredentials();
$apiConfig = BPJSBootstrap::getApiConfig();

$consId = $credentials['cons_id'];
$secretKey = $credentials['secret_key'];
$userKey = $credentials['user_key'];
$isDevMode = BPJSBootstrap::getIsDevMode();
$apiDomainVersion = $apiConfig['api_version'];
$currentDomain = BPJSBootstrap::getCurrentDomain();

// Load modules (variables must be in scope for module files)
$modules = BPJSBootstrap::loadModules();

// Module configuration
$moduleConfig = [
    'vclaim' => ['name' => 'VClaim', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/></svg>', 'color' => 'from-blue-500 to-blue-600'],
    'antrean_rs' => ['name' => 'Antrean RS', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>', 'color' => 'from-red-500 to-red-600'],
    'antrean_fktp' => ['name' => 'Antrean FKTP', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a8 8 0 11-3.536-3.536L8.343 7.657m8.484 8.484z"/></svg>', 'color' => 'from-green-500 to-green-600'],
    'apotek' => ['name' => 'Apotek', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2m14 0V9a2 2 0 00-2-2M7 13h5m-5-4h2m8 0h2"/></svg>', 'color' => 'from-purple-500 to-purple-600'],
    'pcare' => ['name' => 'PCare', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/></svg>', 'color' => 'from-orange-500 to-orange-600'],
    'icare' => ['name' => 'i-Care', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.317 4.317A4 4 0 016.414 3h7.172a4 4 0 012.096.317L19 6.414a2 2 0 01.586 1.414v7.172a2 2 0 01-.317 2.096l-2.097 2.097a4 4 0 01-2.096.317H6.414a4 4 0 01-2.096-.317L2 19l2.097-2.097z"/></svg>', 'color' => 'from-pink-500 to-pink-600'],
    'ws_rekam_medis' => ['name' => 'WS Rekam Medis', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m6 8V9m-6 8h6m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>', 'color' => 'from-teal-500 to-teal-600'],
    'aplicares' => ['name' => 'Aplicares', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>', 'color' => 'from-indigo-500 to-indigo-600'],
    'tools' => ['name' => 'Tools', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'color' => 'from-amber-500 to-orange-600'],
];

// Mask Cons ID for security
$maskedConsId = str_repeat('*', strlen($consId));

// Set page title
$pageTitle = 'BPJS Web Service Catalog';

// Include header
$showConsId = false;
$showModeSelector = false;
$showVersionSelector = false;
include __DIR__ . '/inc/header.php';
?>

    <!-- Main Content - Landing Page -->
    <main class="flex-1 p-6 bg-gray-50 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            <!-- Documentation Banner -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-primary-600 to-indigo-600 text-white rounded-2xl p-6 shadow-md">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight">Katalog & Dokumentasi BPJS Web Service</h2>
                    <p class="text-primary-100 text-sm mt-1">Akses katalog interaktif testing API atau baca dokumentasi lengkap Markdown dari setiap modul BPJS Kesehatan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="docs.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-primary-700 font-bold text-sm shadow hover:bg-primary-50 transition-all flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Buka Dokumentasi </span>
                    </a>
                </div>
            </div>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php 
                $docMap = [
                    'vclaim' => 'vclaim',
                    'antrean_rs' => 'antreanrs',
                    'antrean_fktp' => 'antreanfktp',
                    'apotek' => 'apotek',
                    'pcare' => 'pcare',
                    'icare' => 'icare',
                    'ws_rekam_medis' => 'rekammedis',
                    'aplicares' => 'aplicares',
                    'tools' => 'batalkodebooking'
                ];
                foreach ($modules as $key => $module): 
                    $config = $moduleConfig[$key] ?? ['name' => $key, 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/></svg>', 'color' => 'from-slate-500 to-slate-600'];
                    $endpointCount = count($module['sub_modules'] ?? []);
                    $docKey = $docMap[$key] ?? $key;
                ?>
                <div class="group bg-white border border-gray-200 rounded-xl p-5 transition-all duration-200 card-hover shadow-sm hover:shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-r <?= $config['color'] ?> flex items-center justify-center text-xl shadow-md text-white"><?= $config['icon'] ?></div>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium"><?= $endpointCount ?> endpoints</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors"><?= htmlspecialchars($config['name']) ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($module['description'] ?? 'BPJS API Module') ?></p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-semibold">
                        <a href="catalog.php?module=<?= $key ?>" class="text-primary-600 hover:text-primary-700">Test API →</a>
                        <a href="docs.php?doc=<?= $docKey ?>" class="text-gray-500 hover:text-primary-600 flex items-center gap-1">
                            <span>Docs </span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

</body>
</html>
