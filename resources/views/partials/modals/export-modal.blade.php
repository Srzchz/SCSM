<div x-show="ui.exportOpen" x-cloak
     x-data="{ filename: '', format: 'excel' }"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/30" @click="ui.exportOpen = false"></div>

    <div x-show="ui.exportOpen" x-transition
         class="relative bg-curema-card rounded-2xl border border-curema-border shadow-xl w-full max-w-sm p-6">
        <h2 class="text-center font-bold text-lg mb-5">Export</h2>

        <label class="block text-xs font-medium text-curema-sub mb-1.5">File Name</label>
        <input type="text" x-model="filename" placeholder="file name here...."
               class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm mb-4
                      focus:outline-none focus:ring-2 focus:ring-curema-purple/40">

        <label class="block text-xs font-medium text-curema-sub mb-1.5">Export to</label>
        <div class="grid grid-cols-3 gap-2 mb-4">
            <button type="button" @click="format = 'word'"
                    :class="format === 'word' ? 'border-curema-purple bg-curema-purplesoft' : 'border-curema-border bg-curema-bg'"
                    class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl border text-sm font-medium">
                📄 Word
            </button>
            <button type="button" @click="format = 'excel'"
                    :class="format === 'excel' ? 'border-curema-purple bg-curema-purplesoft' : 'border-curema-border bg-curema-bg'"
                    class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl border text-sm font-medium">
                📊 Excel
            </button>
            <button type="button" @click="format = 'pdf'"
                    :class="format === 'pdf' ? 'border-curema-purple bg-curema-purplesoft' : 'border-curema-border bg-curema-bg'"
                    class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl border text-sm font-medium">
                📕 PDF
            </button>
        </div>

        <label class="block text-xs font-medium text-curema-sub mb-1.5">File Location</label>
        <input type="text" value="Downloads (browser default)" disabled
               title="Browsers can't choose a save path via JavaScript — files land in your default Downloads folder."
               class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm mb-6 text-curema-sub cursor-not-allowed">

        <div class="flex gap-3">
            <button type="button" @click="ui.exportOpen = false"
                    class="flex-1 py-2.5 rounded-xl border border-curema-border text-sm font-semibold">
                Cancel
            </button>
            <button type="button"
                    @click="Curema.export.download(format, filename); ui.exportOpen = false"
                    class="flex-1 py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
                Export
            </button>
        </div>
    </div>
</div>