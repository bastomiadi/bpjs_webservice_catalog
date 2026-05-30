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

/*
|--------------------------------------------------------------------------
| HANDLE API REQUEST
|--------------------------------------------------------------------------
*/

$apiResponse  = null;
$apiError     = '';
$selectedSub  = null;
$selectedMod  = null;
$debugInfo    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_request') {
    $debugInfo['POST'] = $_POST;

    $moduleKey  = $_POST['module_key']  ?? '';
    $subKey     = $_POST['sub_key']     ?? '';
    $method     = strtoupper($_POST['method'] ?? 'GET');
    $path       = $_POST['path']        ?? '';
    $baseUrl    = $_POST['base_url']    ?? '';
    $rawParams  = $_POST['params']      ?? '{}';
    $rawBody    = $_POST['body']        ?? '{}';
    $decrypt    = isset($_POST['decrypt']) && $_POST['decrypt'] === '1';

    $params = is_array($rawParams) ? $rawParams : (json_decode($rawParams, true) ?: []);
    $body   = is_array($rawBody)   ? $rawBody   : (json_decode($rawBody,   true) ?: []);

    /**
     * Transform serializeArray() format
     * [
     *   ['name'=>'param','value'=>'xxx']
     * ]
     * menjadi:
     * [
     *   'param'=>'xxx'
     * ]
     */
    if ($moduleKey === 'icare' && $subKey === 'fkrtl_validate') {

        $formattedBody = [];

        foreach ($body as $item) {

            if (!isset($item['name'])) {
                continue;
            }

            $name  = $item['name'];
            $value = $item['value'] ?? '';

            // khusus kodedokter harus integer
            if ($name === 'kodedokter') {
                $formattedBody[$name] = (int)$value;
            } else {
                $formattedBody[$name] = trim($value);
            }
        }

        // khusus param -> numeric only
        if (isset($formattedBody['param'])) {
            $formattedBody['param'] = preg_replace('/\D/', '', $formattedBody['param']);
        }

        $body = $formattedBody;
    }

    // ── Validate required path params before building URL ──
    $missingParams = [];
    foreach ($params as $p) {
        if (strpos($path, '{' . $p['name'] . '}') !== false && empty(trim($p['value'] ?? ''))) {
            $missingParams[] = $p['name'];
        }
    }
    if (!empty($missingParams)) {
        $apiError  = 'Parameter wajib belum diisi: ' . implode(', ', $missingParams);
        $apiResponse = null;
        $debugInfo['missing_params'] = $missingParams;
    } else {
        $debugInfo['params'] = $params;

        // Build URL with path params
        $url = rtrim($baseUrl, '/') . $path;
        foreach ($params as $p) {
            $url = str_replace('{' . $p['name'] . '}', urlencode($p['value'] ?? ''), $url);
        }

        // Build query string from remaining (non-path) params
        $queryParams = [];
        foreach ($params as $p) {
            if (strpos($path, '{' . $p['name'] . '}') === false && !empty(trim($p['value'] ?? ''))) {
                $queryParams[$p['name']] = $p['value'];
            }
        }
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $debugInfo['url'] = $url;
        $debugInfo['method'] = $method;

        $auth = BPSignature::generate($consId, $secretKey);

        $requestConfig = [
            'url'       => $url,
            'method'    => $method,
            'cons_id'   => $consId,
            'timestamp' => $auth['timestamp'],
            'signature' => $auth['signature'],
            'user_key'  => $userKey,
        ];

        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $requestConfig['body'] = $body;
        }

        $response = BPJSRequest::send($requestConfig);
        $debugInfo['bpjs_response'] = $response;

        if (!$response['status']) {
            $apiError  = $response['message'] ?? 'Unknown error';
            $apiResponse = null;
        } else {
            $apiResponse = $response['data'];
            
            // Check if the response is HTML (error page)
            $rawResponse = $response['raw_response'] ?? null;
            if ($rawResponse && (strpos($rawResponse, '<html>') !== false || strpos($rawResponse, '<!DOCTYPE') !== false)) {
                $apiError = 'API returned HTML error. Possible causes: wrong endpoint URL, invalid credentials, or API not available. Response: ' . htmlspecialchars(substr($rawResponse, 0, 500));
                $apiResponse = null;
            } elseif ($apiResponse && isset($apiResponse['response']) && is_string($apiResponse['response'])) {
                // BPJS API returns encrypted data in the 'response' field
                // Only decrypt if the decrypt checkbox is checked
                if ($decrypt) {
                    try {
                        $apiResponse['response'] = BPJSDecrypt::decryptResponse($consId, $secretKey, $auth['timestamp'], $apiResponse['response']);
                    } catch (\Exception $e) {
                        $apiResponse['decrypt_error'] = 'Decrypt failed: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    // Re-find selected module/sub for UI highlight
    // Only re-find if $selectedMod is not already set correctly
    if ($selectedMod === null || !isset($modules[$selectedMod])) {
        foreach ($modules as $mk => $mv) {
            foreach ($mv['sub_modules'] as $sm) {
                if ($sm['key'] === $subKey) {
                    $selectedMod = $mk;
                    $selectedSub = $subKey;
                    break 2;
                }
            }
        }
    }
    
    // If we have module_key from POST, use it to get the correct module (fixes duplicate key issue)
    if (isset($_POST['module_key']) && $_POST['module_key'] !== '' && isset($modules[$_POST['module_key']])) {
        $selectedMod = $_POST['module_key'];
    }
}

/*
|--------------------------------------------------------------------------
| ACTIVE MODULE / SUB (from GET or POST)
|--------------------------------------------------------------------------
*/

if ($selectedMod === null) {
    $selectedMod = $_POST['module'] ?? ($_GET['module']  ?? array_key_first($modules));
}
if ($selectedSub === null) {
    $selectedSub = $_POST['sub']    ?? ($_GET['sub']     ?? null);
}

$activeModule = $modules[$selectedMod] ?? $modules[array_key_first($modules)];
$activeSubs    = $activeModule['sub_modules'] ?? [];

// Add icon and label to module if not present
if (!isset($activeModule['icon'])) {
    $activeModule['icon'] = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m6 8V9m-6 8h6"/></svg>';
}
if (!isset($activeModule['label'])) {
    $activeModule['label'] = $selectedMod;
}

// Set base_url for active module
$activeModule['base_url'] = getBaseUrl($selectedMod, $currentDomain, $isDevMode);

// Include header
include __DIR__ . '/inc/header.php';
?>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="flex flex-1 overflow-hidden">

        <!-- ===== SIDEBAR ===== -->
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 shadow-lg">

            <!-- Search -->
            <div class="p-3 border-b border-gray-200">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-2a7 7 0 11-5-12"/>
    </svg>
                    <input
                        type="text"
                        id="sidebarSearch"
                        placeholder="Cari modul / endpoint..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:border-primary-400"
                    >
                </div>
            </div>

            <!-- Module List -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-2 space-y-1" id="moduleNav">

                <?php foreach ($modules as $modKey => $mod): ?>
                    <?php
                    $isActive   = ($modKey === $selectedMod);
                    $subCount   = count($mod['sub_modules']);
                    $expanded   = $isActive || $selectedSub !== null;
                    // Add icon and label if not present
                    $modIcon = $mod['icon'] ?? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/></svg>';
                    $modLabel = $mod['label'] ?? $modKey;
                    ?>
                    <div class="module-group" data-module="<?= $modKey ?>">

                        <!-- Module Header -->
                        <button
                            type="button"
                            onclick="toggleModule('<?= $modKey ?>')"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors
                                <?= $isActive ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-700 hover:bg-gray-100' ?>"
                        >
                            <span class="text-lg flex-shrink-0"><?= $modIcon ?></span>
                            <span class="flex-1 font-semibold text-sm truncate"><?= htmlspecialchars($modLabel) ?></span>
                            <span class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-full flex-shrink-0"><?= $subCount ?></span>
                            <svg id="arrow-<?= $modKey ?>" class="w-4 h-4 flex-shrink-0 transition-transform <?= $expanded ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Sub-modules -->
                        <div id="subs-<?= $modKey ?>" class="overflow-hidden transition-all duration-200 <?= $expanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0' ?>">
                            <div class="ml-4 mt-1 space-y-0.5 border-l-2 border-gray-200 pl-2 pb-2">

                                <?php foreach ($mod['sub_modules'] as $sub): ?>
                                    <?php
                                    $subActive = ($modKey === $selectedMod && $sub['key'] === $selectedSub);
                                    ?>
                                    <a
                                        href="?module=<?= $modKey ?>&sub=<?= $sub['key'] ?>"
                                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors
                                            <?= $subActive
                                                ? 'bg-primary-500/20 text-primary-700 border border-primary-500/50'
                                                : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' ?>"
                                    >
                                        <span class="method-<?= strtolower($sub['method']) ?> font-mono font-bold text-[10px] w-10 text-center flex-shrink-0">
                                            <?= $sub['method'] ?>
                                        </span>
                                        <span class="truncate"><?= htmlspecialchars($sub['label']) ?></span>
                                    </a>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </nav>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-gray-200 text-[10px] text-gray-500 text-center">
                BPJS Kesehatan API Catalog v1.0
            </div>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="flex-1 overflow-y-auto bg-gray-50">

            <!-- ===== MODULE HEADER ===== -->
            <div class="bg-white border-b border-gray-200 px-8 py-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-3xl"><?= $activeModule['icon'] ?></span>
                            <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($activeModule['label']) ?></h2>
                        </div>
                        <p class="text-gray-500 text-sm"><?= htmlspecialchars($activeModule['description'] ?? '') ?></p>
                        <p class="text-gray-400 text-xs mt-1 font-mono">
                            Base URL: <span class="text-gray-900 font-medium"><?= htmlspecialchars($activeModule['base_url']) ?></span>
                            <span class="ml-2 px-2 py-0.5 bg-primary-50 rounded text-[10px] text-primary-600 font-semibold"><?= strtoupper($apiDomainVersion) ?> API</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full font-medium">
                            <?= count($activeModule['sub_modules']) ?> Endpoints
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex">

                <!-- ===== ENDPOINT LIST (LEFT) ===== -->
                <div class="w-80 border-r border-gray-200 overflow-y-auto sidebar-scroll flex-shrink-0" style="max-height: calc(100vh - 200px);">
                    <div class="p-4">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Daftar Endpoint</h3>
                        <div class="space-y-1" id="endpointList">

                            <?php foreach ($activeModule['sub_modules'] as $sub): ?>
                                <?php
                                $subActive = $sub['key'] === $selectedSub;
                                ?>
                                <button
                                    type="button"
                                    onclick="selectEndpoint('<?= $sub['key'] ?>')"
                                    class="w-full text-left px-3 py-2.5 rounded-lg transition-all
                                        <?= $subActive
                                            ? 'bg-primary-50 border border-primary-200 shadow-md'
                                            : 'bg-gray-50 border border-transparent hover:bg-gray-100' ?>"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="badge-<?= strtolower($sub['method']) ?> text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">
                                            <?= $sub['method'] ?>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($sub['label']) ?></span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 font-mono truncate"><?= htmlspecialchars($sub['path']) ?></p>
                                    <p class="text-[11px] text-gray-500 mt-1 line-clamp-2"><?= htmlspecialchars($sub['description'] ?? '') ?></p>
                                </button>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>

                <!-- ===== REQUEST / RESPONSE PANEL (RIGHT) ===== -->
                <div class="flex-1 overflow-y-auto p-6 space-y-5" style="max-height: calc(100vh - 200px);">

                    <?php if ($selectedSub === null): ?>
                        <!-- No endpoint selected -->
                        <div class="flex flex-col items-center justify-center py-20 text-gray-500">
                            <div class="w-24 h-24 mb-4 text-gray-300">
                                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-lg font-semibold">Pilih endpoint dari daftar di sebelah kiri</p>
                            <p class="text-sm mt-1">Klik salah satu endpoint untuk mulai menguji API</p>
                        </div>

                    <?php else:
                        // Find selected sub-module
                        $selectedSubData = null;
                        foreach ($activeModule['sub_modules'] as $sm) {
                            if ($sm['key'] === $selectedSub) {
                                $selectedSubData = $sm;
                                break;
                            }
                        }
                        if ($selectedSubData):
                    ?>

                        <!-- ===== ENDPOINT INFO ===== -->
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="badge-<?= strtolower($selectedSubData['method']) ?> text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wide">
                                    <?= $selectedSubData['method'] ?>
                                </span>
                                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($selectedSubData['label']) ?></h3>
                            </div>
                            <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($selectedSubData['description'] ?? '') ?></p>
                            <div class="bg-gray-50 rounded-lg px-4 py-2.5 font-mono text-sm text-gray-600 border border-gray-200">
                                <span class="text-gray-500"><?= rtrim($activeModule['base_url'], '/') ?></span><?= htmlspecialchars($selectedSubData['path']) ?>
                            </div>
                        </div>

                        <!-- ===== FORMAT CONTOH ===== -->
                        <?php if (!empty($selectedSubData['format'])): ?>
                            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/>
                                        </svg>
                                        Format Contoh
                                    </h4>
                                    <button type="button" onclick="copyFormat(this)" class="text-xs text-primary-600 hover:text-primary-700 flex items-center gap-1 px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Salin
                                    </button>
                                </div>
                                <?php
                                $formatData = $selectedSubData['format'];
                                $formatJson = isset($formatData['request']) ? $formatData['request'] : (isset($formatData['response']) ? $formatData['response'] : '');
                                ?>
                                <?php if ($formatJson): ?>
                                    <pre class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-700 font-mono overflow-x-auto"><code class="format-code"><?= htmlspecialchars($formatJson) ?></code></pre>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- ===== REQUEST BUILDER ===== -->
                        <form method="POST" action="" class="space-y-4">

                            <input type="hidden" name="action"        value="send_request">
                            <input type="hidden" name="module_key"     value="<?= htmlspecialchars($selectedMod) ?>">
                            <input type="hidden" name="sub_key"        value="<?= htmlspecialchars($selectedSub) ?>">
                            <input type="hidden" name="method"         value="<?= htmlspecialchars($selectedSubData['method']) ?>">
                            <input type="hidden" name="path"           value="<?= htmlspecialchars($selectedSubData['path']) ?>">
                            <input type="hidden" name="base_url"       value="<?= htmlspecialchars($activeModule['base_url']) ?>">
                            <input type="hidden" name="module"         value="<?= htmlspecialchars($selectedMod) ?>">
                            <input type="hidden" name="sub"            value="<?= htmlspecialchars($selectedSub) ?>">

                            <!-- Decrypt Toggle -->
                            <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="decrypt" value="1" <?= ($decrypt ?? false) ? 'checked' : '' ?> class="w-4 h-4 accent-primary-500">
                                        <span class="text-sm text-gray-700 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h12zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Decrypt Response (LZString + AES-256)
                                        </span>
                                    </label>
                                </div>

                            <!-- Path / Query Parameters -->
                            <?php if (!empty($selectedSubData['params'])): ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L20 7v10l-7 4v-7m-6-4l-3-3m0 0l3 3m-3-3v12"/>
                                        </svg>
                                        Path & Query Parameters
                                    </h4>
                                    <div class="space-y-3">
                                        <?php foreach ($selectedSubData['params'] as $idx => $param): ?>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1 font-mono">
                                                    <?= htmlspecialchars($param['name']) ?>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="params[<?= $idx ?>][name]"
                                                    value="<?= htmlspecialchars($param['name']) ?>"
                                                    class="hidden"
                                                >
                                                <input
                                                    type="text"
                                                    name="params[<?= $idx ?>][value]"
                                                    value="<?= htmlspecialchars($param['default'] ?? '') ?>"
                                                    placeholder="<?= htmlspecialchars($param['placeholder']) ?>"
                                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:border-primary-400"
                                                >
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Request Body -->
                            <?php if (!empty($selectedSubData['body'])): ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/>
                                        </svg>
                                        Request Body
                                    </h4>
                                    <div class="space-y-3">
                                        <?php foreach ($selectedSubData['body'] as $idx => $field): ?>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1 font-mono">
                                                    <?= htmlspecialchars($field['name']) ?>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="body[<?= $idx ?>][name]"
                                                    value="<?= htmlspecialchars($field['name']) ?>"
                                                    class="hidden"
                                                >
                                                <textarea
                                                    name="body[<?= $idx ?>][value]"
                                                    rows="3"
                                                    placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 font-mono focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:border-primary-400"
                                                ><?= htmlspecialchars($field['default'] ?? '') ?></textarea>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Send Button -->
                            <button
                                type="submit"
                                class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary-500/30 transition-all flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Kirim Request
                            </button>

                        </form>

                    <?php endif; // endif $selectedSubData ?>

                    <!-- ===== API RESPONSE ===== -->
                    <?php if ($apiResponse !== null || $apiError !== ''): ?>
                        <div class="border-t border-gray-200 pt-5 mt-5">
                            <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8h18M3 12h18M3 16h18"/>
                                </svg>
                                Response
                            </h4>

                            <?php if ($apiError !== ''): ?>
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                                    <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9.93 9.93 0 012.318-4.193m3.378 3.378A6.003 6.003 0 0121 12z"/>
                                </svg>
                                Request Error
                            </div>
                                    <pre class="text-red-500 text-xs"><?= htmlspecialchars($apiError) ?></pre>
                                </div>
                            <?php else: ?>
                                <?php
                                // Support both 'metadata' (lowercase) and 'metaData' (camelCase) keys
                                $metaData  = $apiResponse['metadata'] ?? $apiResponse['metaData'] ?? [];
                                $httpCode  = $metaData['code']    ?? 'N/A';
                                $httpMsg   = $metaData['message'] ?? '';
                                $isNumeric = is_numeric($httpCode);
                                $codeClass = $isNumeric
                                    ? (($httpCode >= 200 && $httpCode < 300) ? 'green'
                                        : (($httpCode >= 400 && $httpCode < 500) ? 'yellow' : 'red'))
                                    : 'slate';
                                ?>
                                <div class="flex items-center gap-3 mb-3 flex-wrap">
                                    <span class="bg-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-50 border border-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-200 text-<?= $codeClass === 'green' ? 'green' : ($codeClass === 'yellow' ? 'yellow' : ($codeClass === 'red' ? 'red' : 'slate')) ?>-700 text-xs font-bold px-2.5 py-1 rounded-lg">
                                        HTTP <?= htmlspecialchars($httpCode) ?>
                                    </span>
                                    <span class="text-gray-500 text-sm"><?= htmlspecialchars($httpMsg) ?></span>
                                    <?php if (!$isNumeric || $httpCode < 200 || $httpCode >= 400): ?>
                                        <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856a1 1 0 00.883-.5 1 1 0 00.097-1.416L12 12.25"/>
                                </svg>
                                <span class="text-amber-600 text-xs font-semibold">API mengembalikan error</span>
                            </div>
                                    <?php endif; ?>
                                </div>

                                <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2 bg-gray-100 border-b border-gray-200">
                                        <span class="text-xs font-bold text-gray-600 uppercase tracking-warrow flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8h18M3 12h18M3 16h18"/>
                                            </svg>
                                            Response Body
                                        </span>
                                        <button
                                            type="button"
                                            onclick="copyResponse()"
                                            class="text-xs text-primary-600 hover:text-primary-700 hover:bg-gray-200 px-2 py-1 rounded transition-colors flex items-center gap-1"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            Salin
                                        </button>
                                    </div>
                                    <?php
                                    $jsonPretty = json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                    if ($jsonPretty === false) {
                                        $jsonPretty = print_r($apiResponse, true);
                                    }
                                    ?>
                                    <pre id="responseBody" class="p-4 text-xs text-gray-700 font-mono overflow-x-auto"><?= htmlspecialchars($jsonPretty) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!empty($debugInfo)): ?>
                        <div class="border-t border-gray-200 pt-5 mt-5">
                            <h4 class="text-sm font-bold text-amber-600 mb-3 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9v6m6-6v6m5-3a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                </svg>
                                Debug Info
                            </h4>
                            <pre class="text-xs text-gray-500"><?= htmlspecialchars(print_r($debugInfo, true)) ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php endif; // endif $selectedSub === null ?>

                </div><!-- /right panel -->
            </div><!-- /flex -->
        </main>
    </div><!-- /main layout -->

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        // Toggle module accordion
        function toggleModule(key) {
            const subs   = document.getElementById('subs-' + key);
            const arrow  = document.getElementById('arrow-' + key);
            const isOpen = subs.style.maxHeight && subs.style.maxHeight !== '0px';

            if (isOpen) {
                subs.style.maxHeight = '0px';
                subs.style.opacity   = '0';
                arrow.classList.remove('rotate-90');
            } else {
                subs.style.maxHeight = subs.scrollHeight + 'px';
                subs.style.opacity   = '1';
                arrow.classList.add('rotate-90');
            }
        }

        // Select endpoint from left list
        function selectEndpoint(key) {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '';
            const modInput = document.createElement('input');
            modInput.type  = 'hidden';
            modInput.name  = 'module';
            modInput.value = '<?= htmlspecialchars($selectedMod) ?>';
            const subInput = document.createElement('input');
            subInput.type  = 'hidden';
            subInput.name  = 'sub';
            subInput.value = key;
            form.appendChild(modInput);
            form.appendChild(subInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Copy response to clipboard
        function copyResponse() {
            const text = document.getElementById('responseBody').textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('Response copied to clipboard!');
            });
        }

        // Sidebar search filter
        document.getElementById('sidebarSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.module-group').forEach(function (grp) {
                const label = grp.querySelector('button span:nth-child(2)').textContent.toLowerCase();
                const subs  = grp.querySelectorAll('[data-sub]');
                let match   = label.includes(q);
                if (!match) {
                    grp.querySelectorAll('.sub-item').forEach(function (s) {
                        s.style.display = s.textContent.toLowerCase().includes(q) ? '' : 'none';
                        if (s.style.display !== 'none') match = true;
                    });
                }
                grp.style.display = match ? '' : 'none';
                if (match && q) {
                    const subsEl = grp.querySelector('[id^="subs-"]');
                    if (subsEl) { subsEl.style.maxHeight = '2000px'; subsEl.style.opacity = '1'; }
                }
            });
        });

        // Persist form data after submission
        function getStorageKey() {
            return 'bpjs_form_' + <?= json_encode($selectedMod) ?> + '_' + <?= json_encode($selectedSub) ?>;
        }

        function saveFormData() {
            const formData = {};
            const inputs = document.querySelectorAll('input[type="text"], input[type="number"], textarea');
            inputs.forEach(function(input) {
                if (input.name) {
                    formData[input.name] = input.value;
                }
            });
            localStorage.setItem(getStorageKey(), JSON.stringify(formData));
        }

        function restoreFormData() {
            const saved = localStorage.getItem(getStorageKey());
            if (!saved) return;
            const formData = JSON.parse(saved);
            Object.keys(formData).forEach(function(name) {
                const input = document.querySelector('[name="' + name + '"]');
                if (input) {
                    input.value = formData[name];
                }
            });
        }

        // Save form data on input/change
        document.addEventListener('input', function(e) {
            if (e.target.closest('form')) {
                saveFormData();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.closest('form')) {
                saveFormData();
            }
        });

        // Restore form data on page load
        document.addEventListener('DOMContentLoaded', restoreFormData);
    </script>
</body>
</html>
