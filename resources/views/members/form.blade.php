<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $member->exists ? 'Edit Member' : 'Tambah Member' }}</h2>
                <p class="mt-1 text-sm text-slate-400">Form pelanggan member untuk loyalitas dan promosi.</p>
            </div>
            <a href="{{ route('members.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ $member->exists ? route('members.update', $member) : route('members.store') }}" method="POST">
                    @csrf
                    @if($member->exists)
                        @method('PUT')
                    @endif
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="name" value="Nama" class="text-slate-300" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $member->name) }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Nomor HP" class="text-slate-300" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $member->phone) }}" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="address" value="Alamat" class="text-slate-300" />
                            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">{{ old('address', $member->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="points" value="Poin" class="text-slate-300" />
                                <x-text-input id="points" name="points" type="number" class="mt-1 block w-full" value="{{ old('points', $member->points ?? 0) }}" />
                                <x-input-error :messages="$errors->get('points')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="level" value="Level Member" class="text-slate-300" />
                                <select id="level" name="level" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                    @foreach(['silver', 'gold', 'platinum'] as $level)
                                        <option value="{{ $level }}" {{ old('level', $member->level) === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('level')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ $member->exists ? 'Perbarui Member' : 'Simpan Member' }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
