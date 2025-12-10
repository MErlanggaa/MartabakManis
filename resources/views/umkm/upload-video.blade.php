@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 bg-blue-600 text-white">
            <h2 class="text-2xl font-bold">Upload Video Baru</h2>
            <p class="mt-1 opacity-90">Bagikan video menarik tentang produk Anda</p>
        </div>
        
        <form action="{{ route('umkm.videos.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Video File Input -->
            <div class="space-y-2">
                <label for="video" class="block text-sm font-medium text-gray-700">File Video</label>
                <div class="flex items-center justify-center w-full">
                    <label for="video" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6" id="dropzone-content">
                            <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                            <p class="text-xs text-gray-500">MP4, MOV, OGG (MAX. 20MB)</p>
                        </div>
                        <div id="file-preview" class="hidden flex-col items-center justify-center pt-5 pb-6 w-full h-full">
                             <i class="fas fa-file-video text-4xl text-blue-500 mb-2"></i>
                             <p class="text-sm font-medium text-gray-900" id="filename-display"></p>
                             <p class="text-xs text-gray-500 mt-1 cursor-pointer hover:text-red-500" onclick="resetFile(event)">Ganti Video</p>
                        </div>
                        <input id="video" name="video" type="file" class="hidden" accept="video/*" required onchange="displayFilename(this)" />
                    </label>
                </div>
                @error('video')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Caption -->
            <div class="space-y-2">
                <label for="caption" class="block text-sm font-medium text-gray-700">Caption</label>
                <textarea id="caption" name="caption" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Ceritakan tentang video ini..."></textarea>
                @error('caption')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Product Tagging -->
            <div class="space-y-2">
                <label for="product_id" class="block text-sm font-medium text-gray-700">Tautkan Produk (Opsional)</label>
                <select id="product_id" name="product_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->nama }} (Rp {{ number_format($product->price, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500">Munculkan keranjang belanja di video Anda.</p>
                @error('product_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Upload Video
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function displayFilename(input) {
        const dropzone = document.getElementById('dropzone-content');
        const preview = document.getElementById('file-preview');
        const filenameDisplay = document.getElementById('filename-display');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            filenameDisplay.textContent = file.name;
            dropzone.classList.add('hidden');
            preview.classList.remove('hidden');
            preview.classList.add('flex');
        }
    }

    function resetFile(event) {
        event.preventDefault();
        const input = document.getElementById('video');
        const dropzone = document.getElementById('dropzone-content');
        const preview = document.getElementById('file-preview');
        
        input.value = ''; // Reset input
        dropzone.classList.remove('hidden');
        preview.classList.add('hidden');
        preview.classList.remove('flex');
    }
</script>
@endsection
