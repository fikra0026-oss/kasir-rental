<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Dashboard Kasir Rental PS</h2>
                <p class="mt-1 text-sm text-slate-400">Ringkasan operasional, pendapatan, dan status unit secara real-time.</p>
            </div>
            <div class="rounded-2xl bg-slate-900/90 px-4 py-3 text-sm text-slate-300">
                Halo, {{ auth()->user()->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-950 text-slate-100">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('partials.admin-nav')

            <div class="relative mb-6 overflow-hidden rounded-[28px] border border-slate-800 bg-slate-900/95 shadow-2xl shadow-cyan-950/20">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.22),_transparent_24%),radial-gradient(circle_at_top_right,_rgba(168,85,247,0.18),_transparent_20%),linear-gradient(180deg,_#020617_0%,_#051121_100%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_rgba(59,130,246,0.18),_transparent_22%)]"></div>
                <div class="relative flex flex-col gap-6 p-8 sm:p-10 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full border border-cyan-400/25 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-200 shadow-sm shadow-cyan-500/10">🎮 Live rental PS</span>
                        <h3 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">Dashboard kasir rental PS siap pakai</h3>
                        <p class="max-w-2xl text-sm text-slate-300">Pantau booking, transaksi, dan status unit dari satu layar. Desain modern ini cocok untuk kasir rental PlayStation Anda.</p>
                    </div>
                    <div class="rounded-[26px] border border-white/10 bg-slate-950/80 p-5 text-center shadow-xl shadow-slate-950/20 backdrop-blur-xl">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Slot hari ini</p>
                        <p class="mt-2 text-4xl font-bold text-cyan-300">{{ $statusCounts['digunakan'] ?? 0 }} / {{ count($units) }}</p>
                        <p class="mt-1 text-sm text-slate-300">unit sedang digunakan</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-4 lg:grid-cols-2">
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Transaksi Hari Ini</div>
                    <div class="mt-4 text-4xl font-semibold text-white">{{ number_format($transactionsToday) }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Pendapatan Hari Ini</div>
                    <div class="mt-4 text-4xl font-semibold text-cyan-300">Rp {{ number_format($incomeToday, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-emerald-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Unit Digunakan</div>
                    <div class="mt-4 text-4xl font-semibold text-emerald-300">{{ $statusCounts['digunakan'] ?? 0 }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-emerald-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Unit Kosong</div>
                    <div class="mt-4 text-4xl font-semibold text-emerald-300">{{ $statusCounts['kosong'] ?? 0 }}</div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-4">
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Pendapatan Minggu Ini</div>
                    <div class="mt-3 text-3xl font-semibold text-slate-100">Rp {{ number_format($incomeWeek, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Pendapatan Bulan Ini</div>
                    <div class="mt-3 text-3xl font-semibold text-slate-100">Rp {{ number_format($incomeMonth, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Pendapatan PS</div>
                    <div class="mt-3 text-3xl font-semibold text-cyan-300">Rp {{ number_format($psRevenue, 0, ',', '.') }}</div>
                    <div class="mt-2 text-sm text-slate-400">Pendapatan PS Hari Ini: Rp {{ number_format($psRevenueToday, 0, ',', '.') }}</div>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <a href="{{ route('revenue.daily.ps') }}" class="inline-flex text-sm font-medium text-cyan-300 transition hover:text-cyan-200">Lihat daftar pendapatan harian PS →</a>
                        <a href="{{ route('revenue.archive.ps') }}" class="inline-flex text-sm font-medium text-cyan-300/80 transition hover:text-cyan-200">Arsip bulan lalu →</a>
                    </div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-emerald-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Pendapatan Makanan & Minuman</div>
                    <div class="mt-3 text-3xl font-semibold text-emerald-300">Rp {{ number_format($foodDrinkRevenue, 0, ',', '.') }}</div>
                    <div class="mt-2 text-sm text-slate-400">Pendapatan Makanan & Minuman Hari Ini: Rp {{ number_format($foodDrinkRevenueToday, 0, ',', '.') }}</div>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <a href="{{ route('revenue.daily.food-drinks') }}" class="inline-flex text-sm font-medium text-emerald-300 transition hover:text-emerald-200">Lihat daftar pendapatan harian makanan & minuman →</a>
                        <a href="{{ route('revenue.archive.food-drinks') }}" class="inline-flex text-sm font-medium text-emerald-300/80 transition hover:text-emerald-200">Arsip bulan lalu →</a>
                    </div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-emerald-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Laba Bersih</div>
                    <div class="mt-3 text-3xl font-semibold text-emerald-300">Rp {{ number_format($netIncome, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Total Keuangan</div>
                    <div class="mt-3 text-3xl font-semibold text-cyan-300">Rp {{ number_format($totalKeuangan, 0, ',', '.') }}</div>
                    <div class="mt-2 text-sm text-slate-400">Pendapatan + dana tambahan - pengeluaran</div>
                </div>
                <div class="rounded-[30px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="text-sm uppercase tracking-[0.18em] text-slate-500">Booking Akan Dimulai</div>
                    <div class="mt-3 text-3xl font-semibold text-slate-100">{{ $upcomingBookings->count() }}</div>
                </div>
            </div>

            <div class="mt-8 rounded-[30px] border border-slate-800 bg-slate-900/95 p-6 shadow-xl shadow-cyan-500/10">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Grafik Pendapatan Harian</h3>
                        <p class="mt-1 text-sm text-slate-400">Perkembangan pendapatan dalam 30 hari terakhir</p>
                    </div>
                </div>
                <div class="overflow-x-auto max-w-full">
                    @if($revenueChartData->isNotEmpty())
                        @php
                            $maxValue = max($revenueChartData->pluck('total')->toArray() ?: [1]);
                            $chartHeight = 360;
                            $chartWidth = max(680, $revenueChartData->count() * 64);
                            $points = [];
                            $index = 0;
                            foreach ($revenueChartData as $point) {
                                $value = $point['total'];
                                $ratio = $maxValue > 0 ? ($value / $maxValue) : 0;
                                $x = 24 + ($index * 56);
                                $y = 16 + (($chartHeight - 32) * (1 - $ratio));
                                $points[] = ['x' => $x, 'y' => $y, 'label' => $point['label'], 'value' => $value];
                                $index++;
                            }
                        @endphp
                        <div class="min-w-[{{ $chartWidth }}px] rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 40 }}" class="w-full h-[320px]">
                                <line x1="24" y1="16" x2="24" y2="{{ $chartHeight }}" stroke="#475569" stroke-width="1" />
                                <line x1="24" y1="{{ $chartHeight }}" x2="{{ $chartWidth - 24 }}" y2="{{ $chartHeight }}" stroke="#475569" stroke-width="1" />
                                <line x1="24" y1="16" x2="{{ $chartWidth - 24 }}" y2="16" stroke="#334155" stroke-width="1" stroke-dasharray="4 4" />
                                <line x1="24" y1="{{ $chartHeight / 2 }}" x2="{{ $chartWidth - 24 }}" y2="{{ $chartHeight / 2 }}" stroke="#334155" stroke-width="1" stroke-dasharray="4 4" />
                                @foreach($points as $index => $point)
                                    @php
                                        $x = $point['x'];
                                        $y = $point['y'];
                                    @endphp
                                    @if($index > 0)
                                        @php
                                            $prev = $points[$index - 1];
                                        @endphp
                                        <line x1="{{ $prev['x'] }}" y1="{{ $prev['y'] }}" x2="{{ $x }}" y2="{{ $y }}" stroke="#22d3ee" stroke-width="3" stroke-linecap="round" />
                                    @endif
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#34d399" stroke="#e2e8f0" stroke-width="2" />
                                    <text x="{{ $x }}" y="{{ $chartHeight + 20 }}" text-anchor="middle" fill="#94a3b8" font-size="11">{{ $point['label'] }}</text>
                                    <text x="{{ $x }}" y="{{ $y - 10 }}" text-anchor="middle" fill="#f8fafc" font-size="10">Rp {{ number_format($point['value'], 0, ',', '.') }}</text>
                                @endforeach
                            </svg>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-6 text-sm text-slate-400">
                            Belum ada data pendapatan harian.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-2">
                <section class="rounded-[30px] border border-slate-800 bg-slate-900/95 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Upcoming Bookings</h3>
                    </div>
                    <div class="space-y-4">
                        @forelse($upcomingBookings as $booking)
                            <div class="rounded-3xl border border-slate-800 bg-slate-950 px-4 py-4 shadow-sm shadow-slate-950/20">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h4 class="font-semibold text-white">{{ $booking->customer_name }}</h4>
                                        <p class="text-sm text-slate-400">Unit {{ $booking->unit->name ?? 'N/A' }} • {{ \Illuminate\Support\Carbon::parse($booking->date)->format('d M Y') }} {{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }}</p>
                                    </div>
                                    <span class="rounded-full bg-slate-800 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-300">{{ ucfirst($booking->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-slate-800 bg-slate-950 px-4 py-6 text-center text-sm text-slate-400">
                                Tidak ada booking yang dijadwalkan.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[30px] border border-slate-800 bg-slate-900/95 p-6 shadow-xl shadow-cyan-500/10">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Unit Status</h3>
                    </div>
                    <div class="grid gap-3">
                        @foreach($units as $unit)
                            <div class="rounded-3xl border border-slate-800 bg-slate-950 px-4 py-4 shadow-sm shadow-slate-950/20">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="text-sm text-slate-400">{{ $unit->name }} • {{ $unit->type }}</div>
                                        <div class="mt-1 text-base font-semibold text-white">TV {{ $unit->tv }}</div>
                                        @if($unit->active_rental)
                                            <div class="mt-2 text-xs text-cyan-300">
                                                Sisa <span class="countdown-value" data-end-time="{{ $unit->countdown_end }}">{{ gmdate('H:i:s', $unit->remaining_seconds ?? 0) }}</span>
                                            </div>
                                            <div class="mt-1 text-[11px] text-slate-500">
                                                {{ $unit->active_rental->customer_name ?? 'Pelanggan' }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.18em] {{ match($unit->status) {
                                                'kosong' => 'bg-emerald-500/15 text-emerald-300',
                                                'booking' => 'bg-blue-500/15 text-blue-300',
                                                'digunakan' => 'bg-rose-500/15 text-rose-300',
                                                'maintenance' => 'bg-slate-500/15 text-slate-300',
                                            } }}">
                                            {{ ucfirst($unit->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.booking-status-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const formData = new FormData(form);
                    const url = form.getAttribute('action');

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    }).then(function () {
                        window.location.reload();
                    });
                });
            });

            document.querySelectorAll('.countdown-value').forEach(function (element) {
                const endTime = Date.parse(element.dataset.endTime);

                if (!endTime) {
                    return;
                }

                const formatTime = function (seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;

                    return [hours, minutes, secs].map(function (value) {
                        return String(value).padStart(2, '0');
                    }).join(':');
                };

                const updateCountdown = function () {
                    const remainingSeconds = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
                    element.textContent = formatTime(remainingSeconds);

                    if (remainingSeconds === 0) {
                        element.textContent = '00:00:00';
                    }
                };

                updateCountdown();
                setInterval(updateCountdown, 1000);
            });
        });
    </script>
</x-app-layout>
