@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">All Products</h1>
    <p class="text-gray-600 mt-1">Total: {{ $products->total() }} products</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="{{ route('products') }}" class="flex gap-4">
        <!-- Search -->
        <div class="flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search by handle or title..."
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <!-- Status Filter -->
        <select name="status" class="px-4 py-2 border rounded-md">
            <option value="">All Statuses</option>
            <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>
                Successful
            </option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                Failed
            </option>
        </select>

        <!-- Upload Filter -->
        <select name="upload_id" class="px-4 py-2 border rounded-md">
            <option value="">All Uploads</option>
            @foreach($uploads as $upload)
                <option value="{{ $upload->id }}" {{ request('upload_id') == $upload->id ? 'selected' : '' }}>
                    {{ $upload->original_filename }}
                </option>
            @endforeach
        </select>

        <!-- Buttons -->
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
            Filter
        </button>
        <a href="{{ route('products') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300">
            Clear
        </a>
    </form>
</div>

<!-- Products Table -->
@if($products->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <h3 class="text-lg font-medium text-gray-900">No products found</h3>
        <p class="text-gray-500">Upload a CSV file to import products.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Handle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upload</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $product->id }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $product->handle }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                        @if($product->vendor)
                            <div class="text-sm text-gray-500">{{ $product->vendor }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        ${{ number_format($product->variant_price, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $product->variant_inventory_qty }}
                    </td>
                    <td class="px-6 py-4">
                        @if($product->import_status === 'successful')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Successful
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Failed
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('dashboard.show', $product->upload_id) }}" 
                           class="text-blue-600 hover:text-blue-900">
                            #{{ $product->upload_id }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $product->created_at->format('Y-m-d H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $products->appends(request()->query())->links() }}
    </div>
@endif
@endsection