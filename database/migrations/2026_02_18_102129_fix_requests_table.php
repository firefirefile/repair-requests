<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'client_name')) {
                $table->string('client_name')->after('id');
            }
            if (!Schema::hasColumn('requests', 'phone')) {
                $table->string('phone')->after('client_name');
            }
            if (!Schema::hasColumn('requests', 'address')) {
                $table->text('address')->after('phone');
            }
            if (!Schema::hasColumn('requests', 'problem_text')) {
                $table->text('problem_text')->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'phone', 'address', 'problem_text']);
        });
    }
};
