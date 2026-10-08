<?php

use App\Models\Category;
use App\Models\MeasurementType;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::where('email', 'admin@buildmart.test')->firstOrFail());
});

test('create product defaults to count and tracks expiry without requiring a product expiry date', function () {
    $countId = (string) MeasurementType::where('code', MeasurementType::COUNT)->value('id');
    Volt::test('products.create')
        ->assertSet('measurement_type_id', $countId)
        ->assertDontSee('wire:model.live="measurement_type_id"', false)
        ->set('category_id', (string) Category::where('code', 'CEM')->value('id'))
        ->set('name', 'Product With Receipt Expiry')
        ->set('tracks_expiry', true)
        ->assertDontSee('type="month"', false)
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::where('name', 'Product With Receipt Expiry')->firstOrFail();
    expect($product->tracks_expiry)->toBeTrue()->and($product->expiry_date)->toBeNull();
});
