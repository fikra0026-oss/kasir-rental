<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Booking Pelanggan</h2>
                <p class="mt-1 text-sm text-slate-400">Kelola jadwal booking unit PS.</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Booking</a>
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
                                <th class="px-6 py-4 text-left">Pelanggan</th>
                                <th class="px-6 py-4 text-left">Unit</th>
                                <th class="px-6 py-4 text-left">Tanggal</th>
                                <th class="px-6 py-4 text-left">Jam Mulai</th>
                                <th class="px-6 py-4 text-left">Durasi</th>
                                <th class="px-6 py-4 text-left">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4">{{ $booking->customer_name }}</td>
                                    <td class="px-6 py-4">{{ $booking->unit->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::parse($booking->date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }}</td>
                                    <td class="px-6 py-4">{{ intdiv($booking->duration_minutes, 60) }} jam {{ $booking->duration_minutes % 60 }} menit</td>
                                    <td class="px-6 py-4">{{ ucfirst($booking->status) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('bookings.edit', $booking) }}" class="mr-2 text-cyan-300 hover:text-cyan-100">Edit</a>
                                        <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-300 hover:text-rose-100" onclick="return confirm('Hapus booking ini?')">Hapus</button>
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
