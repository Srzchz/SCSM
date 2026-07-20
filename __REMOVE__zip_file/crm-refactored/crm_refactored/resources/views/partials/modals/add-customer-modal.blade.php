<div x-show="ui.addCustomerOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/30" @click="ui.addCustomerOpen = false"></div>

    <div x-show="ui.addCustomerOpen" x-transition
         class="relative bg-curema-card rounded-2xl border border-curema-border shadow-xl w-full max-w-sm p-6">
        <h2 class="text-center font-bold text-lg">Add Customer Membership</h2>
        <p class="text-center text-xs text-curema-sub mt-2 mb-5">
            Add customers to your membership list effortlessly, keeping your connection organized and relationship strong.
        </p>
        <div class="border-t border-curema-border mb-5"></div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl bg-curema-coral/40 text-sm text-curema-ink">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-curema-sub mb-1.5">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Juan Dela Cruz"
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                              focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            </div>
            <div>
                <label class="block text-xs font-medium text-curema-sub mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="juandelacruz@gmail.com"
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                              focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            </div>
            <div>
                <label class="block text-xs font-medium text-curema-sub mb-1.5">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="Complete Address"
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                              focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            </div>
            <div>
                <label class="block text-xs font-medium text-curema-sub mb-1.5">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Complete Phone Number"
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                              focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            </div>
            <div>
                <label class="block text-xs font-medium text-curema-sub mb-1.5">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                              focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" @click="ui.addCustomerOpen = false"
                        class="flex-1 py-2.5 rounded-xl border border-curema-border text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
                    Add Customer
                </button>
            </div>
        </form>
    </div>
</div>