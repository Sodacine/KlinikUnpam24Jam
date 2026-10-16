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
        Schema::table('queues', function (Blueprint $table) {
            $table->boolean('needs_prescription_revision')->default(false)->after('pharmacist_notes');
            $table->text('prescription_replacement_reason')->nullable()->after('needs_prescription_revision');
            $table->timestamp('prescription_replacement_requested_at')->nullable()->after('prescription_replacement_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn([
                'needs_prescription_revision',
                'prescription_replacement_reason',
                'prescription_replacement_requested_at',
            ]);
        });
    }
};
