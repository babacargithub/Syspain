<?php

namespace Tests;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\User;
use Database\Factories\BoulangerieFactory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
    protected Boulangerie $boulangerie;
    protected ?User $user;
    protected Caisse $caisse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        // Reset the singleton before creating
        BoulangerieFactory::$boulangerieSingleton = null;

        $this->boulangerie = Boulangerie::factory()->create();

        // Set the singleton to our boulangerie so mockActiveBoulangerie returns it
        BoulangerieFactory::$boulangerieSingleton = $this->boulangerie;

        // Create a caisse for this boulangerie
        $this->caisse = Caisse::factory()->make();
        $this->caisse->boulangerie_id = $this->boulangerie->id;
        $this->caisse->solde = 0;
        $this->caisse->save();

        $user = User::factory()->create();
        $this->user = $user;
        $this->actingAs($user);
    }

}
