@extends('layouts.app')

@section('title', 'Upload CSV File')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold mb-6">Upload CSV File</h1>

        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select CSV File
                </label>
                <input 
                    type="file" 
                    name="csv_file" 
                    accept=".csv,.txt"
                    required
                    class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        cursor-pointer"
                >
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Maximum file size: 10MB. Accepted formats: CSV, TXT
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Required CSV Columns:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Handle</li>
                    <li>• Title</li>
                    <li>• Variant Price</li>
                    <li>• Plus 18 more optional columns</li>
                </ul>
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit"
                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 font-semibold"
                >
                    Upload and Process
                </button>
                <a 
                    href="{{ route('dashboard') }}"
                    class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 font-semibold text-center"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Sample CSV Info -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h2 class="font-semibold text-gray-800 mb-3">CSV Format Example:</h2>
        <div class="bg-white p-4 rounded border text-xs overflow-x-auto">
            <pre>Handle,Title,Body HTML,Vendor,Product Type,Tags,Published,Variant SKU,Variant Price...
modern-desk-lamp,Modern Desk Lamp,&lt;p&gt;Description&lt;/p&gt;,LightCraft,Lighting,desk,TRUE,MDL-001,39.99...</pre>
        </div>
    </div>
</div>
@endsection