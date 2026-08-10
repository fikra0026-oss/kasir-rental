<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h2>
                <p class="mt-1 text-sm text-slate-400">Form untuk stok makanan dan minuman.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-400 hover:text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class="rounded-3xl border border-slate-700 bg-slate-950/90 p-8 shadow-2xl shadow-cyan-500/5">
                <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" method="POST">
                    @csrf
                    @if($product->exists)
                        @method('PUT')
                    @endif
                    <div class="grid gap-6">
                        <div>
                            <x-input-label for="name" value="Nama Produk" class="text-slate-300" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $product->name) }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="category" value="Kategori" class="text-slate-300" />
                            <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" value="{{ old('category', $product->category) }}" required />
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="price" value="Harga" class="text-slate-300" />
                                <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('price', $product->price) }}" required />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="stock" value="Stok" class="text-slate-300" />
                                <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" value="{{ old('stock', $product->stock ?? 0) }}" required />
                                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3">
                                <input type="checkbox" name="active" value="1" {{ old('active', $product->active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400" />
                                <span class="text-slate-200">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ $product->exists ? 'Perbarui Produk' : 'Simpan Produk' }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
