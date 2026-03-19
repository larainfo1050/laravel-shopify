<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3; // Retry 3 times on failure

    protected $upload;

    /**
     * Create a new job instance.
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('=== JOB PROCESSING START ===', [
                'upload_id' => $this->upload->id,
                'job_id' => $this->job->getJobId()
            ]);

            $this->upload->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Build path properly
            $filePath = storage_path('app') . DIRECTORY_SEPARATOR . str_replace(
                ['/', '\\'], 
                DIRECTORY_SEPARATOR, 
                $this->upload->file_path
            );
            
            Log::info('Reading CSV', [
                'path' => $filePath,
                'exists' => file_exists($filePath),
                'size' => file_exists($filePath) ? filesize($filePath) : 0
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
            
            $this->upload->update(['total_rows' => $totalRows]);

            Log::info('CSV parsed', ['total_rows' => $totalRows]);

            ImportLog::log(
                $this->upload->id,
                'info',
                'csv_parsed',
                "Found {$totalRows} products in CSV"
            );

            // Process each row with transaction
            foreach ($records as $index => $row) {
                try {
                    // Per-product transaction for ACID properties
                    DB::transaction(function () use ($row) {
                        $handle = trim($row['Handle'] ?? '');
                        
                        // Update if exists, create if not (prevents duplicates)
                        $product = Product::updateOrCreate(
                            ['handle' => $handle], // Search by unique handle
                            [
                                'upload_id' => $this->upload->id,
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
                            ]
                        );

                        // Check if product was just created or updated
                        $action = $product->wasRecentlyCreated ? 'created' : 'updated';

                        Log::info("Product {$action}", [
                            'id' => $product->id,
                            'handle' => $product->handle,
                            'action' => $action
                        ]);

                        ImportLog::log(
                            $this->upload->id,
                            'success',
                            "product_{$action}",
                            "Product {$action}: {$product->title}",
                            $product->id
                        );
                    });

                    $this->upload->increment('successful_rows');

                } catch (\Exception $e) {
                    $this->upload->increment('failed_rows');

                    Log::error('Product transaction failed', [
                        'row' => $index + 1,
                        'handle' => $row['Handle'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);

                    ImportLog::log(
                        $this->upload->id,
                        'error',
                        'product_failed',
                        "Failed row " . ($index + 1) . ": " . $e->getMessage()
                    );
                }

                $this->upload->increment('processed_rows');
            }

            $this->upload->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            Log::info('=== JOB PROCESSING COMPLETE ===', [
                'successful' => $this->upload->successful_rows,
                'failed' => $this->upload->failed_rows
            ]);

            ImportLog::log(
                $this->upload->id,
                'success',
                'import_completed',
                "Import completed. Success: {$this->upload->successful_rows}, Failed: {$this->upload->failed_rows}"
            );

        } catch (\Exception $e) {
            $this->upload->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);

            Log::error('=== JOB PROCESSING FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            ImportLog::log(
                $this->upload->id,
                'error',
                'import_failed',
                "Import failed: " . $e->getMessage()
            );

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('=== JOB FAILED AFTER ALL RETRIES ===', [
            'upload_id' => $this->upload->id,
            'error' => $exception->getMessage()
        ]);

        $this->upload->update([
            'status' => 'failed',
            'completed_at' => now()
        ]);

        ImportLog::log(
            $this->upload->id,
            'error',
            'job_failed',
            "Job failed after all retries: " . $exception->getMessage()
        );
    }
}