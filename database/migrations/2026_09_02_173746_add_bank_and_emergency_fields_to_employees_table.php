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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable()->after('mobile_number');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('bank_name')->nullable()->after('salary');
            $table->string('bank_account_no')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_no');
            $table->string('upi_id')->nullable()->after('bank_ifsc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'emergency_contact_name',
                'emergency_contact_phone',
                'bank_name',
                'bank_account_no',
                'bank_ifsc',
                'upi_id',
            ]);
        });
    }
};
