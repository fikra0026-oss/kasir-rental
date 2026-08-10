@if (session('success'))
    <div class="mb-6 rounded-xl border border-cyan-500/10 bg-cyan-500/10 p-4 text-sm text-cyan-100">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-500/10 bg-rose-500/10 p-4 text-sm text-rose-100">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
