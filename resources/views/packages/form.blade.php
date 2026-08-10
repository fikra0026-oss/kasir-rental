<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $package->exists ? 'Edit Paket Rental' : 'Tambah Paket Rental' }}</h2>
                <p class="mt-1 text-sm text-slate-400">Buat paket jam, member, weekend, dan paket promosi.</p>
            </div>
            <a href="{{ route('packages.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ $package->exists ? route('packages.update', $package) : route('packages.store') }}" method="POST">
                    @csrf
                    @if($package->exists)
                        @method('PUT')
                    @endif
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="name" value="Nama Paket" class="text-slate-300" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $package->name) }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="price" value="Harga" class="text-slate-300" />
                                <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('price', $package->price) }}" required />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="duration_minutes" value="Durasi (menit)" class="text-slate-300" />
                                <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="mt-1 block w-full" value="{{ old('duration_minutes', $package->duration_minutes) }}" required />
                                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" value="Deskripsi" class="text-slate-300" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">{{ old('description', $package->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3">
                                <input type="checkbox" name="is_member" value="1" {{ old('is_member', $package->is_member) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400" />
                                <span class="text-slate-200">Member</span>
                            </label>
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3">
                                <input type="checkbox" name="is_weekend" value="1" {{ old('is_weekend', $package->is_weekend) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400" />
                                <span class="text-slate-200">Weekend</span>
                            </label>
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3">
                                <input type="checkbox" name="is_holiday" value="1" {{ old('is_holiday', $package->is_holiday) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400" />
                                <span class="text-slate-200">Hari Libur</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ $package->exists ? 'Perbarui Paket' : 'Simpan Paket' }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
