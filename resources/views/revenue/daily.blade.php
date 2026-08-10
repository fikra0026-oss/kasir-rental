<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $title }}</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900/80 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-cyan-400/60 hover:bg-slate-800">Kembali ke Dashboard</a>
                @if(Str::contains($type, 'archive'))
                    <a href="{{ route($type === 'ps-archive' ? 'revenue.archive.ps.export' : 'revenue.archive.food-drinks.export') }}" class="inline-flex items-center rounded-full border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm font-medium text-emerald-200 transition hover:border-emerald-400 hover:bg-emerald-500/20">Export Excel</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-[28px] border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.2em] text-slate-500">{{ $metricLabel }}</div>
                    <div class="mt-3 text-3xl font-semibold text-cyan-300">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-[28px] border border-slate-800 bg-slate-900/90 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.2em] text-slate-500">Jumlah Transaksi</div>
                    <div class="mt-3 text-3xl font-semibold text-white">{{ number_format($transactionCount) }}</div>
                </div>
            </div>

            <div class="rounded-[30px] border border-slate-800 bg-slate-950/90 p-4 shadow-2xl shadow-cyan-500/5 sm:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Daftar Pendapatan Harian</h3>
                        <p class="text-sm text-slate-400">Ringkasan per hari untuk {{ strtolower($metricLabel) }}.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($entries as $entry)
                        <article class="rounded-[24px] border border-slate-800 bg-slate-900/80 p-4 shadow-sm shadow-slate-950/20 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-white">{{ $entry['label'] }}</div>
                                    <div class="mt-1 text-sm text-slate-400">{{ $entry['transactions'] }} transaksi tercatat</div>
                                </div>
                                <div class="text-xl font-semibold text-cyan-300">Rp {{ number_format($entry['total'], 0, ',', '.') }}</div>
                            </div>

                            @if(!empty($entry['details']))
                                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                    <table class="min-w-full text-sm text-slate-300">
                                        <thead>
                                            <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-[0.12em] text-slate-500">
                                                <th class="py-2 pr-4">Kode</th>
                                                <th class="py-2 pr-4">Pelanggan</th>
                                                <th class="py-2 pr-4">Item</th>
                                                <th class="py-2 pr-4">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($entry['details'] as $detail)
                                                <tr class="border-b border-slate-800/70">
                                                    <td class="py-2 pr-4">{{ $detail['transaction_number'] }}</td>
                                                    <td class="py-2 pr-4">{{ $detail['customer_name'] }}</td>
                                                    <td class="py-2 pr-4">{{ $detail['description'] }}</td>
                                                    <td class="py-2 pr-4">Rp {{ number_format($detail['amount'], 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-[24px] border border-dashed border-slate-700 bg-slate-900/60 px-6 py-10 text-center text-sm text-slate-400">
                            Belum ada data pendapatan harian untuk kategori ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
