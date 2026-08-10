<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Riwayat Transaksi</h2>
                <p class="mt-1 text-sm text-slate-400">Lihat catatan transaksi rental, makanan, dan pembayaran.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('food-drinks') }}" class="inline-flex items-center rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-200 transition hover:bg-cyan-500/20">Pesan Makanan & Minuman</a>
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Transaksi Baru</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 text-white">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 text-white">
            @include('partials.alerts')

            @php
                $totalTransactions = $transactions->count();
                $runningTransactions = $transactions->where('status', 'berjalan')->count();
                $lunasTransactions = $transactions->where('payment_status', 'lunas')->count();
                $totalOmzet = $transactions->sum('total');
            @endphp

            <div class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-[24px] border border-slate-800 bg-slate-900/90 p-5 shadow-lg shadow-cyan-950/20">
                    <div class="text-sm uppercase tracking-[0.2em] text-slate-500">Total Transaksi</div>
                    <div class="mt-2 text-3xl font-semibold text-white">{{ number_format($totalTransactions) }}</div>
                </div>
               
                <div class="rounded-[24px] border border-slate-800 bg-slate-900/90 p-5 shadow-lg shadow-cyan-950/20">
                    <div class="text-sm uppercase tracking-[0.2em] text-slate-500">Total Omzet</div>
                    <div class="mt-2 text-3xl font-semibold text-amber-300">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-[30px] border border-slate-800 bg-slate-950/90 p-4 shadow-2xl shadow-cyan-500/5 sm:p-6">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Daftar Transaksi</h3>
                        <p class="text-sm text-slate-400">Pantau status, pembayaran, dan item pesanan dari satu tampilan.</p>
                    </div>
                    <div class="rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-400">
                        {{ $totalTransactions }} transaksi
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($transactions as $transaction)
                        @php
                            $transactionStatus = match($transaction->status) {
                                'berjalan' => ['label' => 'Berjalan', 'class' => 'bg-cyan-500/15 text-cyan-300'],
                                'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-500/15 text-emerald-300'],
                                default => ['label' => ucfirst($transaction->status), 'class' => 'bg-slate-700/70 text-slate-300'],
                            };

                            $paymentStatus = match($transaction->payment_status ?? 'belum_bayar') {
                                'lunas' => ['label' => 'Lunas', 'class' => 'bg-emerald-500/15 text-emerald-300'],
                                'pending' => ['label' => 'Pending', 'class' => 'bg-amber-500/15 text-amber-300'],
                                default => ['label' => 'Belum Bayar', 'class' => 'bg-amber-500/15 text-amber-300'],
                            };
                        @endphp

                        <article class="rounded-[24px] border border-slate-800 bg-slate-900/80 p-4 shadow-sm shadow-slate-950/20 sm:p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">
                                            {{ $transaction->transaction_number }}
                                        </span>
                                        <span class="text-sm text-slate-400">
                                            {{ \Illuminate\Support\Carbon::parse($transaction->started_at ?? $transaction->created_at)->setTimezone(config('app.timezone', 'Asia/Makassar'))->translatedFormat('d M Y H:i') }}
                                        </span>
                                    </div>

                                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <div>
                                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Unit PS</div>
                                            <div class="mt-1 font-semibold text-white">{{ $transaction->unit->name ?? 'N/A' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Pelanggan</div>
                                            <div class="mt-1 font-semibold text-white">{{ $transaction->customer_name }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Total</div>
                                            <div class="mt-1 font-semibold text-cyan-300">Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Jenis</div>
                                            <div class="mt-1 font-semibold text-white">{{ $transaction->playstation_unit_id ? 'Rental PS' : 'Makanan & Minuman' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start gap-2 lg:items-end">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs uppercase tracking-[0.2em] {{ $transactionStatus['class'] }}">
                                            {{ $transactionStatus['label'] }}
                                        </span>
                                        <span class="rounded-full px-2.5 py-1 text-xs uppercase tracking-[0.2em] {{ $paymentStatus['class'] }}">
                                            {{ $paymentStatus['label'] }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        @if($transaction->status === 'berjalan')
                                            @if($transaction->playstation_unit_id)
                                                <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="package_id" class="rounded-lg border border-slate-700 bg-slate-950 px-2.5 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none">
                                                        <option value="">Pilih paket</option>
                                                        @foreach(App\Models\RentalPackage::orderBy('duration_minutes')->get() as $package)
                                                            <option value="{{ $package->id }}" {{ ($transaction->package_id ?? null) == $package->id ? 'selected' : '' }}>{{ $package->name }} ({{ $package->duration_minutes }} menit)</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-2 text-sm font-medium text-cyan-300 transition hover:bg-cyan-500/20">Edit Paket</button>
                                                </form>
                                                <form action="{{ route('transactions.finish', $transaction) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="rounded-full border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-medium text-slate-200 transition hover:border-cyan-500/30 hover:text-cyan-300">Selesaikan</button>
                                                </form>
                                            @endif
                                        @endif

                                            @if(($transaction->payment_status ?? 'belum_bayar') !== 'lunas')
                                                <form action="{{ route('transactions.complete-payment', $transaction) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="rounded-full bg-emerald-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-400">Selesaikan Bayar</button>
                                                </form>
                                            @endif

                                        @if($transaction->isDeletable())
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm font-medium text-rose-300 transition hover:bg-rose-500/20">Hapus</button>
                                            </form>
                                        @endif

                                        @if(! $transaction->isDeletable() && $transaction->status !== 'berjalan' && ($transaction->payment_status ?? 'belum_bayar') === 'lunas')
                                            <span class="text-sm text-slate-400">Tidak ada aksi yang tersedia</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($transaction->items->isNotEmpty())
                                <div class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-white">Item Pesanan</h4>
                                        <span class="text-xs uppercase tracking-[0.12em] text-slate-400">Makanan & Minuman</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm text-slate-300">
                                            <thead>
                                                <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-[0.12em] text-slate-500">
                                                    <th class="py-2 pr-4">Jenis</th>
                                                    <th class="py-2 pr-4">Nama</th>
                                                    <th class="py-2 pr-4">Qty</th>
                                                    <th class="py-2 pr-4">Harga Satuan</th>
                                                    <th class="py-2 pr-4">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transaction->items as $item)
                                                    @if(in_array($item->type, ['food', 'drink']))
                                                        <tr class="border-b border-slate-800/70">
                                                            <td class="py-2 pr-4">{{ $item->type === 'drink' ? 'Minuman' : 'Makanan' }}</td>
                                                            <td class="py-2 pr-4">{{ $item->description }}</td>
                                                            <td class="py-2 pr-4">{{ $item->quantity }}</td>
                                                            <td class="py-2 pr-4">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                            <td class="py-2 pr-4">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-700 bg-slate-900/60 px-6 py-10 text-center text-sm text-slate-400">
                            Belum ada transaksi yang tercatat.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
