<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Produk Makanan & Minuman</h2>
                <p class="mt-1 text-sm text-slate-400">Kelola stok, kategori, dan harga menu.</p>
            </div>
            <a href="{{ route('products.create') }}" class="inline-flex items-center rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-cyan-400">Tambah Produk</a>
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
                                <th class="px-6 py-4 text-left">Kategori</th>
                                <th class="px-6 py-4 text-left">Harga</th>
                                <th class="px-6 py-4 text-left">Stok</th>
                                <th class="px-6 py-4 text-left">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach($products as $product)
                                <tr>
                                    <td class="px-6 py-4">{{ $product->name }}</td>
                                    <td class="px-6 py-4">{{ $product->category }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">{{ $product->stock }}</td>
                                    <td class="px-6 py-4">{{ $product->active ? 'Aktif' : 'Nonaktif' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('products.edit', $product) }}" class="mr-2 text-cyan-300 hover:text-cyan-100">Edit</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-300 hover:text-rose-100" onclick="return confirm('Hapus produk ini?')">Hapus</button>
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
