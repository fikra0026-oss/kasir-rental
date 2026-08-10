<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Data Unit PS</h2>
                <p class="mt-1 text-sm text-slate-400">Kelola unit PS, status, tarif, dan informasi TV.</p>
            </div>
            <a href="{{ route('units.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Unit</a>
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
                                <th class="px-6 py-4 text-left">Unit</th>
                                <th class="px-6 py-4 text-left">Jenis</th>
                                <th class="px-6 py-4 text-left">TV</th>
                                <th class="px-6 py-4 text-left">Status</th>
                                <th class="px-6 py-4 text-left">Tarif / Jam</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach($units as $unit)
                                <tr>
                                    <td class="px-6 py-4">{{ $unit->name }}</td>
                                    <td class="px-6 py-4">{{ $unit->type }}</td>
                                    <td class="px-6 py-4">{{ $unit->tv }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs uppercase {{ match($unit->status) {
                                            'kosong' => 'bg-emerald-500/10 text-emerald-300',
                                            'booking' => 'bg-blue-500/10 text-blue-300',
                                            'digunakan' => 'bg-rose-500/10 text-rose-300',
                                            'maintenance' => 'bg-slate-500/10 text-slate-300',
                                        } }}">{{ ucfirst($unit->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4">Rp {{ number_format($unit->hourly_rate, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('units.edit', $unit) }}" class="mr-2 text-cyan-300 hover:text-cyan-100">Edit</a>
                                        <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-300 hover:text-rose-100" onclick="return confirm('Hapus unit ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
