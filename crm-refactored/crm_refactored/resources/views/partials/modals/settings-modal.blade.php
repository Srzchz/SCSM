<div x-show="ui.settingsOpen" x-cloak
     x-data="{ settings: Curema.settings.get() }"
     x-init="$watch('ui.settingsOpen', (open) => { if (open) settings = Curema.settings.get() })"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/30" @click="ui.settingsOpen = false"></div>

    <div x-show="ui.settingsOpen" x-transition
         class="relative bg-curema-card rounded-2xl border border-curema-border shadow-xl w-full max-w-sm p-6">
        <h2 class="font-bold text-lg mb-5">Settings</h2>

        <div class="space-y-1">
            <div class="flex items-center justify-between py-3">
                <span class="flex items-center gap-3 text-sm font-medium">🔔 Notifications</span>
                <button type="button"
                        @click="settings.notifications = !settings.notifications; Curema.settings.set('notifications', settings.notifications)"
                        :class="settings.notifications ? 'bg-curema-purple' : 'bg-curema-border'"
                        class="w-11 h-6 rounded-full relative transition">
                    <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all"
                          :class="settings.notifications ? 'left-[22px]' : 'left-0.5'"></span>
                </button>
            </div>

            <div class="flex items-center justify-between py-3 border-t border-curema-border">
                <span class="flex items-center gap-3 text-sm font-medium">🌙 Dark Mode</span>
                <button type="button"
                        @click="settings.darkMode = !settings.darkMode; Curema.settings.set('darkMode', settings.darkMode)"
                        :class="settings.darkMode ? 'bg-curema-purple' : 'bg-curema-border'"
                        class="w-11 h-6 rounded-full relative transition">
                    <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all"
                          :class="settings.darkMode ? 'left-[22px]' : 'left-0.5'"></span>
                </button>
            </div>

            <div class="flex items-center justify-between py-3 border-t border-curema-border">
                <span class="flex items-center gap-3 text-sm font-medium">⏰ Study Reminders</span>
                <button type="button"
                        @click="settings.studyReminders = !settings.studyReminders; Curema.settings.set('studyReminders', settings.studyReminders)"
                        :class="settings.studyReminders ? 'bg-curema-purple' : 'bg-curema-border'"
                        class="w-11 h-6 rounded-full relative transition">
                    <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all"
                          :class="settings.studyReminders ? 'left-[22px]' : 'left-0.5'"></span>
                </button>
            </div>

            <button type="button" @click="alert('Privacy settings — coming soon.')"
                    class="w-full flex items-center justify-between py-3 border-t border-curema-border text-sm font-medium">
                <span class="flex items-center gap-3">🔒 Privacy</span>
                <span class="text-curema-sub">›</span>
            </button>

            <button type="button" @click="alert('Help & Support — coming soon.')"
                    class="w-full flex items-center justify-between py-3 border-t border-curema-border text-sm font-medium">
                <span class="flex items-center gap-3">❓ Help &amp; Support</span>
                <span class="text-curema-sub">›</span>
            </button>

            <button type="button"
                    @click="if (confirm('Log out?')) { ui.settingsOpen = false; window.location.href = '{{ route('dashboard') }}?loggedOut=1'; }"
                    class="w-full flex items-center gap-3 py-3 border-t border-curema-border text-sm font-medium text-red-500">
                ↪ Log Out
            </button>
        </div>
    </div>
</div>