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
 * 
 */

$showSidebar = $showSidebar ?? true;
?>

<?php if ($showSidebar): ?>
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

            <?php if (isset($modules) && is_array($modules)): ?>
                <?php foreach ($modules as $modKey => $mod): ?>
                    <?php
                    $isActive   = ($modKey === $selectedMod);
                    $subCount   = count($mod['sub_modules']);
                    $expanded   = $isActive || $selectedSub !== null;
                    $modIcon = $mod['icon'] ?? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m6 8V9m-6 8h6"/></svg>';
                    $modLabel = $mod['label'] ?? $modKey;
                    ?>
                    <div class="module-group" data-module="<?= $modKey ?>">

                        <!-- Module Header -->
                        <button
                            type="button"
                            onclick="toggleModule('<?= $modKey ?>')"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-all duration-200
                                <?= $isActive 
                                    ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' 
                                    : 'text-gray-700 hover:bg-gray-100' ?>"
                        >
                            <span class="text-lg flex-shrink-0"><?= is_string($modIcon) ? $modIcon : $modIcon ?></span>
                            <span class="flex-1 font-semibold text-sm truncate"><?= htmlspecialchars($modLabel) ?></span>
                            <span class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-full flex-shrink-0"><?= $subCount ?></span>
                            <svg id="arrow-<?= $modKey ?>" class="w-4 h-4 flex-shrink-0 transition-transform <?= $expanded ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Sub-modules -->
                        <div id="subs-<?= $modKey ?>" class="overflow-hidden transition-all duration-200 <?= $expanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0' ?>">
                            <div class="ml-4 mt-1 space-y-0.5 border-l-2 border-gray-200 pl-2 pb-2">

                                <?php if (isset($mod['sub_modules']) && is_array($mod['sub_modules'])): ?>
                                    <?php foreach ($mod['sub_modules'] as $sub): ?>
                                        <?php
                                        $subActive = ($modKey === $selectedMod && $sub['key'] === $selectedSub);
                                        ?>
                                        <a
                                            href="?module=<?= $modKey ?>&sub=<?= $sub['key'] ?>"
                                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition-all duration-200
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
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </nav>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-gray-200 text-[10px] text-gray-500 text-center">
            BPJS Kesehatan API Catalog v1.0
        </div>
    </aside>
<?php endif; ?>

<!-- ===== MAIN CONTENT ===== -->
<main class="<?= $showSidebar ? '' : 'w-full' ?> flex-1 <?= $showSidebar ? 'overflow-y-auto' : 'overflow-hidden' ?> bg-white">

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