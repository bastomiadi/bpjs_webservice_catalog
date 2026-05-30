<?php
/**
 * Dynamic Footer Component
 * Usage: include 'inc/footer.php';
 * 
 * Variables required:
 * - $showSidebar: whether to show sidebar (default: true)
 * - $modules: array of modules for sidebar (required if $showSidebar is true)
 * - $selectedMod: currently selected module key
 * - $selectedSub: currently selected sub-module key
 */

$showSidebar = $showSidebar ?? true;
?>

<?php if ($showSidebar): ?>
    <!-- ===== SIDEBAR ===== -->
    <aside class="w-72 bg-slate-800 border-r border-slate-700 flex flex-col flex-shrink-0">

        <!-- Search -->
        <div class="p-3 border-b border-slate-700">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">🔍</span>
                <input
                    type="text"
                    id="sidebarSearch"
                    placeholder="Cari modul / endpoint..."
                    class="w-full bg-slate-900 border border-slate-600 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-bpjs-400 focus:ring-1 focus:ring-bpjs-400"
                >
            </div>
        </div>

        <!-- Module List -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll p-2 space-y-1" id="moduleNav">

            <?php if (isset($modules) && is_array($modules)): ?>
                <?php foreach ($modules as $modKey => $mod): ?>
                    <?php
                    $isActive   = ($modKey === $selectedMod);
                    $subCount   = count($mod['sub_modules']);
                    $expanded   = $isActive || $selectedSub !== null;
                    ?>
                    <div class="module-group" data-module="<?= $modKey ?>">

                        <!-- Module Header -->
                        <button
                            type="button"
                            onclick="toggleModule('<?= $modKey ?>')"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors
                                <?= $isActive ? 'bg-bpjs-600 text-white' : 'text-slate-300 hover:bg-slate-700' ?>"
                        >
                            <span class="text-lg flex-shrink-0"><?= $mod['icon'] ?? '📁' ?></span>
                            <span class="flex-1 font-semibold text-sm truncate"><?= htmlspecialchars($mod['label'] ?? $modKey) ?></span>
                            <span class="text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded-full flex-shrink-0"><?= $subCount ?></span>
                            <svg id="arrow-<?= $modKey ?>" class="w-4 h-4 flex-shrink-0 transition-transform <?= $expanded ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Sub-modules -->
                        <div id="subs-<?= $modKey ?>" class="overflow-hidden transition-all duration-200 <?= $expanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0' ?>">
                            <div class="ml-4 mt-1 space-y-0.5 border-l-2 border-slate-700 pl-2 pb-2">

                                <?php if (isset($mod['sub_modules']) && is_array($mod['sub_modules'])): ?>
                                    <?php foreach ($mod['sub_modules'] as $sub): ?>
                                        <?php
                                        $subActive = ($modKey === $selectedMod && $sub['key'] === $selectedSub);
                                        ?>
                                        <a
                                            href="?module=<?= $modKey ?>&sub=<?= $sub['key'] ?>"
                                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors
                                                <?= $subActive
                                                    ? 'bg-bpjs-500/30 text-bpjs-100 border border-bpjs-400/40'
                                                    : 'text-slate-400 hover:bg-slate-700/60 hover:text-slate-200' ?>"
                                        >
                                            <span class="method-<?= strtolower($sub['method']) ?> font-mono font-bold text-[10px] w-10 text-center flex-shrink-0">
                                                <?= $sub['method'] ?>
                                            </span>
                                            <span class="truncate"><?= htmlspecialchars($sub['label']) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </nav>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-slate-700 text-[10px] text-slate-500 text-center">
            BPJS Kesehatan API Catalog v1.0
        </div>
    </aside>
<?php endif; ?>

<!-- ===== MAIN CONTENT ===== -->
<main class="<?= $showSidebar ? '' : 'w-full' ?> flex-1 <?= $showSidebar ? 'overflow-y-auto' : 'overflow-hidden' ?> bg-slate-900">

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
            modInput.value = '<?= htmlspecialchars($selectedMod ?? '') ?>';
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
        document.getElementById('sidebarSearch')?.addEventListener('input', function () {
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
            return 'bpjs_form_' + <?= json_encode($selectedMod ?? '') ?> + '_' + <?= json_encode($selectedSub ?? '') ?>;
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
</main>

</body>
</html>