<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Event venue',
            'Retail store back of house',
            'Active construction site',
            'Agriculture, farm and landscaping',
            'Personal care and grooming',
            'Trade service (plumbing, HVAC, electrical)',
            'Hotel and lodging',
            'Auto service, tire shop and car wash',
            'Carpentry and furniture workshop',
            'Restaurant and commercial kitchen',
            'Commercial cleaning and janitorial',
            'Food and beverage manufacturing',
            'Warehouse and distribution',
            'Plastic and light assembly',
            'Metal fabrication and welding',
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }
    }
}
