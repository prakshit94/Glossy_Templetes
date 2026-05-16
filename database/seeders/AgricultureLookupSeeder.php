<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\IrrigationType;
use App\Models\LandUnit;
use Illuminate\Database\Seeder;

class AgricultureLookupSeeder extends Seeder
{
    public function run(): void
    {
        // Land Units
        $landUnits = ['Acre', 'Hectare', 'Bigha', 'Guntha', 'Kanal', 'Marla'];
        foreach ($landUnits as $unit) {
            LandUnit::updateOrCreate(['name' => $unit], ['status' => 'active']);
        }

        // Irrigation Types
        $irrigationTypes = ['Drip', 'Sprinkler', 'Canal', 'Tube Well', 'Rainfed', 'River Pump'];
        foreach ($irrigationTypes as $type) {
            IrrigationType::updateOrCreate(['name' => $type], ['status' => 'active']);
        }

        // Major Crops
        $crops = ['Wheat', 'Rice', 'Cotton', 'Sugarcane', 'Maize', 'Soybean', 'Gram', 'Mustard', 'Bajra', 'Jowar'];
        foreach ($crops as $crop) {
            Crop::updateOrCreate(['name' => $crop], ['status' => 'active']);
        }
    }
}
