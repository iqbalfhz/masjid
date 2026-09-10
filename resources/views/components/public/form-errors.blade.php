@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="font-semibold">Mohon periksa kembali isian berikut:</p>
        <ul class="mt-2 list-disc space-y-1 ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
