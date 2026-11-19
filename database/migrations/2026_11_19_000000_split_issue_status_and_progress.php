<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The notification class names before and after the split.
     */
    protected const string OLD_NOTIFICATION = 'App\\Notifications\\User\\IssueStatusChangedNotification';

    protected const string NEW_NOTIFICATION = 'App\\Notifications\\User\\IssueProgressChangedNotification';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->renameColumn('status', 'progress');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->index('progress');
            $table->string('status', 20)->default('open')->after('progress')->index();
        });

        DB::table('issues')->where('progress', 'rejected')->update(['status' => 'wont_fix', 'progress' => 'new']);
        DB::table('issues')->where('progress', 'released')->whereNotNull('archived_at')->update(['status' => 'closed']);

        DB::table('issue_activities')->where('action', 'status_changed')->update(['action' => 'progress_changed']);
        DB::table('issue_activities')->where('action', 'progress_changed')->where('to_value', 'rejected')
            ->update(['action' => 'status_changed', 'from_value' => 'open', 'to_value' => 'wont_fix']);
        DB::table('issue_activities')->where('action', 'progress_changed')->where('from_value', 'rejected')
            ->update(['action' => 'status_changed', 'from_value' => 'wont_fix', 'to_value' => 'open']);

        DB::table('notifications')->where('type', self::OLD_NOTIFICATION)->update(['type' => self::NEW_NOTIFICATION]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notifications')->where('type', self::OLD_NOTIFICATION)->delete();
        DB::table('notifications')->where('type', self::NEW_NOTIFICATION)->update(['type' => self::OLD_NOTIFICATION]);

        DB::table('issue_activities')->where('action', 'status_changed')->where('to_value', 'wont_fix')
            ->update(['action' => 'progress_changed', 'from_value' => 'new', 'to_value' => 'rejected']);
        DB::table('issue_activities')->where('action', 'status_changed')->where('from_value', 'wont_fix')
            ->update(['action' => 'progress_changed', 'from_value' => 'rejected', 'to_value' => 'new']);
        DB::table('issue_activities')->where('action', 'status_changed')->delete();
        DB::table('issue_activities')->where('action', 'progress_changed')->update(['action' => 'status_changed']);

        DB::table('issues')->where('status', 'wont_fix')->update(['progress' => 'rejected']);

        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
            $table->dropIndex(['progress']);
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->renameColumn('progress', 'status');
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->index('status');
        });
    }
};
