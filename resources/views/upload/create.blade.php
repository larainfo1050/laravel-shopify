@extends('layouts.app')

@section('title', 'Upload CSV')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 mb-4">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold text-slate-900">Upload CSV File</h1>
        <p class="mt-2 text-sm text-slate-600">Import products from a CSV file into the system</p>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Select CSV File
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-slate-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600">
                                <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input 
                                        id="csv_file" 
                                        name="csv_file" 
                                        type="file" 
                                        accept=".csv,.txt"
                                        required
                                        class="sr-only"
                                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'No file chosen'"
                                    >
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-500">CSV or TXT up to 10MB</p>
                            <p id="file-name" class="text-sm font-medium text-blue-600 mt-2"></p>
                        </div>
                    </div>
                    @error('csv_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CSV Requirements -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Required CSV Format</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p class="mb-2">Your CSV must include these columns:</p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li><span class="font-medium">Handle</span> - Unique product identifier</li>
                                    <li><span class="font-medium">Title</span> - Product name</li>
                                    <li><span class="font-medium">Variant Price</span> - Product price</li>
                                </ul>
                                <p class="mt-2 text-xs text-blue-600">Plus 18 optional columns for additional product details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-slate-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-slate-200">
                <a 
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    Cancel
                </a>
                <button 
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Upload and Process
                </button>
            </div>
        </form>
    </div>

    <!-- CSV Format Example -->
    <div class="mt-6 bg-white rounded-lg border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-medium text-slate-900 mb-3 flex items-center">
            <svg class="h-5 w-5 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            CSV Format Example
        </h3>
        <div class="bg-slate-900 rounded-md p-4 overflow-x-auto">
            <pre class="text-xs text-slate-100 font-mono"><span class="text-green-400">Handle</span>,<span class="text-green-400">Title</span>,<span class="text-green-400">Body HTML</span>,<span class="text-green-400">Vendor</span>,<span class="text-green-400">Product Type</span>,<span class="text-green-400">Tags</span>,<span class="text-green-400">Published</span>,<span class="text-green-400">Variant SKU</span>,<span class="text-green-400">Variant Price</span>...
modern-desk-lamp,Modern Desk Lamp,&lt;p&gt;Description&lt;/p&gt;,LightCraft,Lighting,desk,TRUE,MDL-001,39.99...
ergonomic-chair,Office Chair,&lt;p&gt;Comfortable chair&lt;/p&gt;,ErgoFit,Furniture,office,TRUE,EOC-001,189.99...</pre>
        </div>
    </div>
</div>

<script>
    // File drag and drop
    const fileInput = document.getElementById('csv_file');
    const dropZone = fileInput.closest('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        document.getElementById('file-name').textContent = files[0]?.name || '';
    }, false);
</script>
@endsection