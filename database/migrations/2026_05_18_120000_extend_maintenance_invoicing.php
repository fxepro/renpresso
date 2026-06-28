<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_invoices', function (Blueprint $table) {
            $table->foreignUuid('property_id')->nullable()->after('maintenance_request_id')->constrained('properties')->nullOnDelete();
            $table->string('bill_to_name')->nullable()->after('description');
            $table->string('bill_to_email')->nullable()->after('bill_to_name');
            $table->text('notes_customer')->nullable()->after('bill_to_email');
            $table->text('notes_internal')->nullable()->after('notes_customer');
            $table->bigInteger('subtotal_minor')->default(0)->after('amount_minor');
            $table->bigInteger('tax_minor')->default(0)->after('subtotal_minor');
            $table->foreignUuid('created_by')->nullable()->after('tax_minor')->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable()->after('issued_at');
            $table->timestamp('paid_at')->nullable()->after('sent_at');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
        });

        Schema::create('maintenance_invoice_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_invoice_id')->constrained('maintenance_invoices')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('description', 500);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->bigInteger('unit_price_minor');
            $table->bigInteger('line_total_minor');
            $table->timestamps();

            $table->index(['maintenance_invoice_id', 'sort_order']);
        });

        Schema::create('maintenance_invoice_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_invoice_id')->constrained('maintenance_invoices')->cascadeOnDelete();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 32)->default('other');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('size_bytes')->default(0);
            $table->string('caption', 255)->nullable();
            $table->timestamps();

            $table->index('maintenance_invoice_id');
        });

        Schema::create('maintenance_invoice_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_invoice_id')->constrained('maintenance_invoices')->cascadeOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 40);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['maintenance_invoice_id', 'created_at']);
        });

        if (Schema::hasTable('maintenance_invoices')) {
            $invoices = DB::table('maintenance_invoices')->get();
            foreach ($invoices as $inv) {
                $subtotal = (int) $inv->amount_minor;
                DB::table('maintenance_invoices')->where('id', $inv->id)->update([
                    'subtotal_minor' => $subtotal,
                    'tax_minor'      => 0,
                ]);

                $exists = DB::table('maintenance_invoice_lines')
                    ->where('maintenance_invoice_id', $inv->id)
                    ->exists();

                if (! $exists && $subtotal > 0) {
                    DB::table('maintenance_invoice_lines')->insert([
                        'id'                     => (string) Str::uuid(),
                        'maintenance_invoice_id' => $inv->id,
                        'sort_order'             => 0,
                        'description'            => $inv->description ?: 'Invoice total',
                        'quantity'               => 1,
                        'unit_price_minor'       => $subtotal,
                        'line_total_minor'       => $subtotal,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                }

                if ($inv->status === 'paid' && ! $inv->paid_at) {
                    DB::table('maintenance_invoices')->where('id', $inv->id)->update(['paid_at' => $inv->updated_at]);
                }
                if ($inv->status === 'sent' && ! $inv->sent_at) {
                    DB::table('maintenance_invoices')->where('id', $inv->id)->update(['sent_at' => $inv->issued_at ?? $inv->updated_at]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_invoice_events');
        Schema::dropIfExists('maintenance_invoice_attachments');
        Schema::dropIfExists('maintenance_invoice_lines');

        Schema::table('maintenance_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'bill_to_name',
                'bill_to_email',
                'notes_customer',
                'notes_internal',
                'subtotal_minor',
                'tax_minor',
                'sent_at',
                'paid_at',
                'cancelled_at',
            ]);
        });
    }
};
