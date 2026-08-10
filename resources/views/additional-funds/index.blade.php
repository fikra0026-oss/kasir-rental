<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Dana Tambahan</h2>
                <p class="mt-1 text-sm text-slate-400">Catat pemasukan tambahan dari modal pemilik, pinjaman, atau sumber lain.</p>
            </div>
            <a href="{{ route('additional-funds.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Dana Tambahan</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="overflow-hidden rounded-3xl border border-slate-700 bg-slate-950/90 shadow-2xl shadow-cyan-500/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700 text-sm text-slate-200">
                        <thead class="bg-slate-900 text-slate-400 uppercase tracking-[0.12em] text-xs">
                            <tr>
                                <th class="px-6 py-4 text-left">Tanggal</th>
                                <th class="px-6 py-4 text-left">Sumber</th>
                                <th class="px-6 py-4 text-left">Deskripsi</th>
                                <th class="px-6 py-4 text-left">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @forelse($additionalFunds as $additionalFund)
                                <tr>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::parse($additionalFund->date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4">{{ ucwords(str_replace('_', ' ', $additionalFund->source)) }}</td>
                                    <td class="px-6 py-4">{{ $additionalFund->description }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($additionalFund->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-slate-400">Belum ada data dana tambahan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
