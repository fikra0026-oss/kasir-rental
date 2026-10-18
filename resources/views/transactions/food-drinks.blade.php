<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Pesan Makanan & Minuman</h2>
                <p class="mt-1 text-sm text-slate-400">Buat pesanan baru atau catat makanan dan minuman dari hari sebelumnya.</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Lihat Riwayat</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ route('food-drinks.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="occurred_at" value="Tanggal & Jam Transaksi" class="text-slate-300" />
                            <x-text-input id="occurred_at" name="occurred_at" type="datetime-local" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required />
                            <p class="mt-2 text-xs text-slate-400">Pilih tanggal kemarin atau sebelumnya untuk mencatat transaksi lampau. Transaksi lampau otomatis selesai dan lunas.</p>
                            <x-input-error :messages="$errors->get('occurred_at')" class="mt-2" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <x-input-label for="playstation_unit_id" value="Pilih Unit PS" class="text-slate-300" />
                                <select id="playstation_unit_id" name="playstation_unit_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                    <option value="">Hanya pesan makanan & minuman</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('playstation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} — {{ ucfirst($unit->status) }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-slate-400">Biarkan kosong jika pelanggan hanya membeli makanan/minuman.</p>
                            </div>
                            <div>
                                <x-input-label for="customer_name" value="Nama Pelanggan" class="text-slate-300" />
                                <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" placeholder="Contoh: Budi" value="{{ old('customer_name') }}" />
                            </div>
                            <div>
                                <x-input-label for="member_id" value="Member (opsional)" class="text-slate-300" />
                                <select id="member_id" name="member_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                    <option value="">Pelanggan Umum</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-700 bg-slate-900/80 p-6">
                            <h3 class="text-lg font-semibold text-white">Daftar Makanan & Minuman</h3>
                            <div class="mt-6 space-y-4">
                                @foreach($products as $product)
                                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-800 bg-slate-950/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-semibold text-white">{{ $product->name }}</p>
                                            <p class="text-sm text-slate-400">{{ ucfirst($product->category) }} • Rp {{ number_format($product->price, 0, ',', '.') }} • Stok: {{ $product->stock }}</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="text-sm text-slate-400">Qty</label>
                                            <input type="number" name="products[{{ $loop->index }}][quantity]" min="0" value="{{ old("products.{$loop->index}.quantity", 0) }}" class="w-24 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-slate-100" />
                                            <input type="hidden" name="products[{{ $loop->index }}][product_id]" value="{{ $product->id }}" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <x-input-label for="payment_method" value="Metode Pembayaran" class="text-slate-300" />
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" required>
                                @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'e-wallet' => 'E-wallet'] as $method => $label)
                                    <option value="{{ $method }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>Simpan Pesanan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
