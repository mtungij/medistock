<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
         ['Analgesics', 'ANA'],
    ['Antibiotics', 'ANT'],
    ['Antiseptics', 'ASE'],
    ['Disinfectants', 'DIS'],
    ['Vitamins', 'VIT'],
    ['Supplements', 'SUP'],
    ['Herbal Remedies', 'HER'],
    ['Pain Relief', 'PAI'],
    ['Cough & Cold', 'COU'],
    ['Digestive Health', 'DIG'],
    ['Skin Care', 'SKI'],
    ['First Aid', 'FIR'],
    ['Oral Care', 'ORA'],
    ['Eye Care', 'EYE'],
    ['Ear Care', 'EAR'],
    ['Respiratory Drugs', 'RES'],
    ['Cardiovascular Drugs', 'CAR'],
    ['Diabetes Drugs', 'DIA'],
    ['Gastrointestinal Drugs', 'GAS'],
    ['Neurological Drugs', 'NEU'],
    ['Psychiatric Drugs', 'PSY'],
    ['Dermatological Drugs', 'DER'],
    ['Oncology Drugs', 'ONC'],
    ['Immunology Drugs', 'IMM'],
    ['Endocrinology Drugs', 'END'],
    ['Hematology Drugs', 'HEM'],
    ['Nephrology Drugs', 'NEP'],
    ['Ophthalmology Drugs', 'OPH'],
    ['Rheumatology Drugs', 'RHE'],
    ['Laxatives', 'LAX'],
        ];

        foreach ($this->companyIds() as $companyId) {
            $branch = Branch::query()
                ->where('company_id', $companyId)
                ->where('code', 'MAIN')
                ->first()
                ?? Branch::query()->where('company_id', $companyId)->first();

            foreach ($categories as [$name, $code]) {
                Category::query()->updateOrCreate(
                    ['company_id' => $companyId, 'code' => $code],
                    [
                        'branch_id' => $branch?->id,
                        'name' => $name,
                        'description' => "{$name} construction materials",
                        'status' => 'active',
                        'allow_fractional_sales' => $code === 'PIP',
                    ]
                );
            }
        }
    }

    /**
     * Set SEED_COMPANY_ID=3 to seed one company, or omit it to seed all companies.
     *
     * @return array<int>
     */
    private function companyIds(): array
    {
        $companyId = env('SEED_COMPANY_ID');

        if ($companyId) {
            return [Company::query()->findOrFail((int) $companyId)->id];
        }

        return Company::query()->orderBy('id')->pluck('id')->all();
    }
}
