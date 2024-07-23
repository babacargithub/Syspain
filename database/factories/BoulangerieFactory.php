<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Boulangerie>
 */
class BoulangerieFactory extends Factory
{

    static ?Boulangerie $boulangerieSingleton = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "nom" => $this->faker->company(),
            "created_at" => now(),
            "company_id" => Company::factory()->create()->id,
            //
        ];
    }


    public static function mockActiveBoulangerie(): Boulangerie
    {
        if (self::$boulangerieSingleton === null) {
            self::$boulangerieSingleton = Boulangerie::factory()->create();
        }
        return self::$boulangerieSingleton;
    }


}
