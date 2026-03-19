@extends('layouts.app')

@section('title', 'Upload Details')

@section('content')
<!-- Header -->
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Dashboard
    </a>
</div>

<!-- Upload Summary Card -->
<div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $upload->original_filename }}</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Uploaded {{ $upload->created_at->diffForHumans() }} • {{ number_format($upload->file_size / 1024, 2) }} KB
                    </p>
                </div>
            </div>
            
            <div>
                @if($upload->status === 'completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="mr-1.5 h-2 w-2 fill-green-400" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                        Completed
                    </span>
                @elseif($upload->status === 'processing')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 animate-pulse">
                        <svg class="mr-1.5 h-2 w-2 fill-blue-400" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                        Processing
                    </span>
                @elseif($upload->status === 'failed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <svg class="mr-1.5 h-2 w-2 fill-red-400" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                        Failed
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">
                        <svg class="mr-1.5 h-2 w-2 fill-slate-400" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                        Pending
                    </span>
                @endif
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-6">
            <div class="flex justify-between text-sm text-slate-600 mb-2">
                <span class="font-medium">Import Progress</span>
                <span>{{ $upload->processed_rows }} / {{ $upload->total_rows }} rows processed</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $upload->progress_percentage }}%"></div>
            </div>
            <div class="mt-1 text-right text-sm font-medium text-slate-900">{{ $upload->progress_percentage }}%</div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
        <dl class="grid grid-cols-4 gap-4">
            <div class="text-center">
                <dt class="text-sm font-medium text-slate-500">Total Rows</dt>
                <dd class="mt-1 text-3xl font-bold text-slate-900">{{ $upload->total_rows ?: '0' }}</dd>
            </div>
            <div class="text-center">
                <dt class="text-sm font-medium text-slate-500">Successful</dt>
                <dd class="mt-1 text-3xl font-bold text-green-600">{{ $upload->successful_rows ?: '0' }}</dd>
            </div>
            <div class="text-center">
                <dt class="text-sm font-medium text-slate-500">Failed</dt>
                <dd class="mt-1 text-3xl font-bold text-red-600">{{ $upload->failed_rows ?: '0' }}</dd>
            </div>
            <div class="text-center">
                <dt class="text-sm font-medium text-slate-500">Processed</dt>
                <dd class="mt-1 text-3xl font-bold text-blue-600">{{ $upload->processed_rows ?: '0' }}</dd>
            </div>
        </dl>
    </div>
</div>

<!-- Products Section -->
<div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
        <h2 class="text-lg font-semibold text-slate-900">Imported Products ({{ $upload->products->count() }})</h2>
    </div>
    
    @if($upload->products->isEmpty())
        <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-4 text-sm font-medium text-slate-900">No products yet</h3>
            <p class="mt-1 text-sm text-slate-500">Products will appear here once processing starts</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Handle</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @foreach($upload->products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $product->handle }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $product->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">${{ number_format($product->variant_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $product->variant_sku ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->import_status === 'successful')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Success
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Failed
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Import Logs Section -->
<div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
        <h2 class="text-lg font-semibold text-slate-900">Import Logs ({{ $upload->logs->count() }})</h2>
    </div>
    
    <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
        @forelse($upload->logs as $log)
        <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        @if($log->level === 'error')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                ERROR
                            </span>
                        @elseif($log->level === 'success')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                SUCCESS
                            </span>
                        @elseif($log->level === 'warning')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                WARNING
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                INFO
                            </span>
                        @endif
                        <span class="text-sm font-medium text-slate-900">{{ $log->message }}</span>
                    </div>
                    @if($log->context)
                        <pre class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-200 overflow-x-auto">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                    @endif
                </div>
                <span class="ml-4 text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at->format('H:i:s') }}</span>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="mt-2 text-sm text-slate-500">No logs available</p>
        </div>
        @endforelse
    </div>
</div>
@endsection