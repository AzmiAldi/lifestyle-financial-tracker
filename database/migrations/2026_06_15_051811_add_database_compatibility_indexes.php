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
        Schema::table('categories', function (Blueprint $table): void {
            $table->index(['user_id', 'type'], 'categories_user_id_type_index');
            $table->index('type', 'categories_type_index');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->index('category_id', 'transactions_category_id_index');
            $table->index('transaction_date', 'transactions_transaction_date_index');
            $table->index(['user_id', 'category_id', 'transaction_date'], 'transactions_user_category_date_index');
        });

        Schema::table('budgets', function (Blueprint $table): void {
            $table->index('category_id', 'budgets_category_id_index');
            $table->index('month', 'budgets_month_index');
        });

        Schema::table('savings_goals', function (Blueprint $table): void {
            $table->index(['user_id', 'deadline'], 'savings_goals_user_deadline_index');
        });

        Schema::table('mood_logs', function (Blueprint $table): void {
            $table->index('logged_date', 'mood_logs_logged_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mood_logs', function (Blueprint $table): void {
            $table->dropIndex('mood_logs_logged_date_index');
        });

        Schema::table('savings_goals', function (Blueprint $table): void {
            $table->dropIndex('savings_goals_user_deadline_index');
        });

        Schema::table('budgets', function (Blueprint $table): void {
            $table->dropIndex('budgets_category_id_index');
            $table->dropIndex('budgets_month_index');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex('transactions_category_id_index');
            $table->dropIndex('transactions_transaction_date_index');
            $table->dropIndex('transactions_user_category_date_index');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_user_id_type_index');
            $table->dropIndex('categories_type_index');
        });
    }
};
