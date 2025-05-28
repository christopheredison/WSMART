<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FinalRating;

class FinalRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ratings = [
            [
                'rating' => 'AAA',
                'conversion_score' => 100
            ],
            [
                'rating' => 'AA',
                'conversion_score' => 90
            ],
            [
                'rating' => 'A',
                'conversion_score' => 79
            ],
            [
                'rating' => 'BBB',
                'conversion_score' => 67
            ],
            [
                'rating' => 'BB',
                'conversion_score' => 56
            ],
            [
                'rating' => 'B',
                'conversion_score' => 44
            ],
            [
                'rating' => 'CCC',
                'conversion_score' => 33
            ],
            [
                'rating' => 'CC',
                'conversion_score' => 21
            ],
            [
                'rating' => 'C',
                'conversion_score' => 10
            ],
        ];

        foreach ($ratings as $rating) {
            FinalRating::create($rating);
        }
    }
}
