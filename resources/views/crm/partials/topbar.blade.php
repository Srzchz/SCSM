@if (session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-curema-greensoft text-curema-green text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
    <div>
        <h1 class="text-2xl font-extrabold">CRM Overview</h1>
        <p class="text-sm text-curema-sub">Manage and build stronger relationships with your customers</p>
    </div>

    <div class="flex items-center gap-3">
        <div class="relative hidden md:block">
            <input type="text" id="global-search" placeholder="Search customers, emails, orders, ..."
                   class="w-72 pl-4 pr-10 py-2.5 rounded-xl bg-white border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-curema-sub">🔍</span>
        </div>

        <div class="relative" x-data="{ open: false, filename: '', format: 'excel' }">
            <button type="button" @click="open = !open"
                    class="px-4 py-2.5 rounded-xl bg-white border border-curema-border text-sm font-medium flex items-center gap-2">
                ⭳ Export
            </button>

            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                 class="absolute right-0 mt-2 w-80 bg-curema-card rounded-2xl border border-curema-border shadow-xl z-40 p-5">
                <h3 class="font-semibold text-sm mb-4">Export</h3>

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
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm mb-5 text-curema-sub cursor-not-allowed">

                <div class="flex gap-3">
                    <button type="button" @click="open = false"
                            class="flex-1 py-2.5 rounded-xl border border-curema-border text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="button"
                            @click="Curema.export.download(format, filename); open = false"
                            class="flex-1 py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
                        Export
                    </button>
                </div>
            </div>
        </div>

        <div class="relative"
             x-data="{
                open: false,
                items: [],
                unread: 0,
                refresh() {
                    this.items = Curema.notifications.getAll();
                    this.unread = Curema.notifications.unreadCount();
                },
                markRead(id) {
                    Curema.notifications.markRead(id);
                    this.refresh();
                },
                markAllRead() {
                    Curema.notifications.markAllRead();
                    this.refresh();
                }
             }"
             x-init="refresh()">

            <button type="button" @click="open = !open; if (open) refresh()"
                    class="relative w-10 h-10 rounded-xl bg-white border border-curema-border flex items-center justify-center">
                🔔
                <span x-show="unread > 0" x-cloak
                      class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-curema-coral text-curema-ink text-[10px] font-bold flex items-center justify-center"
                      x-text="unread"></span>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                 class="absolute right-0 mt-2 w-80 bg-curema-card rounded-2xl border border-curema-border shadow-xl z-40 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-curema-border">
                    <h3 class="font-semibold text-sm">Notifications</h3>
                    <button type="button" @click="markAllRead"
                            class="text-xs text-curema-purple font-medium hover:underline">
                        Mark all as read
                    </button>
                </div>

                <ul class="max-h-80 overflow-y-auto divide-y divide-curema-border">
                    <template x-for="n in items" :key="n.id">
                        <li @click="markRead(n.id)"
                            class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-curema-bg transition"
                            :class="!n.read && 'bg-curema-bg/60'">
                            <div class="w-8 h-8 rounded-full bg-curema-purplesoft flex items-center justify-center text-sm shrink-0"
                                 x-text="n.icon"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium leading-tight" x-text="n.title"></p>
                                <p class="text-xs text-curema-sub mt-0.5" x-text="n.note"></p>
                                <p class="text-[11px] text-curema-sub mt-1" x-text="n.time"></p>
                            </div>
                            <span x-show="!n.read" class="w-2 h-2 rounded-full bg-curema-purple shrink-0 mt-1"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <button type="button" @click="ui.addCustomerOpen = true"
                class="px-4 py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold flex items-center gap-2">
            + Add Customer <span class="opacity-70">▾</span>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        Curema.search.bindGlobalTopbarInput(document.getElementById('global-search'));
    });
</script>