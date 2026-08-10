<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Data Member</h2>
                <p class="mt-1 text-sm text-slate-400">Kelola pelanggan member, poin, dan level loyalitas.</p>
            </div>
            <a href="{{ route('members.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Member</a>
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
                                <th class="px-6 py-4 text-left">Nama</th>
                                <th class="px-6 py-4 text-left">Telepon</th>
                                <th class="px-6 py-4 text-left">Level</th>
                                <th class="px-6 py-4 text-left">Poin</th>
                                <th class="px-6 py-4 text-left">Total Transaksi</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach($members as $member)
                                <tr>
                                    <td class="px-6 py-4">{{ $member->name }}</td>
                                    <td class="px-6 py-4">{{ $member->phone }}</td>
                                    <td class="px-6 py-4">{{ ucfirst($member->level) }}</td>
                                    <td class="px-6 py-4">{{ $member->points }}</td>
                                    <td class="px-6 py-4">{{ $member->total_transactions }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('members.edit', $member) }}" class="mr-2 text-cyan-300 hover:text-cyan-100">Edit</a>
                                        <form action="{{ route('members.destroy', $member) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-300 hover:text-rose-100" onclick="return confirm('Hapus member ini?')">Hapus</button>
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
