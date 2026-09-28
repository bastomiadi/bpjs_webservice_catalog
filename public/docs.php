<?php

/**
 * BPJS Web Service Documentation Viewer
 * Reads and renders markdown files from the docs/ directory.
 */

$basePath = __DIR__ . '/..';

// Initialize bootstrap
require_once $basePath . '/app/Bootstrap.php';
BPJSBootstrap::init($basePath);

$credentials = BPJSBootstrap::getCredentials();
$apiConfig = BPJSBootstrap::getApiConfig();

$consId = $credentials['cons_id'];
$secretKey = $credentials['secret_key'];
$userKey = $credentials['user_key'];
$isDevMode = BPJSBootstrap::getIsDevMode();
$apiDomainVersion = $apiConfig['api_version'];
$currentDomain = BPJSBootstrap::getCurrentDomain();

// Docs Directory
$docsDir = realpath($basePath . '/docs');

// List of documentation definitions with human-friendly metadata
$docDefinitions = [
    'vclaim' => [
        'title' => 'VClaim (Virtual Claim)',
        'description' => 'Klaim, LPK, SEP, Rujukan, SPRI & Rencana Kontrol',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/></svg>',
        'badge' => '87 Endpoints',
        'color' => 'text-blue-600 bg-blue-50 border-blue-200'
    ],
    'antreanrs' => [
        'title' => 'Antrean RS',
        'description' => 'Antrean Rumah Sakit, Jadwal Dokter & Mobile JKN',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>',
        'badge' => '28 Endpoints',
        'color' => 'text-red-600 bg-red-50 border-red-200'
    ],
    'antreanfktp' => [
        'title' => 'Antrean FKTP',
        'description' => 'Antrean Faskes Tingkat Pertama & Mobile JKN',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a8 8 0 11-3.536-3.536L8.343 7.657m8.484 8.484z"/></svg>',
        'badge' => '14 Endpoints',
        'color' => 'text-green-600 bg-green-50 border-green-200'
    ],
    'apotek' => [
        'title' => 'Apotek (Farmasi)',
        'description' => 'Obat DPHO, Resep, Pelayanan Obat & Stok',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2m14 0V9a2 2 0 00-2-2M7 13h5m-5-4h2m8 0h2"/></svg>',
        'badge' => '18 Endpoints',
        'color' => 'text-purple-600 bg-purple-50 border-purple-200'
    ],
    'pcare' => [
        'title' => 'PCare (Primary Care)',
        'description' => 'Diagnosa, MCU, Obat, Kunjungan & Skrining',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/></svg>',
        'badge' => '51 Endpoints',
        'color' => 'text-orange-600 bg-orange-50 border-orange-200'
    ],
    'icare' => [
        'title' => 'i-Care JKN',
        'description' => 'Validasi & Riwayat Pelayanan FKRTL dan FKTP',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.317 4.317A4 4 0 016.414 3h7.172a4 4 0 012.096.317L19 6.414a2 2 0 01.586 1.414v7.172a2 2 0 01-.317 2.096l-2.097 2.097a4 4 0 01-2.096.317H6.414a4 4 0 01-2.096-.317L2 19l2.097-2.097z"/></svg>',
        'badge' => '2 Endpoints',
        'color' => 'text-pink-600 bg-pink-50 border-pink-200'
    ],
    'rekammedis' => [
        'title' => 'e-Rekam Medis',
        'description' => 'Pengiriman Data Rekam Medis Elektronik (RME)',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m6 8V9m-6 8h6m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>',
        'badge' => '1 Endpoint',
        'color' => 'text-teal-600 bg-teal-50 border-teal-200'
    ],
    'aplicares' => [
        'title' => 'Aplicares',
        'description' => 'Ketersediaan Kamar & Tempat Tidur RS',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>',
        'badge' => '5 Endpoints',
        'color' => 'text-indigo-600 bg-indigo-50 border-indigo-200'
    ],
    'create_signature_pcare' => [
        'title' => 'Signature & Auth (PCare)',
        'description' => 'Panduan HMAC-SHA256, Timestamp & Base64 Auth',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
        'badge' => 'Panduan Teknis',
        'color' => 'text-amber-600 bg-amber-50 border-amber-200'
    ],
    'batalkodebooking' => [
        'title' => 'Batch Batal Kode Booking',
        'description' => 'Tools Mass Cancellation Antrean via Regex & HMAC',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'badge' => 'Script / Tools',
        'color' => 'text-slate-600 bg-slate-50 border-slate-200'
    ]
];

// Scan docs folder for any .md files
$availableDocs = [];
if ($docsDir && is_dir($docsDir)) {
    foreach (glob($docsDir . '/*.md') as $file) {
        $key = pathinfo($file, PATHINFO_FILENAME);
        $size = filesize($file);
        $availableDocs[$key] = [
            'key' => $key,
            'file' => $file,
            'size' => $size,
            'size_formatted' => $size > 1024 ? round($size / 1024, 1) . ' KB' : $size . ' B',
            'title' => $docDefinitions[$key]['title'] ?? ucfirst($key),
            'description' => $docDefinitions[$key]['description'] ?? 'Dokumentasi ' . $key,
            'icon' => $docDefinitions[$key]['icon'] ?? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m7-6v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v10a4 4 0 004 4h6a4 4 0 004-4V12z"/></svg>',
            'badge' => $docDefinitions[$key]['badge'] ?? round($size / 1024, 1) . ' KB',
            'color' => $docDefinitions[$key]['color'] ?? 'text-blue-600 bg-blue-50 border-blue-200'
        ];
    }
}

// Determine selected document
$selectedDoc = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['doc'] ?? 'vclaim');
if (!isset($availableDocs[$selectedDoc])) {
    $firstKey = array_key_first($availableDocs);
    $selectedDoc = $firstKey ?? 'vclaim';
}

// Load markdown content
$currentDocMeta = $availableDocs[$selectedDoc] ?? null;
$mdContent = '';
if ($currentDocMeta && file_exists($currentDocMeta['file'])) {
    $mdContent = file_get_contents($currentDocMeta['file']);
}

// Raw download / view option
if (isset($_GET['raw']) && $_GET['raw'] === '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $mdContent;
    exit;
}

$pageTitle = 'Dokumentasi Web Service BPJS - ' . ($currentDocMeta['title'] ?? 'Docs');
$showConsId = false;
$showModeSelector = false;
$showVersionSelector = false;
$activeNav = 'docs';

include __DIR__ . '/inc/header.php';
?>

<!-- Marked.js for fast, accurate GitHub-Flavored Markdown parsing (Local) -->
<script src="assets/js/marked.min.js"></script>

<!-- Highlight.js for Code Highlighting (Local) -->
<link rel="stylesheet" href="assets/css/github-dark.min.css">
<script src="assets/js/highlight.min.js"></script>
<script src="assets/js/highlight-json.min.js"></script>
<script src="assets/js/highlight-php.min.js"></script>
<script src="assets/js/highlight-bash.min.js"></script>
<script src="assets/js/highlight-http.min.js"></script>

<style>
    /* Documentation Typography & Elements */
    .markdown-body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #1e293b;
        line-height: 1.7;
    }
    .markdown-body h1 {
        font-size: 1.875rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        color: #0f172a;
        margin-top: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .markdown-body h2 {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1e293b;
        margin-top: 2.25rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        scroll-margin-top: 5rem;
    }
    .markdown-body h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e40af;
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        scroll-margin-top: 5rem;
    }
    .markdown-body h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #475569;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .markdown-body p {
        margin-bottom: 1rem;
    }
    .markdown-body ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .markdown-body ol {
        list-style-type: decimal;
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .markdown-body li {
        margin-bottom: 0.35rem;
    }
    .markdown-body hr {
        border: 0;
        border-top: 1px solid #e2e8f0;
        margin: 2rem 0;
    }
    .markdown-body a {
        color: #2563eb;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.15s ease;
    }
    .markdown-body a:hover {
        color: #1d4ed8;
    }

    /* Tables */
    .markdown-body table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 1.25rem 0;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .markdown-body th {
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: left;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .markdown-body td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        vertical-align: top;
    }
    .markdown-body tr:last-child td {
        border-bottom: none;
    }
    .markdown-body tr:hover td {
        background: #f8fafc;
    }

    /* Blockquotes */
    .markdown-body blockquote {
        margin: 1rem 0;
        padding: 0.875rem 1.25rem;
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        border-radius: 0 0.5rem 0.5rem 0;
        color: #334155;
        font-size: 0.9rem;
    }
    .markdown-body blockquote p {
        margin: 0;
    }

    /* Inline Code */
    .markdown-body :not(pre) > code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.85em;
        background: #f1f5f9;
        color: #0f172a;
        padding: 0.2em 0.45em;
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
    }

    /* Code Blocks */
    .code-wrapper {
        position: relative;
        margin: 1.25rem 0;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #1e293b;
        background: #0d1117;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }
    .code-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        background: #161b22;
        border-bottom: 1px solid #21262d;
        font-size: 0.75rem;
        font-family: ui-monospace, monospace;
        color: #8b949e;
    }
    .code-copy-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 0.375rem;
        background: #21262d;
        color: #c9d1d9;
        font-size: 0.75rem;
        transition: all 0.15s ease;
        border: 1px solid #30363d;
        cursor: pointer;
    }
    .code-copy-btn:hover {
        background: #30363d;
        color: #ffffff;
    }
    .code-wrapper pre {
        margin: 0;
        padding: 1rem;
        overflow-x: auto;
        background: transparent !important;
        font-size: 0.85rem;
        line-height: 1.6;
    }
    .code-wrapper pre code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: transparent !important;
        padding: 0 !important;
        border: none !important;
    }

    /* Method Badges */
    .http-badge {
        display: inline-block;
        font-family: ui-monospace, monospace;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 0.375rem;
        text-transform: uppercase;
        margin-right: 0.5rem;
    }
    .http-get { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .http-post { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .http-put { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .http-delete { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Right TOC Navigation Styles */
    .toc-item-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.4rem 0.65rem;
        border-radius: 0.5rem;
        color: #475569;
        font-size: 0.75rem;
        line-height: 1.3;
        transition: all 0.15s ease;
        text-decoration: none !important;
        border-left: 2px solid transparent;
    }
    .toc-item-link:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .toc-item-link.active {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 600;
        border-left-color: #2563eb !important;
    }
    .toc-category-header {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        padding: 0.75rem 0.5rem 0.25rem 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px dashed #e2e8f0;
    }
    .toc-category-header:first-child {
        margin-top: 0;
        border-top: none;
        padding-top: 0.25rem;
    }

    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="flex-1 flex overflow-hidden bg-gray-50">

    <!-- ===== LEFT SIDEBAR: LIST OF DOCS ===== -->
    <aside class="w-80 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 shadow-sm z-10">
        <!-- Search Docs -->
        <div class="p-3 border-b border-gray-200 bg-white">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-2a7 7 0 11-5-12"/>
                </svg>
                <input
                    type="text"
                    id="docSearchInput"
                    placeholder="Cari dokumentasi modul..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400"
                >
            </div>
        </div>

        <!-- Documentation List -->
        <div class="flex-1 overflow-y-auto custom-scroll p-3 space-y-1.5" id="docsListContainer">
            <?php foreach ($availableDocs as $k => $doc): 
                $isSelected = ($k === $selectedDoc);
            ?>
            <a
                href="docs.php?doc=<?= urlencode($k) ?>"
                class="doc-item group block p-3 rounded-xl transition-all duration-150 border <?= $isSelected ? 'bg-primary-50/70 border-primary-300 shadow-sm' : 'bg-white border-gray-100 hover:bg-gray-50 hover:border-gray-200' ?>"
                data-title="<?= htmlspecialchars(strtolower($doc['title'])) ?>"
                data-desc="<?= htmlspecialchars(strtolower($doc['description'])) ?>"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $doc['color'] ?>">
                            <?= $doc['icon'] ?>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-tight group-hover:text-primary-600 transition-colors">
                                <?= htmlspecialchars($doc['title']) ?>
                            </h3>
                            <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">
                                <?= htmlspecialchars($doc['description']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 text-[11px]">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-600">
                        <?= htmlspecialchars($doc['badge']) ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-gray-200 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span><?= count($availableDocs) ?> Modul Dokumentasi</span>
            <a href="docs.php?doc=<?= urlencode($selectedDoc) ?>&raw=1" target="_blank" class="text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                <span>Raw .md</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT: MARKDOWN VIEWER ===== -->
    <main class="flex-1 flex flex-col overflow-hidden bg-white">

        <!-- Content Top Bar -->
        <div class="px-6 py-3.5 border-b border-gray-200 bg-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 <?= $currentDocMeta['color'] ?? 'bg-primary-50 text-primary-600' ?>">
                    <?= $currentDocMeta['icon'] ?? '' ?>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 leading-tight">
                        <?= htmlspecialchars($currentDocMeta['title'] ?? 'Dokumentasi') ?>
                    </h2>
                    <p class="text-xs text-gray-500 flex items-center gap-2">
                        <span>File: <code class="font-mono text-gray-700">docs/<?= htmlspecialchars($selectedDoc) ?>.md</code></span>
                        <span>•</span>
                        <span>Ukuran: <?= htmlspecialchars($currentDocMeta['size_formatted'] ?? '0 KB') ?></span>
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <!-- Search in Document -->
                <div class="relative hidden sm:block">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-2a7 7 0 11-5-12"/>
                    </svg>
                    <input
                        type="text"
                        id="inPageSearch"
                        placeholder="Cari dalam dokumen..."
                        class="w-48 bg-gray-50 border border-gray-200 rounded-lg pl-8 pr-3 py-1.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400"
                    >
                </div>

                <!-- Copy Markdown Button -->
                <button
                    onclick="copyMarkdownSource()"
                    id="btnCopyMd"
                    class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center gap-1.5 transition-colors"
                    title="Salin Markdown mentah"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Salin Markdown</span>
                </button>

                <!-- Toggle Raw View -->
                <button
                    onclick="toggleRawView()"
                    id="btnToggleRaw"
                    class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center gap-1.5 transition-colors"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span id="rawBtnText">Lihat Raw</span>
                </button>

                <!-- Open in Catalog testing if available -->
                <?php if (in_array($selectedDoc, ['vclaim', 'antreanrs', 'antreanfktp', 'apotek', 'pcare', 'icare', 'rekammedis', 'aplicares', 'batalkodebooking'])): 
                    $catalogModuleMap = [
                        'vclaim' => 'vclaim',
                        'antreanrs' => 'antrean_rs',
                        'antreanfktp' => 'antrean_fktp',
                        'apotek' => 'apotek',
                        'pcare' => 'pcare',
                        'icare' => 'icare',
                        'rekammedis' => 'ws_rekam_medis',
                        'aplicares' => 'aplicares',
                        'batalkodebooking' => 'tools'
                    ];
                    $targetMod = $catalogModuleMap[$selectedDoc] ?? 'vclaim';
                ?>
                <a
                    href="catalog.php?module=<?= urlencode($targetMod) ?>"
                    class="px-3 py-1.5 rounded-lg bg-primary-50 text-primary-700 border border-primary-200 text-xs font-medium hover:bg-primary-100 flex items-center gap-1.5 transition-colors"
                    title="Buka modul ini di API Catalog untuk test request"
                >
                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span>Test API</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rendered Content Body -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Markdown Container -->
            <div class="flex-1 overflow-y-auto custom-scroll p-6 lg:p-8" id="markdownScrollContainer">
                <div class="max-w-4xl mx-auto">
                    <!-- Formatted Markdown -->
                    <div id="markdownRenderArea" class="markdown-body">
                        <!-- Loaded by JavaScript from rawMarkdownData below -->
                    </div>

                    <!-- Raw Markdown (Hidden by default) -->
                    <div id="rawMarkdownArea" class="hidden">
                        <textarea readonly class="w-full h-[75vh] font-mono text-xs p-4 bg-gray-900 text-gray-100 rounded-xl border border-gray-800 focus:outline-none resize-none custom-scroll"><?= htmlspecialchars($mdContent) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- In-Page TOC (On this page) -->
            <aside class="w-72 xl:w-80 border-l border-gray-200 bg-white flex flex-col flex-shrink-0 hidden xl:flex shadow-[-2px_0_8px_rgba(0,0,0,0.02)] z-10" id="inPageTocContainer">
                <!-- TOC Header & Quick Filter -->
                <div class="p-3.5 border-b border-gray-100 bg-gray-50/70 flex-shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 tracking-wide uppercase">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span>Daftar Endpoint</span>
                        </div>
                        <span id="tocEndpointCount" class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-full bg-primary-100 text-primary-700">0</span>
                    </div>

                    <!-- Filter input -->
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-2a7 7 0 11-5-12"/>
                        </svg>
                        <input
                            type="text"
                            id="tocFilterInput"
                            placeholder="Cari di daftar endpoint..."
                            class="w-full bg-white border border-gray-200 rounded-lg pl-8 pr-7 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all"
                        >
                        <button id="tocClearBtn" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden" onclick="clearTocFilter()">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- TOC Scrollable Navigation List -->
                <nav class="flex-1 overflow-y-auto custom-scroll p-3 space-y-1 text-xs" id="inPageTocNav">
                    <!-- Populated dynamically via JS -->
                </nav>
            </aside>
        </div>
    </main>

</div>

<!-- Hidden RAW markdown source for JS renderer -->
<script id="rawMarkdownSource" type="text/markdown"><?= htmlspecialchars($mdContent, ENT_NOQUOTES, 'UTF-8') ?></script>

<script>
    // 1. Markdown Parsing with Marked & Highlight.js
    const rawMarkdown = document.getElementById('rawMarkdownSource').textContent;
    const renderArea = document.getElementById('markdownRenderArea');
    const tocNav = document.getElementById('inPageTocNav');

    // Configure marked options
    marked.setOptions({
        gfm: true,
        breaks: false,
        headerIds: true,
        mangle: false,
        highlight: function(code, lang) {
            if (typeof hljs !== 'undefined') {
                const language = hljs.getLanguage(lang) ? lang : 'plaintext';
                return hljs.highlight(code, { language }).value;
            }
            return code;
        }
    });

    // Helper to safely escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Custom renderer for code blocks and badges
    const renderer = new marked.Renderer();

    renderer.code = function(tokenOrCode, language) {
        let codeStr = '';
        let lang = 'plaintext';
        if (typeof tokenOrCode === 'object' && tokenOrCode !== null) {
            codeStr = tokenOrCode.text || '';
            lang = tokenOrCode.lang || 'plaintext';
        } else {
            codeStr = String(tokenOrCode || '');
            lang = language || 'plaintext';
        }

        lang = (lang || 'plaintext').trim().toLowerCase();
        let highlighted = '';
        try {
            if (hljs.getLanguage(lang)) {
                highlighted = hljs.highlight(codeStr, { language: lang }).value;
            } else {
                highlighted = hljs.highlightAuto(codeStr).value;
            }
        } catch (e) {
            highlighted = escapeHtml(codeStr);
        }

        const id = 'code_' + Math.random().toString(36).substr(2, 9);
        return `
            <div class="code-wrapper">
                <div class="code-header">
                    <span class="font-mono text-xs font-semibold tracking-wider text-slate-300">${lang.toUpperCase()}</span>
                    <button type="button" class="code-copy-btn" onclick="copyCodeBlock('${id}')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Salin</span>
                    </button>
                </div>
                <pre><code id="${id}" class="hljs language-${lang}">${highlighted}</code></pre>
            </div>
        `;
    };

    // Render markdown to HTML
    renderArea.innerHTML = marked.parse(rawMarkdown, { renderer: renderer });

    // Post-rendering beautifier for endpoints, parameters, and tables
    function beautifyDocumentation(container) {
        // 1. Wrap tables in responsive wrapper
        container.querySelectorAll('table').forEach(tbl => {
            if (!tbl.parentElement.classList.contains('overflow-x-auto')) {
                const wrap = document.createElement('div');
                wrap.className = 'overflow-x-auto my-4 custom-scroll shadow-sm rounded-xl border border-gray-200';
                tbl.parentNode.insertBefore(wrap, tbl);
                wrap.appendChild(tbl);
            }
        });

        // 2. Transform Endpoint specification <ul> (Method, URL, Format, Content-Type)
        container.querySelectorAll('ul').forEach(ul => {
            const lis = Array.from(ul.children);
            if (lis.length === 0) return;

            let hasMethod = false;
            let methodVal = '';
            let urlVal = '';
            let formatVal = '';
            let contentTypeVal = '';
            let headerItems = [];
            let otherItems = [];

            lis.forEach(li => {
                const txt = li.textContent.trim();
                if (/^Method\s*:\s*(GET|POST|PUT|DELETE|PATCH)/i.test(txt)) {
                    hasMethod = true;
                    const m = txt.match(/^Method\s*:\s*(GET|POST|PUT|DELETE|PATCH)/i);
                    methodVal = m[1].toUpperCase();
                } else if (/^URL\s*:\s*(.+)/i.test(txt)) {
                    urlVal = li.querySelector('code') ? li.querySelector('code').textContent : txt.replace(/^URL\s*:\s*/i, '');
                } else if (/^Format\s*:\s*(.+)/i.test(txt)) {
                    formatVal = txt.replace(/^Format\s*:\s*/i, '');
                } else if (/^(Content-Type|Accept)\s*:\s*(.+)/i.test(txt)) {
                    contentTypeVal = txt.replace(/^(Content-Type|Accept)\s*:\s*/i, '');
                } else {
                    otherItems.push(li);
                }
            });

            if (hasMethod || urlVal) {
                const card = document.createElement('div');
                card.className = 'endpoint-banner bg-slate-900 border border-slate-800 rounded-xl p-4 my-4 text-white shadow-md';
                
                const badgeClass = {
                    'GET': 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50',
                    'POST': 'bg-blue-500/20 text-blue-400 border-blue-500/50',
                    'PUT': 'bg-amber-500/20 text-amber-400 border-amber-500/50',
                    'DELETE': 'bg-rose-500/20 text-rose-400 border-rose-500/50'
                }[methodVal] || 'bg-slate-700 text-slate-200 border-slate-600';

                const copyUrlEscaped = escapeHtml(urlVal);

                card.innerHTML = `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 flex-1 min-w-0">
                            ${methodVal ? `<span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold tracking-wide border ${badgeClass}">${methodVal}</span>` : ''}
                            <code class="font-mono text-xs sm:text-sm text-emerald-300 font-semibold truncate bg-slate-950/80 px-3 py-1.5 rounded-lg border border-slate-800 flex-1">${copyUrlEscaped}</code>
                        </div>
                        ${urlVal ? `
                        <button type="button" onclick="navigator.clipboard.writeText('${urlVal.replace(/'/g, "\\'")}'); this.innerText='Tersalin!'; setTimeout(()=>this.innerText='Salin URL', 1500);" class="text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-lg border border-slate-700 transition-colors flex-shrink-0">
                            Salin URL
                        </button>` : ''}
                    </div>
                    ${(formatVal || contentTypeVal) ? `
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 mt-3 pt-3 border-t border-slate-800">
                        ${formatVal ? `<div><span class="text-slate-500 font-medium">Format:</span> <span class="font-mono text-slate-300 font-semibold">${escapeHtml(formatVal)}</span></div>` : ''}
                        ${contentTypeVal ? `<div><span class="text-slate-500 font-medium">Content-Type:</span> <span class="font-mono text-slate-300 font-semibold">${escapeHtml(contentTypeVal)}</span></div>` : ''}
                    </div>` : ''}
                `;

                ul.parentNode.insertBefore(card, ul);

                if (otherItems.length === 0) {
                    ul.remove();
                } else {
                    ul.innerHTML = '';
                    otherItems.forEach(item => ul.appendChild(item));
                }
            }
        });

        // 3. Beautify Parameters list
        container.querySelectorAll('li').forEach(li => {
            const text = li.textContent.trim();
            if (/^Parameter\s*\d*\s*:/i.test(text)) {
                const m = text.match(/^Parameter\s*(\d*)\s*:\s*(.+)/i);
                const num = m[1] ? `Parameter ${m[1]}` : 'Parameter';
                const desc = m[2];
                li.className = 'list-none my-1.5 flex items-baseline gap-2.5 p-2.5 rounded-lg bg-blue-50/70 border border-blue-100 text-xs sm:text-sm';
                li.innerHTML = `<span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-mono font-bold flex-shrink-0 text-xs">${num}</span><span class="text-slate-700 leading-relaxed">${escapeHtml(desc)}</span>`;
            }
        });

        // 4. Beautify Fungsi paragraphs
        container.querySelectorAll('p').forEach(p => {
            const text = p.textContent.trim();
            if (/^Fungsi\s*:\s*(.+)/i.test(text)) {
                const desc = text.replace(/^Fungsi\s*:\s*/i, '');
                p.className = 'text-sm text-slate-600 font-medium mb-3 flex items-start gap-2.5 bg-slate-50 border border-slate-200/90 p-3 rounded-xl';
                p.innerHTML = `<svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span><strong class="text-slate-800">Fungsi:</strong> ${escapeHtml(desc)}</span>`;
            }
        });

        // 5. Beautify Request / Response labels
        container.querySelectorAll('h4').forEach(h4 => {
            const text = h4.textContent.trim();
            if (/^(Request|Response|Response Sukses|Response Gagal)$/i.test(text)) {
                const isResponse = text.toLowerCase().includes('response');
                const badgeCls = isResponse 
                    ? (text.toLowerCase().includes('gagal') ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200')
                    : 'bg-primary-50 text-primary-700 border-primary-200';
                
                h4.className = 'my-3';
                h4.innerHTML = `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border ${badgeCls}">${text}</span>`;
            }
        });
    }

    // Run beautifier
    beautifyDocumentation(renderArea);

    // 2. Generate In-Page Table of Contents (TOC) with Categories, Method Badges, Search & ScrollSpy
    const headings = renderArea.querySelectorAll('h2, h3');
    const endpointCountEl = document.getElementById('tocEndpointCount');
    const tocFilterInput = document.getElementById('tocFilterInput');
    const tocClearBtn = document.getElementById('tocClearBtn');
    const scrollContainer = document.getElementById('markdownScrollContainer');

    let endpointCount = 0;
    const tocLinks = [];
    const headingElements = [];

    // Helper: detect HTTP method from surrounding elements or title
    function getEndpointMethod(heading, titleText) {
        let el = heading.nextElementSibling;
        let checks = 0;
        while (el && checks < 8 && !el.matches('h2, h3')) {
            // Check if endpoint-banner was generated
            const banner = el.classList?.contains('endpoint-banner') ? el : el.querySelector?.('.endpoint-banner');
            if (banner) {
                const badge = banner.querySelector('span');
                if (badge && /^(GET|POST|PUT|DELETE|PATCH)$/i.test(badge.textContent.trim())) {
                    return badge.textContent.trim().toUpperCase();
                }
            }
            // Check raw text for Method: GET
            const textMatch = el.textContent.match(/Method\s*:\s*(GET|POST|PUT|DELETE|PATCH)/i);
            if (textMatch) {
                return textMatch[1].toUpperCase();
            }
            el = el.nextElementSibling;
            checks++;
        }

        // Fallback from title text
        if (/^(Get|Cari|Lihat|Pencarian)\b/i.test(titleText)) return 'GET';
        if (/^(Add|Insert|Create|Post|Kirim|Simpan)\b/i.test(titleText)) return 'POST';
        if (/^(Edit|Update|Put|Ubah)\b/i.test(titleText)) return 'PUT';
        if (/^(Delete|Hapus|Batal)\b/i.test(titleText)) return 'DELETE';
        return '';
    }

    if (headings.length > 0) {
        let currentCategoryGroup = null;
        let hasOverview = false;

        headings.forEach((heading, idx) => {
            const rawTitle = heading.textContent.trim().replace(/^#+\s*/, '');

            // Skip invalid syntax noise or junk
            if (rawTitle.length < 2) return;
            if (/^[\]\}\)\[\{\"]/.test(rawTitle)) return;
            if (/^KET\s*:/i.test(rawTitle)) return;
            if (/^kd[A-Z]/i.test(rawTitle)) return;

            // Assign clean id
            if (!heading.id) {
                heading.id = 'heading-' + idx + '-' + rawTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }

            const isH2 = heading.tagName === 'H2';
            const normalizedTitle = rawTitle.toLowerCase();

            // Skip "Daftar Endpoint"
            if (normalizedTitle === 'daftar endpoint') return;

            // Overview items (Base URL, HTTP Header Standar)
            if (isH2 && (normalizedTitle === 'base url' || normalizedTitle.includes('header standar'))) {
                if (!hasOverview) {
                    const overviewHeader = document.createElement('div');
                    overviewHeader.className = 'flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-2 pt-1 pb-1 mb-0.5';
                    overviewHeader.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span><span>INFORMASI UMUM</span>`;
                    tocNav.appendChild(overviewHeader);
                    hasOverview = true;
                }
                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.className = 'toc-item-link pl-3';
                link.setAttribute('data-target-id', heading.id);
                link.setAttribute('data-title', rawTitle.toLowerCase());
                link.innerHTML = `
                    <span class="truncate font-medium flex-1">${escapeHtml(rawTitle)}</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-slate-100 text-slate-500 border border-slate-200">INFO</span>
                `;
                link.onclick = (e) => {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth' });
                    history.pushState(null, null, '#' + heading.id);
                };
                tocNav.appendChild(link);
                tocLinks.push(link);
                headingElements.push({ heading, link });
                return;
            }

            // Category group header (H2)
            if (isH2) {
                const catGroup = document.createElement('div');
                catGroup.className = 'toc-category-wrapper mt-3 pt-2.5 border-t border-slate-100 first:border-0 first:mt-0 first:pt-0';
                
                const catTitle = document.createElement('div');
                catTitle.className = 'flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-2 pb-1.5';
                const catCleanName = rawTitle.replace(/^Web Service\s+/i, '').trim();
                catTitle.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-primary-400"></span><span class="text-slate-600 font-bold">${escapeHtml(catCleanName)}</span>`;
                catGroup.appendChild(catTitle);

                const itemsContainer = document.createElement('div');
                itemsContainer.className = 'space-y-0.5 mt-0.5';
                catGroup.appendChild(itemsContainer);

                tocNav.appendChild(catGroup);
                currentCategoryGroup = itemsContainer;
                return;
            }

            // Endpoint item (H3)
            endpointCount++;
            const method = getEndpointMethod(heading, rawTitle);
            
            const badgeClasses = {
                'GET': 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'POST': 'bg-blue-50 text-blue-700 border-blue-200',
                'PUT': 'bg-amber-50 text-amber-700 border-amber-200',
                'DELETE': 'bg-rose-50 text-rose-700 border-rose-200'
            }[method] || 'bg-slate-100 text-slate-600 border-slate-200';

            const link = document.createElement('a');
            link.href = '#' + heading.id;
            link.className = 'toc-item-link pl-3';
            link.title = rawTitle;
            link.setAttribute('data-target-id', heading.id);
            link.setAttribute('data-title', rawTitle.toLowerCase());
            link.setAttribute('data-method', method.toLowerCase());

            link.innerHTML = `
                <span class="truncate font-medium flex-1">${escapeHtml(rawTitle)}</span>
                ${method ? `<span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold border ${badgeClasses} flex-shrink-0 tracking-wide">${method}</span>` : ''}
            `;

            link.onclick = (e) => {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth' });
                history.pushState(null, null, '#' + heading.id);
            };

            if (currentCategoryGroup) {
                currentCategoryGroup.appendChild(link);
            } else {
                tocNav.appendChild(link);
            }

            tocLinks.push(link);
            headingElements.push({ heading, link });
        });

        if (endpointCountEl) {
            endpointCountEl.textContent = endpointCount + ' Endpoint';
        }

        // ScrollSpy logic
        function updateScrollSpy() {
            if (!scrollContainer) return;
            const containerTop = scrollContainer.getBoundingClientRect().top;
            let currentActive = null;

            for (let i = 0; i < headingElements.length; i++) {
                const rect = headingElements[i].heading.getBoundingClientRect();
                if (rect.top - containerTop <= 100) {
                    currentActive = headingElements[i].link;
                } else {
                    break;
                }
            }

            if (!currentActive && headingElements.length > 0) {
                currentActive = headingElements[0].link;
            }

            tocLinks.forEach(l => l.classList.remove('active'));
            if (currentActive) {
                currentActive.classList.add('active');
            }
        }

        scrollContainer?.addEventListener('scroll', updateScrollSpy, { passive: true });
        setTimeout(updateScrollSpy, 250);

        // Filter / Search in TOC
        if (tocFilterInput) {
            tocFilterInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                if (tocClearBtn) {
                    if (query) tocClearBtn.classList.remove('hidden');
                    else tocClearBtn.classList.add('hidden');
                }

                let visibleCount = 0;
                tocLinks.forEach(link => {
                    const title = link.getAttribute('data-title') || '';
                    const method = link.getAttribute('data-method') || '';
                    if (!query || title.includes(query) || method.includes(query)) {
                        link.style.display = 'flex';
                        visibleCount++;
                    } else {
                        link.style.display = 'none';
                    }
                });

                // Hide category wrapper if all its links are hidden
                document.querySelectorAll('.toc-category-wrapper').forEach(cat => {
                    const hasVisibleLinks = Array.from(cat.querySelectorAll('.toc-item-link')).some(l => l.style.display !== 'none');
                    cat.style.display = hasVisibleLinks ? 'block' : 'none';
                });

                if (endpointCountEl) {
                    endpointCountEl.textContent = query ? (visibleCount + ' Ditemukan') : (endpointCount + ' Endpoint');
                }
            });
        }

    } else {
        document.getElementById('inPageTocContainer')?.classList.add('hidden');
    }

    function clearTocFilter() {
        if (tocFilterInput) {
            tocFilterInput.value = '';
            tocFilterInput.dispatchEvent(new Event('input'));
            tocFilterInput.focus();
        }
    }

    // 3. Copy Code Block Helper
    function copyCodeBlock(id) {
        const codeEl = document.getElementById(id);
        if (!codeEl) return;
        const text = codeEl.innerText || codeEl.textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = codeEl.closest('.code-wrapper').querySelector('.code-copy-btn');
            const original = btn.innerHTML;
            btn.innerHTML = '<span class="text-green-400 font-semibold">Tersalin!</span>';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }

    // 4. Copy Entire Markdown Source
    function copyMarkdownSource() {
        navigator.clipboard.writeText(rawMarkdown).then(() => {
            const btn = document.getElementById('btnCopyMd');
            const original = btn.innerHTML;
            btn.innerHTML = '<span class="text-green-600 font-semibold">Tersalin!</span>';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }

    // 5. Toggle Raw Markdown View
    let isRawView = false;
    function toggleRawView() {
        isRawView = !isRawView;
        const renderArea = document.getElementById('markdownRenderArea');
        const rawArea = document.getElementById('rawMarkdownArea');
        const btnText = document.getElementById('rawBtnText');

        if (isRawView) {
            renderArea.classList.add('hidden');
            rawArea.classList.remove('hidden');
            btnText.textContent = 'Lihat Formatted';
        } else {
            renderArea.classList.remove('hidden');
            rawArea.classList.add('hidden');
            btnText.textContent = 'Lihat Raw';
        }
    }

    // 6. Search within documentation list (Left Sidebar)
    document.getElementById('docSearchInput')?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.doc-item').forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const desc = item.getAttribute('data-desc') || '';
            if (title.includes(query) || desc.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // 7. Search in Current Document Content
    document.getElementById('inPageSearch')?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        if (!query) {
            // Unhide all headings and paragraphs
            document.querySelectorAll('#markdownRenderArea > *').forEach(el => el.style.opacity = '1');
            return;
        }

        document.querySelectorAll('#markdownRenderArea > h2, #markdownRenderArea > h3, #markdownRenderArea > p, #markdownRenderArea > ul, #markdownRenderArea > .code-wrapper').forEach(el => {
            if (el.textContent.toLowerCase().includes(query)) {
                el.style.opacity = '1';
                el.style.background = '#fef9c3';
            } else {
                el.style.opacity = '0.35';
                el.style.background = '';
            }
        });
    });
</script>

</body>
</html>
