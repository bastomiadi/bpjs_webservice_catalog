<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| INITIAL SETUP
|--------------------------------------------------------------------------
*/

$basePath = __DIR__ . '/..';

// Load core files
require_once $basePath . '/config/env.php';
require_once $basePath . '/helpers/bpjs_signature.php';
require_once $basePath . '/helpers/bpjs_request.php';
require_once $basePath . '/helpers/bpjs_decrypt.php';
loadEnv($basePath . '/.env');

// Load library
foreach (glob($basePath . '/library/lz-string/src/LZCompressor/*.php') as $file) {
    require_once $file;
}

// Credentials
$consId    = $_ENV['BPJS_CONS_ID'] ?? '';
$secretKey = $_ENV['BPJS_SECRET_KEY'] ?? '';
$userKey   = $_ENV['BPJS_USER_KEY'] ?? '';

// API Domain Configuration
$isDevMode        = isset($_GET['dev_mode']) ? ($_GET['dev_mode'] === 'true') : (($_COOKIE['bpjs_dev_mode'] ?? 'false') === 'true');
$apiDomainVersion = isset($_GET['api_version']) ? $_GET['api_version'] : (($_COOKIE['bpjs_api_version'] ?? 'v1'));

$prodDomainMap = ['v1' => 'apijkn.bpjs-kesehatan.go.id', 'v2' => 'new-apijkn.bpjs-kesehatan.go.id'];
$currentDomain = $isDevMode ? 'apijkn-dev.bpjs-kesehatan.go.id' : ($prodDomainMap[$apiDomainVersion] ?? $prodDomainMap['v1']);

/*
|--------------------------------------------------------------------------
| HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/

function getBaseUrl($moduleKey, $currentDomain, $isDevMode) {
    $prodPaths = [
        'vclaim' => '/vclaim-rest', 'antrean_rs' => '/antreanrs', 'antrean_fktp' => '/antreanfktp',
        'apotek' => '/apotek-rest', 'pcare' => '/pcare-rest', 'icare' => '/wsihs',
        'ws_rekam_medis' => '/erekammedis', 'aplicares' => '/aplicaresws/rest',
    ];

    $devDomains = [
        'vclaim' => 'apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev',
        'antrean_rs' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev',
        'antrean_fktp' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanfktp_dev',
        'apotek' => 'apijkn-dev.bpjs-kesehatan.go.id/apotek-rest-dev',
        'pcare' => 'apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev',
        'icare' => 'apijkn-dev.bpjs-kesehatan.go.id/ihs_dev',
        'ws_rekam_medis' => 'apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev',
    ];

    if ($moduleKey === 'aplicares') {
        return $isDevMode
            ? 'https://apijkn.bpjs-kesehatan.go.id/aplicaresws/rest'
            : 'https://' . $currentDomain . ($prodPaths[$moduleKey] ?? '');
    }

    return $isDevMode && isset($devDomains[$moduleKey])
        ? 'https://' . $devDomains[$moduleKey]
        : 'https://' . $currentDomain . ($prodPaths[$moduleKey] ?? '');
}

/*
|--------------------------------------------------------------------------
| LOAD MODULES
|--------------------------------------------------------------------------
*/

$modules = [];
foreach (glob($basePath . '/app/modules/*.php') as $file) {
    $moduleKey = basename($file, '.php');
    $moduleData = require_once $file;
    is_array($moduleData) && $modules[$moduleKey] = $moduleData;
}

/*
|--------------------------------------------------------------------------
| MODULE CONFIGURATION
|--------------------------------------------------------------------------
| Define module metadata for landing page
|--------------------------------------------------------------------------
*/

$moduleConfig = [
    'vclaim' => ['name' => 'VClaim', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/></svg>', 'color' => 'from-blue-500 to-blue-600'],
    'antrean_rs' => ['name' => 'Antrean RS', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>', 'color' => 'from-red-500 to-red-600'],
    'antrean_fktp' => ['name' => 'Antrean FKTP', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a8 8 0 11-3.536-3.536L8.343 7.657m8.484 8.484z"/></svg>', 'color' => 'from-green-500 to-green-600'],
    'apotek' => ['name' => 'Apotek', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2m14 0V9a2 2 0 00-2-2M7 13h5m-5-4h2m8 0h2"/></svg>', 'color' => 'from-purple-500 to-purple-600'],
    'pcare' => ['name' => 'PCare', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/></svg>', 'color' => 'from-orange-500 to-orange-600'],
    'icare' => ['name' => 'i-Care', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.317 4.317A4 4 0 016.414 3h7.172a4 4 0 012.096.317L19 6.414a2 2 0 01.586 1.414v7.172a2 2 0 01-.317 2.096l-2.097 2.097a4 4 0 01-2.096.317H6.414a4 4 0 01-2.096-.317L2 19l2.097-2.097z"/></svg>', 'color' => 'from-pink-500 to-pink-600'],
    'ws_rekam_medis' => ['name' => 'WS Rekam Medis', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4V9m-6-3a3 3 0 00-3 3v12a3 3 0 003 3h8a3 3 0 003-3V9a3 3 0 00-3-3h-2m-5 16H7a4 4 0 01-4-4V5a4 4 0 014-4h5a4 4 0 014 4v12a4 4 0 01-4 4z"/></svg>', 'color' => 'from-teal-500 to-teal-600'],
    'aplicares' => ['name' => 'Aplicares', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>', 'color' => 'from-indigo-500 to-indigo-600'],
];

// Set page title
$pageTitle = 'BPJS Web Service Catalog';

// Include header (without Cons ID, Mode selector, Version selector for landing page)
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