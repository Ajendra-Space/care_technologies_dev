<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            // Add indexes for better performance
            try {
                $table->index('sort_order', 'custom_fields_sort_order_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            try {
                $table->index('is_active', 'custom_fields_is_active_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            try {
                $table->dropIndex('custom_fields_sort_order_index');
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropIndex('custom_fields_is_active_index');
            } catch (\Exception $e) {
                // Index might not exist
            }
        });
    }
};

