<?php
// app/Http/Controllers/UploadController.php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;

class UploadController extends Controller
{
    /**
     * Show upload form
     */
    public function create()
    {
        return view('upload.create');
    }

    /**
     * Handle file upload and process CSV
     */
    public function store(Request $request)
    {
        // Validate file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '.csv';
            
            // Store file
            $filePath = $file->storeAs('uploads', $filename);

            // Create upload record
            $upload = Upload::create([
                'filename' => $filename,
                'original_filename' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'status' => 'pending'
            ]);

            // Log upload started
            ImportLog::log(
                $upload->id,
                'info',
                'upload_created',
                "File uploaded: {$originalName}"
            );

            // Process CSV immediately (no queue for now)
            $this->processCsv($upload);

            return redirect()
                ->route('dashboard.show', $upload->id)
                ->with('success', 'CSV file uploaded and processed successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Process CSV file
     */
    private function processCsv(Upload $upload)
    {
        try {
            $upload->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $filePath = storage_path('app/' . $upload->file_path);
            
            // Parse CSV
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            $records = iterator_to_array($csv->getRecords());

            $upload->update(['total_rows' => count($records)]);

            ImportLog::log(
                $upload->id,
                'info',
                'csv_parsed',
                "Found " . count($records) . " products in CSV"
            );

            // Process each row
            foreach ($records as $index => $row) {
                try {
                    $product = Product::create([
                        'upload_id' => $upload->id,
                        'handle' => $row['Handle'] ?? '',
                        'title' => $row['Title'] ?? '',
                        'body_html' => $row['Body HTML'] ?? null,
                        'vendor' => $row['Vendor'] ?? null,
                        'product_type' => $row['Product Type'] ?? null,
                        'tags' => $row['Tags'] ?? null,
                        'published' => strtoupper($row['Published'] ?? 'FALSE') === 'TRUE',
                        'variant_sku' => $row['Variant SKU'] ?? null,
                        'variant_price' => (float) ($row['Variant Price'] ?? 0),
                        'variant_compare_at_price' => !empty($row['Variant Compare At Price']) ? (float) $row['Variant Compare At Price'] : null,
                        'variant_requires_shipping' => strtoupper($row['Variant Requires Shipping'] ?? 'TRUE') === 'TRUE',
                        'variant_taxable' => strtoupper($row['Variant Taxable'] ?? 'TRUE') === 'TRUE',
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

                    ImportLog::log(
                        $upload->id,
                        'success',
                        'product_imported',
                        "Product imported: {$product->title}",
                        $product->id
                    );

                } catch (\Exception $e) {
                    $upload->increment('failed_rows');

                    ImportLog::log(
                        $upload->id,
                        'error',
                        'product_failed',
                        "Failed to import row " . ($index + 1) . ": " . $e->getMessage(),
                        null,
                        ['row' => $row, 'error' => $e->getMessage()]
                    );
                }

                $upload->increment('processed_rows');
            }

            // Mark as completed
            $upload->update([
                'status' => 'completed',
                'completed_at' => now()
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

            ImportLog::log(
                $upload->id,
                'error',
                'import_failed',
                "Import failed: " . $e->getMessage()
            );
        }
    }

    /**
     * Delete upload
     */
    public function destroy(Upload $upload)
    {
        try {
            // Delete file
            if (Storage::exists($upload->file_path)) {
                Storage::delete($upload->file_path);
            }

            // Delete record (cascade will delete products and logs)
            $upload->delete();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Upload deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}