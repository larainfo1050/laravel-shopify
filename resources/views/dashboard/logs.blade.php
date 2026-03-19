@extends('layouts.app')

@section('title', 'Import Logs')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Import Logs</h1>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" class="flex gap-4">
        <select name="level" class="rounded border-gray-300">
            <option value="">All Levels</option>
            <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
            <option value="success" {{ request('level') === 'success' ? 'selected' : '' }}>Success</option>
            <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
            <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
        </select>
        
        <select name="upload_id" class="rounded border-gray-300">
            <option value="">All Uploads</option>
            @foreach($uploads as $upload)
                <option value="{{ $upload->id }}" {{ request('upload_id') == $upload->id ? 'selected' : '' }}>
                    {{ $upload->original_filename }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Filter
        </button>
        <a href="{{ route('logs') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
            Clear
        </a>
    </form>
</div>

<!-- Logs Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upload</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($logs as $log)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td class="px-6 py-4">
                    @if($log->level === 'error')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ERROR</span>
                    @elseif($log->level === 'success')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">SUCCESS</span>
                    @elseif($log->level === 'warning')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">WARNING</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">INFO</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $log->upload->original_filename ?? 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $log->message }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    No logs found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>
@endsection