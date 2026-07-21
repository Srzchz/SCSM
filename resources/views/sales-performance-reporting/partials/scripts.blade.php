{{--
    Shared behavior for the Sales Performance Reporting pages' hand-rolled
    widgets (.select dropdowns, .modal-overlay popups, .toggle-switch
    buttons). These pages' markup calls toggleSelect()/initSelect()/
    openModal()/closeModal()/toggleSetting() via inline onclick handlers,
    which used to be defined in this module's own standalone layout. Now
    that the pages render through the shared shell (layouts.app) instead,
    that layout is no longer included, so these helpers need to be pushed
    in directly. Defined with `typeof ... === 'undefined'` guards since
    this partial may be included by more than one page-level @push in the
    same response.
--}}
<script>
    if (typeof window.toggleSelect === 'undefined') {
        window.toggleSelect = function (btn) {
            const el = btn.closest('.select');
            const wasOpen = el.classList.contains('open');
            document.querySelectorAll('.select.open').forEach(function (s) { s.classList.remove('open'); });
            if (!wasOpen) el.classList.add('open');
        };

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.select')) {
                document.querySelectorAll('.select.open').forEach(function (s) { s.classList.remove('open'); });
            }
        });

        window.initSelect = function (selectEl, onChange) {
            if (!selectEl) return;
            selectEl.querySelectorAll('.select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    selectEl.querySelectorAll('.select-option').forEach(function (o) { o.classList.remove('selected'); });
                    this.classList.add('selected');
                    selectEl.querySelector('.select-value').textContent = this.textContent.trim();
                    selectEl.dataset.value = this.dataset.value;
                    selectEl.classList.remove('open');
                    if (onChange) onChange(this.dataset.value, this.textContent.trim());
                });
            });
        };

        window.openModal = function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('open');
            document.body.style.overflow = 'hidden';
        };

        window.closeModal = function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('open');
            document.body.style.overflow = '';
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.open').forEach(function (m) { closeModal(m.id); });
            }
        });

        window.toggleSetting = function (btn) {
            const isActive = btn.classList.contains('active');
            btn.classList.toggle('active', !isActive);
            btn.classList.toggle('inactive', isActive);
            btn.textContent = isActive ? 'Inactive' : 'Active';
        };
    }
</script>
