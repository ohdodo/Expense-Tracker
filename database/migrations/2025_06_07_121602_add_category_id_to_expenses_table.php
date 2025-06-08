<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure we have a default "Other" category for each user
        $this->createDefaultCategories();

        // Add the category_id column as nullable first
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
        });

        // Migrate existing category data
        $this->migrateExistingCategories();

        // Now make category_id NOT NULL and add foreign key
        Schema::table('expenses', function (Blueprint $table) {
            // Make sure all expenses have a category_id before making it NOT NULL
            $defaultCategoryId = Category::where('name', 'Other')->where('is_default', true)->first()?->id;
            if ($defaultCategoryId) {
                DB::table('expenses')
                    ->whereNull('category_id')
                    ->update(['category_id' => $defaultCategoryId]);
            }

            // Now modify to NOT NULL and add foreign key
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
        });

        // Remove the old category column if it exists
        if (Schema::hasColumn('expenses', 'category')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add back the old category column
            $table->string('category')->nullable();
        });

        // Migrate data back to string categories
        $expenses = DB::table('expenses')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('expenses.id', 'categories.name')
            ->get();

        foreach ($expenses as $expense) {
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update(['category' => $expense->name]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Create default categories for existing users
     */
    private function createDefaultCategories(): void
    {
        $defaultCategories = [
            ['name' => 'Food & Dining', 'icon' => '🍽️', 'color' => '#EF4444'],
            ['name' => 'Transportation', 'icon' => '🚗', 'color' => '#3B82F6'],
            ['name' => 'Shopping', 'icon' => '🛍️', 'color' => '#8B5CF6'],
            ['name' => 'Entertainment', 'icon' => '🎬', 'color' => '#F59E0B'],
            ['name' => 'Bills & Utilities', 'icon' => '💡', 'color' => '#10B981'],
            ['name' => 'Healthcare', 'icon' => '🏥', 'color' => '#EF4444'],
            ['name' => 'Education', 'icon' => '📚', 'color' => '#6366F1'],
            ['name' => 'Travel', 'icon' => '✈️', 'color' => '#06B6D4'],
            ['name' => 'Other', 'icon' => '📝', 'color' => '#6B7280'],
        ];

        // Get all existing users
        $users = DB::table('users')->get();

        foreach ($defaultCategories as $categoryData) {
            // Create default category if it doesn't exist
            $existingDefault = Category::where('name', $categoryData['name'])
                ->where('is_default', true)
                ->first();

            if (!$existingDefault) {
                Category::create([
                    'user_id' => null, // Default categories have no user_id
                    'name' => $categoryData['name'],
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                    'is_default' => true,
                ]);
            }

            // Create user-specific categories for existing users
            foreach ($users as $user) {
                $existingUserCategory = Category::where('name', $categoryData['name'])
                    ->where('user_id', $user->id)
                    ->first();

                if (!$existingUserCategory) {
                    Category::create([
                        'user_id' => $user->id,
                        'name' => $categoryData['name'],
                        'icon' => $categoryData['icon'],
                        'color' => $categoryData['color'],
                        'is_default' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Migrate existing category strings to category_id
     */
    private function migrateExistingCategories(): void
    {
        // Get all expenses with old category strings
        $expenses = DB::table('expenses')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->get();

        foreach ($expenses as $expense) {
            // Try to find matching category for this user
            $category = Category::where('user_id', $expense->user_id)
                ->where('name', $expense->category)
                ->first();

            // If no user-specific category found, try default categories
            if (!$category) {
                $category = Category::where('is_default', true)
                    ->where('name', $expense->category)
                    ->first();
            }

            // If still no category found, create a new one for this user
            if (!$category) {
                $category = Category::create([
                    'user_id' => $expense->user_id,
                    'name' => $expense->category,
                    'icon' => '📝', // Default icon
                    'color' => '#6B7280', // Default color
                    'is_default' => false,
                ]);
            }

            // Update the expense with the category_id
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update(['category_id' => $category->id]);
        }

        // For expenses without categories, assign them to "Other"
        $otherCategory = Category::where('name', 'Other')
            ->where('is_default', true)
            ->first();

        if ($otherCategory) {
            DB::table('expenses')
                ->whereNull('category_id')
                ->update(['category_id' => $otherCategory->id]);
        }
    }
};
