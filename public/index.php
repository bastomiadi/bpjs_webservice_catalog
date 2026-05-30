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
    'vclaim' => ['name' => 'VClaim', 'icon' => '📋', 'color' => 'from-blue-500 to-blue-600'],
    'antrean_rs' => ['name' => 'Antrean RS', 'icon' => '🩺', 'color' => 'from-red-500 to-red-600'],
    'antrean_fktp' => ['name' => 'Antrean FKTP', 'icon' => '🏥', 'color' => 'from-green-500 to-green-600'],
    'apotek' => ['name' => 'Apotek', 'icon' => '💊', 'color' => 'from-purple-500 to-purple-600'],
    'pcare' => ['name' => 'PCare', 'icon' => '🏪', 'color' => 'from-orange-500 to-orange-600'],
    'icare' => ['name' => 'i-Care', 'icon' => '💙', 'color' => 'from-pink-500 to-pink-600'],
    'ws_rekam_medis' => ['name' => 'WS Rekam Medis', 'icon' => '📁', 'color' => 'from-teal-500 to-teal-600'],
    'aplicares' => ['name' => 'Aplicares', 'icon' => '🏥', 'color' => 'from-indigo-500 to-indigo-600'],
];

// Set page title
$pageTitle = 'BPJS Web Service Catalog';

// Include header (without Cons ID, Mode selector, Version selector, Theme toggle for landing page)
$showConsId = false;
$showModeSelector = false;
$showVersionSelector = false;
$showThemeToggle = true;
include __DIR__ . '/inc/header.php';
?>

    <!-- Main Content - Landing Page -->
    <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Modules Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach ($modules as $key => $module): 
                    $config = $moduleConfig[$key] ?? ['name' => $key, 'icon' => '📁', 'color' => 'from-slate-500 to-slate-600'];
                    $endpointCount = count($module['sub_modules'] ?? []);
                ?>
                <a href="catalog.php?module=<?= $key ?>" class="group bg-slate-800/60 hover:bg-slate-700/60 border border-slate-700 hover:border-bpjs-400/50 rounded-xl p-4 transition-all duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-r <?= $config['color'] ?> flex items-center justify-center text-xl"><?= $config['icon'] ?></div>
                        <span class="text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded-full"><?= $endpointCount ?> endpoints</span>
                    </div>
                    <h3 class="text-lg font-bold text-white group-hover:text-bpjs-200 transition-colors"><?= htmlspecialchars($config['name']) ?></h3>
                    <p class="text-sm text-slate-400 mt-1"><?= htmlspecialchars($module['description'] ?? 'BPJS API Module') ?></p>
                    <div class="mt-3 pt-3 border-t border-slate-700">
                        <span class="text-xs text-bpjs-300 group-hover:text-bpjs-200 font-medium">Pilih Modul →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

</body>
</html>