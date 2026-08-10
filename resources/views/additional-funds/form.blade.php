<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Tambah Dana Tambahan</h2>
                <p class="mt-1 text-sm text-slate-400">Catat tambahan modal atau pemasukan non-operasional.</p>
            </div>
            <a href="{{ route('additional-funds.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ route('additional-funds.store') }}" method="POST">
                    @csrf
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="source" value="Sumber" class="text-slate-300" />
                            <select id="source" name="source" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" required>
                                @foreach(['modal_owner' => 'Modal Owner', 'pinjaman' => 'Pinjaman', 'donasi' => 'Donasi', 'lain_lain' => 'Lain-lain'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('source')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="amount" value="Jumlah" class="text-slate-300" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('amount') }}" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="date" value="Tanggal" class="text-slate-300" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" value="{{ old('date', now()->format('Y-m-d')) }}" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="description" value="Deskripsi" class="text-slate-300" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>Simpan Dana Tambahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
