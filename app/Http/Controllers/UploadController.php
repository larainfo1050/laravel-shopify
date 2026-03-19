<?php
// app/Http/Controllers/UploadController.php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use App\Jobs\ProcessCsvImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function create()
    {
        return view('upload.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '.csv';
            
            Log::info('=== UPLOAD START ===', [
                'original_name' => $originalName,
                'generated_name' => $filename
            ]);

            // Ensure uploads directory exists with proper permissions
            $uploadsPath = storage_path('app') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadsPath)) {
                mkdir($uploadsPath, 0777, true);
                chmod($uploadsPath, 0777); // Force permissions
                Log::info('Created uploads directory', ['path' => $uploadsPath]);
            }
            
            // Build full file path
            $fullPath = $uploadsPath . DIRECTORY_SEPARATOR . $filename;
            
            Log::info('Target path', [
                'uploads_dir' => $uploadsPath,
                'full_path' => $fullPath,
                'dir_exists' => is_dir($uploadsPath),
                'dir_writable' => is_writable($uploadsPath)
            ]);

            // Move uploaded file manually (more reliable than Storage on Windows)
            if (!$file->move($uploadsPath, $filename)) {
                throw new \Exception("Failed to move uploaded file");
            }
            
            // Verify file exists
            if (!file_exists($fullPath)) {
                throw new \Exception("File was not saved to: {$fullPath}");
            }
            
            $fileSize = filesize($fullPath);
            
            Log::info('File saved successfully', [
                'path' => $fullPath,
                'size' => $fileSize,
                'exists' => file_exists($fullPath),
                'readable' => is_readable($fullPath)
            ]);

            // Create upload record
            $upload = Upload::create([
                'filename' => $filename,
                'original_filename' => $originalName,
                'file_path' => 'uploads' . DIRECTORY_SEPARATOR . $filename,
                'file_size' => $fileSize,
                'status' => 'pending'
            ]);

            Log::info('Upload record created', ['upload_id' => $upload->id]);

            ImportLog::log(
                $upload->id,
                'info',
                'upload_created',
                "File uploaded: {$originalName}"
            );

            // Dispatch CSV processing job
            ProcessCsvImport::dispatch($upload);

            return redirect()
                ->route('dashboard.show', $upload->id)
                ->with('success', 'CSV file uploaded and processing has started!');

        } catch (\Exception $e) {
            Log::error('Upload failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function destroy(Upload $upload)
    {
        try {
            $filePath = storage_path('app') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload->file_path);
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $upload->delete();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Upload deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}