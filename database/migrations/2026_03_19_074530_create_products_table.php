<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->onDelete('cascade');
            
            // CSV Fields - Exact match
            $table->string('handle');
            $table->string('title');
            $table->text('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('published')->default(false);
            
            $table->string('variant_sku')->nullable();
            $table->decimal('variant_price', 10, 2);
            $table->decimal('variant_compare_at_price', 10, 2)->nullable();
            $table->boolean('variant_requires_shipping')->default(true);
            $table->boolean('variant_taxable')->default(true);
            $table->string('variant_inventory_tracker')->nullable();
            $table->integer('variant_inventory_qty')->default(0);
            $table->string('variant_inventory_policy')->nullable();
            $table->string('variant_fulfillment_service')->nullable();
            $table->decimal('variant_weight', 8, 2)->nullable();
            $table->string('variant_weight_unit')->nullable();
            
            $table->text('image_src')->nullable();
            $table->integer('image_position')->nullable();
            $table->string('image_alt_text')->nullable();
            
            // Status tracking
            $table->enum('import_status', ['pending', 'processing', 'successful', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
