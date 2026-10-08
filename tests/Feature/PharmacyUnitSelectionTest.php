<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\MeasurementType;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\PharmacyUnits;
use Database\Seeders\DatabaseSeeder;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@buildmart.test')->firstOrFail();
    $this->actingAs($this->admin);
});

test('all three unit selectors show pharmacy units and store the chosen unit IDs', function () {
    $tablet = Unit::where('short_name', 'tab')->firstOrFail();
    $box = Unit::where('short_name', 'box')->firstOrFail();
    $form = Volt::test('products.create')
        ->assertSee('Tablet (tab)')
        ->assertSee('Capsule (cap)')
        ->assertSee('Ampoule (amp)')
        ->assertDontSee('Bag (bag)')
        ->set('category_id', (string) Category::where('code', 'CEM')->value('id'))
        ->set('name', 'Tablet Unit Product')
        ->set('unit_id', (string) $tablet->id)
        ->set('selling_unit_id', (string) $tablet->id)
        ->set('purchase_unit_id', (string) $box->id)
        ->set('purchase_conversion_factor', '100')
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::where('name', 'Tablet Unit Product')->firstOrFail();
    expect($product->unit_id)->toBe($tablet->id)
        ->and($product->selling_unit_id)->toBe($tablet->id)
        ->and($product->purchase_unit_id)->toBe($box->id)
        ->and($product->stockQuantityForPurchase(2))->toBe(200.0);
});

test('pharmacy defaults preserve existing units and options stay inside the current company', function () {
    $companyId = $this->admin->company_id;
    $before = Unit::where('company_id', $companyId)->count();
    $box = Unit::where('short_name', 'box')->firstOrFail();
    $box->update(['name' => 'Custom Box']);
    PharmacyUnits::ensureForCompany($companyId);
    expect(Unit::where('company_id', $companyId)->count())->toBe($before)
        ->and($box->fresh()->name)->toBe('Custom Box');

    $otherCompany = Company::create(['company_name' => 'Other Company', 'business_type' => 'Pharmacy', 'phone' => '255700000002', 'whatsapp_number' => '255700000002']);
    PharmacyUnits::ensureForCompany($otherCompany->id);
    $options = PharmacyUnits::options();
    expect($options->every(fn (Unit $unit) => (int) $unit->company_id === (int) $companyId))->toBeTrue()
        ->and($options->pluck('short_name')->all())->toBe(array_keys(PharmacyUnits::COMMON));

    Unit::where('short_name', 'cap')->update(['status' => 'inactive']);
    expect(PharmacyUnits::options()->pluck('short_name')->contains('cap'))->toBeFalse();
});

test('different stock and selling units expose the conversion factor for count products', function () {
    $tablet = Unit::where('short_name', 'tab')->firstOrFail();
    $strip = Unit::where('short_name', 'strip')->firstOrFail();
    Volt::test('products.create')
        ->set('category_id', (string) Category::where('code', 'CEM')->value('id'))
        ->set('name', 'Strip Unit Product')
        ->set('unit_id', (string) $tablet->id)
        ->set('purchase_unit_id', (string) $tablet->id)
        ->set('selling_unit_id', (string) $strip->id)
        ->assertSee('id="conversion_factor"', false)
        ->set('conversion_factor', '0.1')
        ->call('save')->assertHasNoErrors();

    $product = Product::where('name', 'Strip Unit Product')->firstOrFail();
    expect($product->measurementCode())->toBe(MeasurementType::COUNT)
        ->and($product->baseQuantityForSale(1))->toBe(10.0);

    Volt::test('products.edit', ['product' => $product])
        ->call('save')->assertHasNoErrors();
    expect($product->fresh()->baseQuantityForSale(1))->toBe(10.0);
});
