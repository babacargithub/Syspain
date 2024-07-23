<?php

namespace Tests;

use App\Models\Boulangerie;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
    protected Boulangerie $boulangerie;
    protected ?User $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->boulangerie = Boulangerie::factory()::mockActiveBoulangerie();
        $user = User::factory()->create();
        $this->user = $user;
        $this->actingAs($user);

    }

}
