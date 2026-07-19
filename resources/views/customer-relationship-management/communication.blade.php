@extends('layouts.app')

@section('title', $customer['full_name'] . ' - Communication')

@php
    $active = 'Customers';
    $activeTab = 'communication';
@endphp

@section('content')

    @include('partials.topbar')

    @include('customer-relationship-management.partials.profile-header', ['customer' => $customer, 'activeTab' => $activeTab])

    {{-- Data lives in its own script tag, not inline in an HTML attribute —
         this avoids any conflict between JSON's double quotes and the
         x-data="..." attribute's own double-quote delimiters. --}}
    <script type="application/json" id="comm-logs-data">@json($customer['communication_logs'])</script>

    <div class="grid grid-cols-1 xl:grid-cols-[1.7fr_1fr] gap-4 mb-4" x-data="commPage()">

        <div class="bg-curema-card rounded-2xl border border-curema-border p-5">
            <h2 class="font-semibold mb-4">Communication History</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-curema-sub text-xs">
                            <th class="font-medium pb-3">Ticket ID</th>
                            <th class="font-medium pb-3">Issue</th>
                            <th class="font-medium pb-3">Date</th>
                            <th class="font-medium pb-3">Mode</th>
                            <th class="font-medium pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="log in logs" :key="log.ticket_id">
                            <tr class="border-t border-curema-border cursor-pointer hover:bg-curema-bg/60 transition"
                                :class="selected && selected.ticket_id === log.ticket_id && 'bg-curema-bg/60'"
                                @click="selected = log">
                                <td class="py-3 font-medium text-curema-purple" x-text="log.ticket_id"></td>
                                <td x-text="log.issue"></td>
                                <td x-text="log.date"></td>
                                <td x-text="log.mode"></td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                          :class="log.status === 'Resolved' ? 'bg-curema-greensoft text-curema-green' : 'bg-curema-bluesoft text-curema-blue'"
                                          x-text="log.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-curema-card rounded-2xl border border-curema-border p-5 flex flex-col h-[480px]">
            <div class="mb-3">
                <h2 class="font-semibold">Chats</h2>
                <p class="text-xs text-curema-sub" x-show="selected" x-text="selected ? selected.ticket_id + ' — ' + selected.issue : ''"></p>
                <p class="text-xs text-curema-sub" x-show="!selected">No ticket selected</p>
            </div>

            <div x-ref="chatScroll" class="flex-1 space-y-3 mb-3 overflow-y-auto pr-1">
                <template x-for="msg in selectedMessages()" :key="msg.time + msg.text">
                    <div class="flex" :class="msg.from === 'agent' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[80%] rounded-2xl px-3.5 py-2 text-sm"
                             :class="msg.from === 'agent' ? 'bg-curema-purple text-white' : 'bg-curema-bg text-curema-ink'">
                            <p x-text="msg.text"></p>
                            <p class="text-[10px] mt-1 opacity-70" x-text="msg.time"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-2 border-t border-curema-border pt-3">
                <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                       :disabled="!selected"
                       placeholder="Type a message..."
                       class="flex-1 px-3 py-2 rounded-xl bg-curema-bg text-sm border border-curema-border focus:outline-none focus:ring-2 focus:ring-curema-purple/40 disabled:opacity-50">
                <button type="button" @click="sendMessage" :disabled="!selected"
                        class="w-9 h-9 rounded-xl bg-curema-purple text-white flex items-center justify-center shrink-0 disabled:opacity-50">
                    ➤
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function commPage() {
        return {
            logs: JSON.parse(document.getElementById('comm-logs-data').textContent),
            selected: null,
            newMessage: '',
            autoReplies: [
                'Got it, let me check that for you.',
                'Thanks for the update! Give me a moment.',
                "I understand — I'll look into this right away.",
                'Appreciate your patience, checking now.',
                "Noted! I'll follow up shortly.",
                "Sure thing, one sec while I pull that up."
            ],
            init() {
                const params = new URLSearchParams(window.location.search);
                const ticketParam = params.get('ticket');
                this.selected = this.logs.find(l => l.ticket_id === ticketParam) || this.logs[0] || null;
            },
            selectedMessages() {
                return this.selected ? this.selected.chats : [];
            },
            sendMessage() {
                if (!this.newMessage.trim() || !this.selected) return;
                const time = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });

                this.selected.chats.push({ from: 'customer', text: this.newMessage, time });
                this.newMessage = '';
                this.$nextTick(() => this.scrollChatToBottom());

                setTimeout(() => {
                    const reply = this.autoReplies[Math.floor(Math.random() * this.autoReplies.length)];
                    const replyTime = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                    this.selected.chats.push({ from: 'agent', text: reply, time: replyTime });
                    this.$nextTick(() => this.scrollChatToBottom());
                }, 900);
            },
            scrollChatToBottom() {
                const el = this.$refs.chatScroll;
                if (el) el.scrollTop = el.scrollHeight;
            }
        };
    }
</script>
@endpush