<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $unit->exists ? 'Edit Unit PS' : 'Tambah Unit PS' }}</h2>
                <p class="mt-1 text-sm text-slate-400">Form untuk unit, tarif, dan status PS.</p>
            </div>
            <a href="{{ route('units.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ $unit->exists ? route('units.update', $unit) : route('units.store') }}" method="POST">
                    @csrf
                    @if($unit->exists)
                        @method('PUT')
                    @endif
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="name" value="Nomor Unit PS" class="text-slate-300" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $unit->name) }}" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="type" value="Jenis PS" class="text-slate-300" />
                            <x-text-input id="type" name="type" type="text" class="mt-1 block w-full" value="{{ old('type', $unit->type) }}" required />
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tv" value="TV" class="text-slate-300" />
                            <x-text-input id="tv" name="tv" type="text" class="mt-1 block w-full" value="{{ old('tv', $unit->tv) }}" required />
                            <x-input-error :messages="$errors->get('tv')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="hourly_rate" value="Tarif per Jam" class="text-slate-300" />
                            <x-text-input id="hourly_rate" name="hourly_rate" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('hourly_rate', $unit->hourly_rate ?? 15000) }}" required />
                            <x-input-error :messages="$errors->get('hourly_rate')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" value="Status" class="text-slate-300" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                @foreach(['kosong', 'digunakan', 'booking', 'maintenance'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $unit->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ $unit->exists ? 'Perbarui Unit' : 'Simpan Unit' }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
