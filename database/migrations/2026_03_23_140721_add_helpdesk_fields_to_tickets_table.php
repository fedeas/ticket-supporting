<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('id');

            $table->string('department')->nullable()->after('customer_id');
            $table->string('station_unit')->nullable()->after('department');
            $table->string('contact_person')->nullable()->after('station_unit');
            $table->string('contact_phone')->nullable()->after('contact_person');

            $table->string('installation_location')->nullable()->after('contact_phone');
            $table->string('vehicle_registration')->nullable()->after('installation_location');

            $table->string('report_channel')->nullable()->after('vehicle_registration');
            $table->json('equipment_types')->nullable()->after('report_channel');

            $table->text('fault_description')->nullable()->after('description');

            $table->string('recorded_by')->nullable()->after('fault_description');
            $table->text('internal_notes')->nullable()->after('recorded_by');

            $table->timestamp('resolved_at')->nullable()->after('closed_at');
            $table->text('resolution_action')->nullable()->after('resolved_at');
            $table->string('resolved_by')->nullable()->after('resolution_action');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'reference_number',
                'department',
                'station_unit',
                'contact_person',
                'contact_phone',
                'installation_location',
                'vehicle_registration',
                'report_channel',
                'equipment_types',
                'fault_description',
                'recorded_by',
                'internal_notes',
                'resolved_at',
                'resolution_action',
                'resolved_by',
            ]);
        });
    }
};