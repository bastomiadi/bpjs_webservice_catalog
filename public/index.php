<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <main class="flex-1 p-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <!-- Modules Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach ($modules as $key => $module): 
                    $config = $moduleConfig[$key] ?? ['name' => $key, 'icon' => '📁', 'color' => 'from-slate-500 to-slate-600'];
                    $endpointCount = count($module['sub_modules'] ?? []);
                ?>
                <a href="catalog.php?module=<?= $key ?>" class="group bg-white border border-gray-200 rounded-xl p-5 transition-all duration-200 card-hover shadow-sm hover:shadow-md">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-r <?= $config['color'] ?> flex items-center justify-center text-xl shadow-md text-white"><?= $config['icon'] ?></div>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium"><?= $endpointCount ?> endpoints</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors"><?= htmlspecialchars($config['name']) ?></h3>
                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($module['description'] ?? 'BPJS API Module') ?></p>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-primary-600 group-hover:text-primary-700 font-medium">Pilih Modul →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

</body>
</html>