<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $unsupportedCodes = ['BSHRM', 'BTLE', 'BSTLE'];
        $unsupportedIds = [10, 11];

        // Delete any related subjects if any exist
        DB::table('subjects')
            ->whereIn('program_id', function ($query) use ($unsupportedCodes, $unsupportedIds) {
                $query->select('id')
                    ->from('programs')
                    ->whereIn('code', $unsupportedCodes)
                    ->orWhereIn('id', $unsupportedIds);
            })
            ->delete();

        // Delete the unsupported programs
        DB::table('programs')
            ->whereIn('code', $unsupportedCodes)
            ->orWhereIn('id', $unsupportedIds)
            ->delete();
    }

    public function down(): void
    {
        // No reversal needed as these programs are unsupported.
    }
};
