<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Pengeluaran</h2>
                <p class="mt-1 text-sm text-slate-400">Catat pengeluaran listrik, internet, gaji, dan pembelian rutin.</p>
            </div>
            <a href="{{ route('expenses.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Pengeluaran</a>
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
                                <th class="px-6 py-4 text-left">Kategori</th>
                                <th class="px-6 py-4 text-left">Deskripsi</th>
                                <th class="px-6 py-4 text-left">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach($expenses as $expense)
                                <tr>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::parse($expense->date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4">{{ ucwords(str_replace('_', ' ', $expense->category)) }}</td>
                                    <td class="px-6 py-4">{{ $expense->description }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
