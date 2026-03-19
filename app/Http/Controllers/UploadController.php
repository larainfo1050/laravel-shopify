<?php
// app/Http/Controllers/UploadController.php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\Csv\Reader;

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

            // Process CSV
            $this->processCsv($upload);

            return redirect()
                ->route('dashboard.show', $upload->id)
                ->with('success', 'CSV file uploaded and processed successfully!');

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

    private function processCsv(Upload $upload)
    {
        try {
            Log::info('=== CSV PROCESSING START ===', ['upload_id' => $upload->id]);

            $upload->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Build path properly
            $filePath = storage_path('app') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload->file_path);
            
            Log::info('Reading CSV', [
                'path' => $filePath,
                'exists' => file_exists($filePath),
                'size' => file_exists($filePath) ? filesize($filePath) : 0,
                'readable' => is_readable($filePath)
            ]);

            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            // Parse CSV
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            
            $headers = $csv->getHeader();
            Log::info('CSV headers', ['headers' => $headers]);
            
            $records = iterator_to_array($csv->getRecords());
            $totalRows = count($records);
            
            $upload->update(['total_rows' => $totalRows]);

            Log::info('CSV parsed', ['total_rows' => $totalRows]);

            ImportLog::log(
                $upload->id,
                'info',
                'csv_parsed',
                "Found {$totalRows} products in CSV"
            );

            // Process each row
            foreach ($records as $index => $row) {
                try {
                    $product = Product::create([
                        'upload_id' => $upload->id,
                        'handle' => trim($row['Handle'] ?? ''),
                        'title' => trim($row['Title'] ?? ''),
                        'body_html' => $row['Body HTML'] ?? null,
                        'vendor' => $row['Vendor'] ?? null,
                        'product_type' => $row['Product Type'] ?? null,
                        'tags' => $row['Tags'] ?? null,
                        'published' => strtoupper(trim($row['Published'] ?? 'FALSE')) === 'TRUE',
                        'variant_sku' => $row['Variant SKU'] ?? null,
                        'variant_price' => (float) ($row['Variant Price'] ?? 0),
                        'variant_compare_at_price' => !empty($row['Variant Compare At Price']) ? (float) $row['Variant Compare At Price'] : null,
                        'variant_requires_shipping' => strtoupper(trim($row['Variant Requires Shipping'] ?? 'TRUE')) === 'TRUE',
                        'variant_taxable' => strtoupper(trim($row['Variant Taxable'] ?? 'TRUE')) === 'TRUE',
                        'variant_inventory_tracker' => $row['Variant Inventory Tracker'] ?? null,
                        'variant_inventory_qty' => (int) ($row['Variant Inventory Qty'] ?? 0),
                        'variant_inventory_policy' => $row['Variant Inventory Policy'] ?? null,
                        'variant_fulfillment_service' => $row['Variant Fulfillment Service'] ?? null,
                        'variant_weight' => !empty($row['Variant Weight']) ? (float) $row['Variant Weight'] : null,
                        'variant_weight_unit' => $row['Variant Weight Unit'] ?? null,
                        'image_src' => $row['Image Src'] ?? null,
                        'image_position' => !empty($row['Image Position']) ? (int) $row['Image Position'] : null,
                        'image_alt_text' => $row['Image Alt Text'] ?? null,
                        'import_status' => 'successful'
                    ]);

                    $upload->increment('successful_rows');

                    Log::info('Product imported', [
                        'id' => $product->id,
                        'handle' => $product->handle
                    ]);

                    ImportLog::log(
                        $upload->id,
                        'success',
                        'product_imported',
                        "Product imported: {$product->title}",
                        $product->id
                    );

                } catch (\Exception $e) {
                    $upload->increment('failed_rows');

                    Log::error('Product failed', [
                        'row' => $index + 1,
                        'error' => $e->getMessage()
                    ]);

                    ImportLog::log(
                        $upload->id,
                        'error',
                        'product_failed',
                        "Failed row " . ($index + 1) . ": " . $e->getMessage()
                    );
                }

                $upload->increment('processed_rows');
            }

            $upload->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            Log::info('=== PROCESSING COMPLETE ===', [
                'successful' => $upload->successful_rows,
                'failed' => $upload->failed_rows
            ]);

            ImportLog::log(
                $upload->id,
                'success',
                'import_completed',
                "Import completed. Success: {$upload->successful_rows}, Failed: {$upload->failed_rows}"
            );

        } catch (\Exception $e) {
            $upload->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);

            Log::error('=== PROCESSING FAILED ===', [
                'error' => $e->getMessage()
            ]);

            ImportLog::log(
                $upload->id,
                'error',
                'import_failed',
                "Import failed: " . $e->getMessage()
            );
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