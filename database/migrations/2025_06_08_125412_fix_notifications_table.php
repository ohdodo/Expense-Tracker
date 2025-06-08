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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the existing type column if it exists
            if (Schema::hasColumn('notifications', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Add the type column with proper length and values
            $table->string('type', 50)->after('user_id');
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->after('type');
            }
            
            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->after('title');
            }
            
            // Ensure data column exists and is properly typed
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('message');
            }
            
            // Ensure is_read column exists
            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('data');
            }
            
            // Ensure read_at column exists
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'title', 'message', 'data', 'is_read', 'read_at']);
        });
    }
};
