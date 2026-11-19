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
        Schema::table('issues', function (Blueprint $table) {
            $table->unsignedInteger('github_issue_number')->nullable()->after('assigned_to')->index();
            $table->string('github_issue_url')->nullable()->after('github_issue_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['github_issue_number']);
            $table->dropColumn(['github_issue_number', 'github_issue_url']);
        });
    }
};
