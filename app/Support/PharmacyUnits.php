<?php

namespace App\Support;

use App\Models\MeasurementType;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PharmacyUnits
{
    public const COMMON = [
        'tab' => 'Tablet',
        'cap' => 'Capsule',
        'strip' => 'Strip',
        'blister' => 'Blister',
        'box' => 'Box',
        'pack' => 'Pack',
        'bottle' => 'Bottle',
        'vial' => 'Vial',
        'amp' => 'Ampoule',
        'tube' => 'Tube',
        'sachet' => 'Sachet',
        'inh' => 'Inhaler',
        'dose' => 'Dose',
        'kit' => 'Kit',
        'pcs' => 'Piece',
    ];

    public static function ensureForCompany(int $companyId): void
    {
        $countId = MeasurementType::where('code', MeasurementType::COUNT)->firstOrFail()->id;
        foreach (self::COMMON as $symbol => $name) {
            if (DB::table('units')->where('company_id', $companyId)->where('short_name', $symbol)->exists()) {
                continue;
            }
            DB::table('units')->insertOrIgnore([
                'company_id' => $companyId,
                'name' => $name,
                'short_name' => $symbol,
                'measurement_type_id' => $countId,
                'description' => $name.' pharmacy inventory unit',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** @return Collection<int, Unit> */
    public static function options(): Collection
    {
        $order = array_flip(array_keys(self::COMMON));

        return Unit::query()->with('measurementType')
            ->where('company_id', CompanyFeatures::companyId())
            ->where('status', 'active')
            ->whereIn('short_name', array_keys(self::COMMON))
            ->get()
            ->filter(fn (Unit $unit) => ProductMeasurementOptions::unitIsAllowed($unit, MeasurementType::COUNT))
            ->sortBy(fn (Unit $unit) => $order[$unit->short_name])
            ->values();
    }
}
