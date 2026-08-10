<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $booking->exists ? 'Edit Booking' : 'Tambah Booking' }}</h2>
                <p class="mt-1 text-sm text-slate-400">Buat booking pelanggan untuk unit PS.</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ $booking->exists ? route('bookings.update', $booking) : route('bookings.store') }}" method="POST">
                    @csrf
                    @if($booking->exists)
                        @method('PUT')
                    @endif
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="customer_name" value="Nama Pelanggan" class="text-slate-300" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" value="{{ old('customer_name', $booking->customer_name) }}" required />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Nomor HP" class="text-slate-300" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $booking->phone) }}" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="date" value="Tanggal" class="text-slate-300" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" value="{{ old('date', $booking->date?->format('Y-m-d')) }}" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="start_time" value="Jam Mulai" class="text-slate-300" />
                                <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" value="{{ old('start_time', $booking->start_time) }}" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="package_id" value="Paket Rental (Opsional)" class="text-slate-300" />
                            <select id="package_id" name="package_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                <option value="">-- Pilih Paket (atau isi durasi manual) --</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" data-duration="{{ $package->duration_minutes }}" {{ old('package_id', $booking->package_id) == $package->id ? 'selected' : '' }}>{{ $package->name }} - {{ $package->duration_minutes }} menit - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('package_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="duration_minutes" value="Durasi (menit)" class="text-slate-300" />
                            <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="mt-1 block w-full" value="{{ old('duration_minutes', $booking->duration_minutes) }}" required />
                            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="playstation_unit_id" value="Unit PS" class="text-slate-300" />
                            <select id="playstation_unit_id" name="playstation_unit_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400" required>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('playstation_unit_id', $booking->playstation_unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} — {{ $unit->type }} ({{ ucfirst($unit->status) }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('playstation_unit_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status Booking" class="text-slate-300" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-200 outline-none focus:border-cyan-400 focus:ring-cyan-400">
                                @foreach(['pending', 'aktif', 'selesai', 'dibatalkan'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $booking->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ $booking->exists ? 'Perbarui Booking' : 'Simpan Booking' }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const packageSelect = document.getElementById('package_id');
            const durationInput = document.getElementById('duration_minutes');

            if (!packageSelect || !durationInput) {
                return;
            }

            const applyPackageDuration = () => {
                const selected = packageSelect.options[packageSelect.selectedIndex];
                const duration = selected?.dataset.duration;

                if (duration) {
                    durationInput.value = duration;
                }
            };

            packageSelect.addEventListener('change', applyPackageDuration);
            applyPackageDuration();
        });
    </script>
</x-app-layout>
