<?php

namespace Database\Seeders;

use App\Support\PharmacyUnits;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyUnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            PharmacyUnits::ensureForCompany((int) $companyId);
        }
    }
}
