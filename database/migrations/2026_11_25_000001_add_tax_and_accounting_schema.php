<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('npwp', 32)->nullable()->after('nik');
            $table->string('tax_entity_type', 32)->default('orang_pribadi')->after('npwp');
            $table->string('ptkp_status', 16)->default('TK/0')->after('tax_entity_type');
            $table->boolean('is_tax_exempt')->default(false)->after('ptkp_status');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_domestic')->default(false)->after('tax_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_domestic')->default(false)->after('client_tax_id');
            $table->decimal('ppn_rate', 5, 2)->default(0)->after('is_domestic');
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_rate');
            $table->decimal('pph23_withheld', 15, 2)->default(0)->after('ppn_amount');
            $table->decimal('idr_exchange_rate', 14, 2)->nullable()->after('pph23_withheld');
            $table->string('faktur_pajak_no', 64)->nullable()->after('idr_exchange_rate');
            $table->timestamp('journal_posted_at')->nullable()->after('faktur_pajak_no');
        });

        Schema::table('payment_cycles', function (Blueprint $table) {
            $table->timestamp('journal_posted_at')->nullable()->after('created_by');
        });

        Schema::table('finance_entries', function (Blueprint $table) {
            $table->string('source_type', 64)->nullable()->after('finance_period_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('tax_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_month', 7)->unique();
            $table->string('status', 24)->default('DRAFT');
            $table->bigInteger('total_gross_idr')->default(0);
            $table->bigInteger('total_pph21_idr')->default(0);
            $table->bigInteger('total_pph23_idr')->default(0);
            $table->bigInteger('total_ppn_idr')->default(0);
            $table->bigInteger('total_client_pph23_idr')->default(0);
            $table->string('ntpn_code', 64)->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_period_id')->constrained('tax_periods')->cascadeOnDelete();
            $table->foreignId('payment_cycle_id')->nullable()->constrained('payment_cycles')->nullOnDelete();
            $table->foreignId('payment_cycle_entry_id')->nullable()->constrained('payment_cycle_entries')->nullOnDelete();
            $table->uuid('partner_id')->nullable()->index();
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_npwp', 32)->nullable();
            $table->string('recipient_nik', 32)->nullable();
            $table->string('tax_type', 16)->default('PPH21');
            $table->string('tax_object_code', 32)->default('21-100-09');
            $table->bigInteger('gross_amount_idr')->default(0);
            $table->bigInteger('dpp_amount_idr')->default(0);
            $table->decimal('tax_rate_percent', 6, 3)->default(0);
            $table->bigInteger('tax_amount_idr')->default(0);
            $table->bigInteger('net_amount_idr')->default(0);
            $table->string('bupot_number', 64)->nullable();
            $table->string('bupot_path')->nullable();
            $table->string('status', 24)->default('CALCULATED');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['tax_period_id', 'payment_cycle_entry_id']);
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_records');
        Schema::dropIfExists('tax_periods');

        Schema::table('finance_entries', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });

        Schema::table('payment_cycles', function (Blueprint $table) {
            $table->dropColumn('journal_posted_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'is_domestic',
                'ppn_rate',
                'ppn_amount',
                'pph23_withheld',
                'idr_exchange_rate',
                'faktur_pajak_no',
                'journal_posted_at',
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('is_domestic');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['npwp', 'tax_entity_type', 'ptkp_status', 'is_tax_exempt']);
        });
    }
};
