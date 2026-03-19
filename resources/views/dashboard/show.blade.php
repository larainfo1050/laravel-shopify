@extends('layouts.app')

@section('title', 'Upload Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
</div>

<!-- Upload Info -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $upload->original_filename }}</h1>
            <p class="text-gray-500 mt-1">Uploaded on {{ $upload->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        <div class="text-right">
            @if($upload->status === 'completed')
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                    Completed
                </span>
            @elseif($upload->status === 'processing')
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                    Processing
                </span>
            @elseif($upload->status === 'failed')
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                    Failed
                </span>
            @else
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                    Pending
                </span>
            @endif
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="mt-6">
        <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>Progress</span>
            <span>{{ $upload->processed_rows }} / {{ $upload->total_rows }} products</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $upload->progress_percentage }}%"></div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-4 gap-4 mt-6">
        <div class="bg-gray-50 p-4 rounded">
            <div class="text-2xl font-bold text-gray-900">{{ $upload->total_rows }}</div>
            <div class="text-sm text-gray-600">Total</div>
        </div>
        <div class="bg-green-50 p-4 rounded">
            <div class="text-2xl font-bold text-green-600">{{ $upload->successful_rows }}</div>
            <div class="text-sm text-gray-600">Successful</div>
        </div>
        <div class="bg-red-50 p-4 rounded">
            <div class="text-2xl font-bold text-red-600">{{ $upload->failed_rows }}</div>
            <div class="text-sm text-gray-600">Failed</div>
        </div>
        <div class="bg-blue-50 p-4 rounded">
            <div class="text-2xl font-bold text-blue-600">{{ $upload->processed_rows }}</div>
            <div class="text-sm text-gray-600">Processed</div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Products ({{ $upload->products->count() }})</h2>
    </div>
    
    @if($upload->products->isEmpty())
        <div class="p-8 text-center text-gray-500">
            No products yet
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Handle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($upload->products as $product)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $product->handle }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $product->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($product->variant_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product->variant_sku }}</td>
                    <td class="px-6 py-4">
                        @if($product->import_status === 'successful')
                            <span class="px-2 text-xs font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                        @else
                            <span class="px-2 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Logs -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Import Logs ({{ $upload->logs->count() }})</h2>
    </div>
    
    <div class="divide-y divide-gray-200">
        @forelse($upload->logs as $log)
        <div class="px-6 py-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        @if($log->level === 'error')
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">ERROR</span>
                        @elseif($log->level === 'success')
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">SUCCESS</span>
                        @elseif($log->level === 'warning')
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">WARNING</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">INFO</span>
                        @endif
                        <span class="text-sm font-medium text-gray-900">{{ $log->message }}</span>
                    </div>
                    @if($log->context)
                        <pre class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                    @endif
                </div>
                <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
            </div>
        </div>
        @empty
        <div class="px-6 py-8 text-center text-gray-500">
            No logs available
        </div>
        @endforelse
    </div>
</div>
@endsection