<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Transaksi Kasir</h2>
                <p class="mt-1 text-sm text-slate-400">Buat transaksi rental PS baru atau catat transaksi dari hari sebelumnya.</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Riwayat Transaksi</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="occurred_at" value="Tanggal & Jam Transaksi" class="text-slate-300" />
                            <x-text-input id="occurred_at" name="occurred_at" type="datetime-local" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required />
                            <p class="mt-2 text-xs text-slate-400">Pilih tanggal kemarin atau sebelumnya untuk mencatat transaksi lampau. Transaksi lampau otomatis selesai dan lunas.</p>
                            <x-input-error :messages="$errors->get('occurred_at')" class="mt-2" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="playstation_unit_id" value="Pilih Unit PS" class="text-slate-300" />
                                <select id="playstation_unit_id" name="playstation_unit_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('playstation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} — {{ $unit->type }} ({{ ucfirst($unit->status) }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('playstation_unit_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="package_id" value="Pilih Paket" class="text-slate-300" />
                                <select id="package_id" name="package_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                    <option value="">Pilih paket (optional)</option>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>{{ $package->name }} — Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="manual_minutes" value="Input Jam Manual (menit)" class="text-slate-300" />
                                <x-text-input id="manual_minutes" name="manual_minutes" type="number" class="mt-1 block w-full" value="{{ old('manual_minutes') }}" />
                                <x-input-error :messages="$errors->get('manual_minutes')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="member_id" value="Pilih Member" class="text-slate-300" />
                                <select id="member_id" name="member_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                    <option value="">Pelanggan Umum</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} • {{ ucfirst($member->level) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-700 bg-slate-900/80 p-6">
                            <h3 class="text-lg font-semibold text-white">Makanan & Minuman</h3>
                            <p class="mt-1 text-sm text-slate-400">Tambah item yang dibeli oleh pelanggan.</p>
                            <div class="mt-6 space-y-4">
                                @for($index = 0; $index < 3; $index++)
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <select name="products[{{ $index }}][product_id]" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                            <option value="">Pilih item</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ old("products.{$index}.product_id") == $product->id ? 'selected' : '' }}>{{ $product->name }} — Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                                            @endforeach
                                        </select>
                                        <x-text-input name="products[{{ $index }}][quantity]" type="number" min="1" placeholder="Jumlah" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" value="{{ old("products.{$index}.quantity") }}" />
                                        <div class="flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200">Stok tersedia</div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <x-input-label for="payment_method" value="Metode Pembayaran" class="text-slate-300" />
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" required>
                                @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'e-wallet' => 'E-wallet'] as $method => $label)
                                    <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>Simpan Transaksi</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
