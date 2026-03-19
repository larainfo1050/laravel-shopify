<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard - List all uploads
     */
    public function index()
    {
        $uploads = Upload::withCount([
            'products',
            'successfulProducts',
            'failedProducts'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        // Dashboard stats
        $stats = [
            'total_uploads' => Upload::count(),
            'total_products' => Product::count(),
            'successful_products' => Product::where('import_status', 'successful')->count(),
            'failed_products' => Product::where('import_status', 'failed')->count(),
            'processing_uploads' => Upload::where('status', 'processing')->count(),
        ];

        return view('dashboard.index', compact('uploads', 'stats'));
    }

    /**
     * Show upload details
     */
    public function show($id)
    {
        $upload = Upload::with(['products', 'logs'])
            ->findOrFail($id);

        return view('dashboard.show', compact('upload'));
    }

    /**
     * Show all logs
     */
    public function logs(Request $request)
    {
        $query = ImportLog::with(['upload', 'product'])
            ->orderBy('created_at', 'desc');

        // Filter by level
        if ($request->has('level') && $request->level != '') {
            $query->where('level', $request->level);
        }

        // Filter by upload
        if ($request->has('upload_id') && $request->upload_id != '') {
            $query->where('upload_id', $request->upload_id);
        }

        $logs = $query->paginate(50);
        $uploads = Upload::orderBy('created_at', 'desc')->get();

        return view('dashboard.logs', compact('logs', 'uploads'));
    }

    /**
     * Show all products
     */
    public function products(Request $request)
    {
        $query = Product::with('upload')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('import_status', $request->status);
        }

        // Filter by upload
        if ($request->has('upload_id') && $request->upload_id != '') {
            $query->where('upload_id', $request->upload_id);
        }

        // Search by handle or title
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('handle', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(20);
        $uploads = Upload::orderBy('created_at', 'desc')->get();

        return view('dashboard.products', compact('products', 'uploads'));
    }
}