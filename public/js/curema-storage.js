/**
 * Curema — shared client-side utilities referenced across Blade views.
 *
 * Loaded via: <script src="{{ asset('js/curema-storage.js') }}"></script>
 * in resources/views/layouts/app.blade.php.
 *
 * Place this file at: public/js/curema-storage.js
 *
 * Namespaces implemented here, matched to the exact call sites found in the
 * codebase:
 *   - Curema.settings.get()                <- settings-modal.blade.php (x-data init + $watch)
 *   - Curema.settings.set(key, value)      <- settings-modal.blade.php (each toggle's @click)
 *   - Curema.settings.applyDarkMode()      <- layouts.app x-init
 *   - Curema.customers.renderExtraRows()   <- partials/customer-table.blade.php
 *   - Curema.export.download(format, name) <- partials/topbar.blade.php Export button
 */
(function (window) {
    'use strict';

    const STORAGE_KEY = 'curema.settings';

    const DEFAULT_SETTINGS = {
        notifications: true,
        darkMode: false,
        studyReminders: true
    };

    // ---------------------------------------------------------------
    // Curema.settings — one JSON object in localStorage, keyed by name
    // ---------------------------------------------------------------
    const settings = {
        // Returns the full settings object (defaults merged with whatever's
        // stored), so settings-modal.blade.php's toggles always have a
        // notifications/darkMode/studyReminders key to read/flip.
        get() {
            try {
                const stored = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
                return { ...DEFAULT_SETTINGS, ...stored };
            } catch (e) {
                return { ...DEFAULT_SETTINGS };
            }
        },

        // Persists a single key back into that same object.
        set(key, value) {
            const current = settings.get();
            current[key] = value;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(current));

            if (key === 'darkMode') {
                settings.applyDarkMode();
            }
        },

        // Called on every page load (layouts.app x-init) to reapply whatever
        // the user last chose in the Settings modal.
        applyDarkMode() {
            document.documentElement.classList.toggle('curema-dark', settings.get().darkMode === true);
        }
    };

    // ---------------------------------------------------------------
    // Curema.customers — table helpers
    // ---------------------------------------------------------------
    const customers = {
        // customer-table.blade.php already renders every row server-side via
        // @foreach, so there are no "extra" rows left to inject. Kept as a
        // safe no-op purely so the existing DOMContentLoaded call in that
        // partial stops throwing in the console. If this was meant to do
        // something (e.g. client-side row highlighting using badgeClasses),
        // let me know and I'll build that out for real.
        renderExtraRows(tableId, badgeClasses) {
            // intentionally empty
        }
    };

    // ---------------------------------------------------------------
    // Curema.export — Word / Excel / PDF export of the visible table
    // ---------------------------------------------------------------
    const exportUtil = {
        download(format, filename) {
            const table = document.querySelector('table[data-export="true"]');
            if (!table) {
                console.warn('Curema.export.download: no table[data-export="true"] found on this page.');
                return;
            }

            const safeName = (filename && filename.trim()) ? filename.trim() : 'export';

            switch (format) {
                case 'excel':
                    return exportUtil.toExcel(table, safeName);
                case 'pdf':
                    return exportUtil.toPdf(table, safeName);
                case 'word':
                    return exportUtil.toWord(table, safeName);
                default:
                    console.warn(`Curema.export.download: unknown format "${format}"`);
            }
        },

        toExcel(table, filename) {
            // SheetJS, already loaded via CDN in layouts.app
            const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
            XLSX.writeFile(wb, `${filename}.xlsx`);
        },

        toPdf(table, filename) {
            // jsPDF + jspdf-autotable, already loaded via CDN in layouts.app
            const doc = new window.jspdf.jsPDF();
            doc.autoTable({ html: table });
            doc.save(`${filename}.pdf`);
        },

        toWord(table, filename) {
            // No docx-generation library is loaded in this app. Word will
            // happily open an .doc file whose content is plain HTML wrapped
            // in the Office XML namespaces below — this is the standard
            // no-dependency trick for "export to Word" from the browser.
            const html = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office"
                      xmlns:w="urn:schemas-microsoft-com:office:word"
                      xmlns="http://www.w3.org/TR/REC-html40">
                <head><meta charset="utf-8"><title>${filename}</title></head>
                <body>${table.outerHTML}</body>
                </html>`;

            const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${filename}.doc`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    };

    window.Curema = { settings, customers, export: exportUtil };

})(window);