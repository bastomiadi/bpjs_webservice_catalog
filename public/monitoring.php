<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$basePath = __DIR__ . '/..';

require_once $basePath . '/app/Bootstrap.php';
BPJSBootstrap::init($basePath);
date_default_timezone_set('Asia/Jakarta');

$credentials = BPJSBootstrap::getCredentials();
$apiConfig = BPJSBootstrap::getApiConfig();

$consId = $credentials['cons_id'];
$secretKey = $credentials['secret_key'];
$userKey = $credentials['user_key'];
$isDevMode = BPJSBootstrap::getIsDevMode();
$currentDomain = BPJSBootstrap::getCurrentDomain();

$probePaths = [
    'peserta' => '/Peserta/nokartu/{noKartu}/tglSEP/{tglSEP}',
    'rujukan' => '/Rujukan/Peserta/{noKartu}',
    'sep'     => '/SEP/{noSep}',
    'surkon'  => '/RencanaKontrol/noSuratKontrol/{noSurkon}',
];

/*
|--------------------------------------------------------------------------
| AJAX: run single probe
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_probe') {
    header('Content-Type: application/json');
    $now = date('Y-m-d H:i:s');

    $endpoint = $_POST['endpoint'] ?? '';
    $params   = json_decode($_POST['params'] ?? '{}', true);
    if (!is_array($params)) {
        $params = [];
    }

    if (!isset($probePaths[$endpoint])) {
        echo json_encode(['success' => false, 'time' => $now, 'ms' => 0, 'code' => '', 'message' => 'Endpoint tidak dikenal', 'url' => '']);
        exit;
    }

    $path = $probePaths[$endpoint];
    $url  = rtrim(getBaseUrl('vclaim', $currentDomain, $isDevMode), '/') . $path;

    foreach ($params as $k => $v) {
        $url = str_replace('{' . $k . '}', urlencode((string) $v), $url);
    }
    // Hapus placeholder parameter yang tidak diisi agar URL tetap bersih
    // (collaps // hanya di path, jangan di https://)
    $url = preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $url);
    $url = preg_replace('#(?<!:)//+#', '/', $url);

    $start = microtime(true);
    $auth  = BPSignature::generate($consId, $secretKey);

    $resp = BPJSRequest::send([
        'url'       => $url,
        'method'    => 'GET',
        'cons_id'   => $consId,
        'timestamp' => $auth['timestamp'],
        'signature' => $auth['signature'],
        'user_key'  => $userKey,
    ]);

    $ms = (int) round((microtime(true) - $start) * 1000);

    if (!$resp['status']) {
        echo json_encode(['success' => false, 'time' => $now, 'ms' => $ms, 'code' => '', 'message' => $resp['message'] ?? 'Request gagal', 'http_code' => $resp['http_code'] ?? 0, 'url' => $url]);
        exit;
    }

    $data    = $resp['data'] ?? [];
    $code    = (string) ($data['metaData']['code'] ?? '');
    $message = (string) ($data['metaData']['message'] ?? '');

    // SUKSES = request sampai ke server (menerima respons apa pun: JSON API atau HTML).
    // FAIL = request tidak sampai (timeout / koneksi gagal).
    $success = true;

    $raw = $resp['raw_response'] ?? '';
    if (stripos($raw, '<html') !== false || stripos($raw, '<!DOCTYPE') !== false) {
        $message = 'Sampai ke server, tapi respons bukan JSON API (HTML, HTTP ' . ($resp['http_code'] ?? '-') . ')';
    }

    echo json_encode([
        'success' => $success,
        'time'    => $now,
        'ms'      => $ms,
        'code'    => $code,
        'message' => $message,
        'http_code' => $resp['http_code'] ?? '',
        'url'     => $url,
    ]);
    exit;
}

$pageTitle = 'Monitoring Bridging';
$showConsId = false;
$showModeSelector = false;
$showVersionSelector = false;
$activeNav = 'monitoring';
include __DIR__ . '/inc/header.php';
?>
<script>
    const API_VERSION_COOKIE = 'bpjs_api_version';
    const DEV_MODE_COOKIE = 'bpjs_dev_mode';

    const probes = [
        { key: 'peserta', name: 'Get Peserta', desc: 'Peserta by No Kartu' },
        { key: 'rujukan', name: 'Get Rujukan', desc: 'Rujukan by No Kartu' },
        { key: 'sep', name: 'Get SEP', desc: 'Detail SEP by No SEP' },
        { key: 'surkon', name: 'Get Surkon', desc: 'Surat Kontrol by No Surkon' },
    ];

    function getCookie(name) {
        const m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? m.pop() : '';
    }

    function setCookie(name, value) {
        const d = new Date();
        d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    }

    const state = {
        timerId: null,
        running: false,
        logs: {},      // key -> array of {time, success, ms, code, message}
        lastPreview: {}, // key -> preview text
    };

    function logLine(key, entry) {
        if (!state.logs[key]) state.logs[key] = [];
        state.logs[key].unshift(entry);
        if (state.logs[key].length > 50) state.logs[key].length = 50;
        renderLog(key);
    }

    function renderLog(key) {
        const card = document.getElementById('card-' + key);
        const list = card.querySelector('.probe-log');
        const rows = (state.logs[key] || []).map(e => {
            const cls = e.success ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200';
            return `<tr class="border-b border-gray-100 text-xs">
                <td class="px-3 py-2 font-mono whitespace-nowrap">${e.time}</td>
                <td class="px-3 py-2"><span class="inline-block px-2 py-0.5 rounded-full border font-semibold ${cls}">${e.success ? 'SUKSES' : 'FAIL'}</span></td>
                <td class="px-3 py-2 font-mono">${e.ms} ms</td>
                <td class="px-3 py-2 font-mono">${e.code || '-'}</td>
                <td class="px-3 py-2 text-gray-600 max-w-[280px] truncate" title="${escapeHtml(e.message)}">${escapeHtml(e.message)}</td>
            </tr>`;
        }).join('');
        list.innerHTML = rows || '<tr><td colspan="5" class="px-3 py-4 text-center text-gray-400">Belum ada request</td></tr>';
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderCard(cfg) {
        const card = document.createElement('div');
        card.id = 'card-' + cfg.key;
        card.className = 'bg-white border border-gray-200 rounded-xl shadow-sm';
        card.innerHTML = `
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900">${cfg.name}</h3>
                    <p class="text-sm text-gray-500">${cfg.desc}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="probe-dot w-3 h-3 rounded-full bg-gray-300 inline-block"></span>
                    <span class="probe-status text-xs font-semibold text-gray-500">IDLE</span>
                </div>
            </div>
            <div class="p-5 space-y-3">
                <div class="text-xs text-gray-500">
                    <span class="stat-total font-semibold text-gray-700">0</span> total ·
                    <span class="stat-ok font-semibold text-green-600">0</span> sukses ·
                    <span class="stat-fail font-semibold text-red-600">0</span> fail
                </div>
            </div>
            <div class="border-t border-gray-100">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wide text-gray-400 bg-gray-50">
                            <th class="px-3 py-2">Waktu</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Durasi</th>
                            <th class="px-3 py-2">Code</th>
                            <th class="px-3 py-2">Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="probe-log"></tbody>
                </table>
            </div>`;
        document.getElementById('probeCards').appendChild(card);
        renderLog(cfg.key);
    }

    function updateCardStatus(key, success, ms) {
        const card = document.getElementById('card-' + key);
        const dot = card.querySelector('.probe-dot');
        const status = card.querySelector('.probe-status');
        const stats = (state.logs[key] || []);
        const total = stats.length;
        const ok = stats.filter(e => e.success).length;
        card.querySelector('.stat-total').textContent = total;
        card.querySelector('.stat-ok').textContent = ok;
        card.querySelector('.stat-fail').textContent = total - ok;
        dot.className = 'probe-dot w-3 h-3 rounded-full inline-block ' + (success ? 'bg-green-500' : 'bg-red-500');
        status.textContent = (success ? 'SUKSES · ' : 'FAIL · ') + ms + ' ms';
        status.className = 'probe-status text-xs font-semibold ' + (success ? 'text-green-600' : 'text-red-600');
    }

    async function runProbe(key) {
        const cfg = probes.find(p => p.key === key);
        if (!cfg) return;
        const card = document.getElementById('card-' + key);
        card.querySelector('.probe-dot').className = 'probe-dot w-3 h-3 rounded-full inline-block bg-amber-400 animate-pulse';
        card.querySelector('.probe-status').textContent = 'PROSES...';

        const fd = new FormData();
        fd.append('action', 'run_probe');
        fd.append('endpoint', key);
        fd.append('params', '{}');

        try {
            const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
            const data = await res.json();
            logLine(key, { time: data.time, success: !!data.success, ms: data.ms, code: data.code, message: data.message || 'OK' });
            updateCardStatus(key, !!data.success, data.ms);
        } catch (err) {
            logLine(key, { time: new Date().toLocaleString('id-ID'), success: false, ms: 0, code: '', message: 'Fetch gagal: ' + err.message });
            updateCardStatus(key, false, 0);
        }
    }

    function runAllProbes() {
        probes.forEach(p => runProbe(p.key));
    }

    function intervalMs() {
        const val = parseInt(document.getElementById('intervalValue').value, 10) || 0;
        const unit = document.getElementById('intervalUnit').value;
        const ms = unit === 'menit' ? val * 60000 : val * 1000;
        return Math.max(ms, 1000);
    }

    function startMonitoring() {
        stopMonitoring();
        state.timerId = setInterval(runAllProbes, intervalMs());
        state.running = true;
        runAllProbes();
        updateControls();
    }

    function stopMonitoring() {
        if (state.timerId) clearInterval(state.timerId);
        state.timerId = null;
        state.running = false;
        updateControls();
    }

    function updateControls() {
        const btn = document.getElementById('btnToggle');
        const badge = document.getElementById('monitorStatus');
        if (state.running) {
            btn.textContent = 'Stop';
            btn.className = 'px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors';
            badge.textContent = 'Monitoring Aktif';
            badge.className = 'text-sm font-semibold text-green-600';
        } else {
            btn.textContent = 'Start Monitoring';
            btn.className = 'px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 transition-colors';
            badge.textContent = 'Monitoring Nonaktif';
            badge.className = 'text-sm font-semibold text-gray-500';
        }
    }

    function changeApiVersion(v) {
        setCookie(API_VERSION_COOKIE, v);
        document.getElementById('apiVersion').value = v;
    }

    function changeDevMode(checked) {
        setCookie(DEV_MODE_COOKIE, checked ? 'true' : 'false');
        const label = document.getElementById('devModeLabel');
        label.textContent = checked ? 'DEV' : 'PROD';
        label.className = 'ml-2.5 text-sm font-semibold ' + (checked ? 'text-orange-500' : 'text-gray-400');
    }

    document.addEventListener('DOMContentLoaded', () => {
        probes.forEach(renderCard);
        updateControls();

        const saved = localStorage.getItem('bpjs_monitor_interval');
        if (saved) {
            const [value, unit] = saved.split(':');
            document.getElementById('intervalValue').value = value;
            document.getElementById('intervalUnit').value = unit;
        }

        const version = getCookie(API_VERSION_COOKIE) || 'v1';
        document.getElementById('apiVersion').value = version;
        document.getElementById('devMode').checked = getCookie(DEV_MODE_COOKIE) === 'true';
        changeDevMode(document.getElementById('devMode').checked);

        document.getElementById('btnToggle').addEventListener('click', () => {
            localStorage.setItem('bpjs_monitor_interval', document.getElementById('intervalValue').value + ':' + document.getElementById('intervalUnit').value);
            if (state.running) stopMonitoring(); else startMonitoring();
        });
        document.getElementById('btnRunNow').addEventListener('click', runAllProbes);
        document.getElementById('btnClearLogs').addEventListener('click', () => {
            probes.forEach(p => { state.logs[p.key] = []; renderLog(p.key); });
        });
    });
</script>

<main class="flex-1 p-6 bg-gray-50 overflow-y-auto">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Settings Panel -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">API Version</p>
                    <select id="apiVersion" onchange="changeApiVersion(this.value)" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-400">
                        <option value="v1">V1</option>
                        <option value="v2">V2</option>
                    </select>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Mode</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="devMode" onchange="changeDevMode(this.checked)" class="sr-only peer">
                        <div class="w-12 h-7 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-500"></div>
                        <span class="ml-2.5 text-sm font-semibold text-gray-400" id="devModeLabel">PROD</span>
                    </label>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Interval Request</p>
                    <div class="flex items-center gap-2">
                        <input type="number" id="intervalValue" value="30" min="1" class="w-24 bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-400">
                        <select id="intervalUnit" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-400">
                            <option value="detik">Detik</option>
                            <option value="menit">Menit</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3 ml-auto">
                    <span id="monitorStatus" class="text-sm font-semibold text-gray-500">Monitoring Nonaktif</span>
                    <button id="btnRunNow" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Run Sekarang</button>
                    <button id="btnClearLogs" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Bersihkan Log</button>
                    <button id="btnToggle" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 transition-colors">Start Monitoring</button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Monitoring mengirim request kosong ke 4 endpoint VClaim secara paralel setiap interval, tanpa perlu mengisi form. SUKSES = request sampai ke server BPJS dan menerima respons API. FAIL = request tidak sampai (timeout / error koneksi / server tidak merespons).</p>
        </div>

        <!-- Probe Cards -->
        <div id="probeCards" class="grid grid-cols-1 lg:grid-cols-2 gap-6"></div>
    </div>
</main>

</body>
</html>
