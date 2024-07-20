<?php
namespace Database\Seeders;

use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\CaisseTransaction;
use App\Models\Chariot;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompteLivreur;
use App\Models\Depense;
use App\Models\Intrant;
use App\Models\Livreur;
use App\Models\MouveIntrant;
use App\Models\Recette;
use App\Models\TypeDepense;
use App\Models\TypeRecette;
use App\Models\User;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/** @noinspection PhpUnused */

class DevDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('db:wipe', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);
        // run company seeder
        $company = Company::factory()->create();

        $boulangerie = Boulangerie::factory()->create([
            "company_id" => $company->id,
            "nom" => "Boulangerie Lamam 1",
        ]);
        $boulangerie->save();
        $boulangerie->refresh();
        // caisse
        $caisse = Caisse::factory()->create([
            "boulangerie_id" => $boulangerie->id,
        ]);
        $caisse->transactions()->saveMany(CaisseTransaction::factory()->count(100)
            ->for($caisse)
            ->for(User::factory()->create())
            ->make());
        // clients
        $clients = Client::factory()->count(5)->make();
        foreach ($clients as $client) {
            $client->boulangerie_id = $boulangerie->id;
            $client->save();
//            $client->abonnement()->save(Abonnement::factory()->make());
//                $client->compteClient()->save(CompteClient::factory()->make());
//                $client->save();
        }


        $livreurs = Livreur::factory()->count(30)->make();
        foreach ($livreurs as $livreur) {
            $livreur->boulangerie_id = $boulangerie->id;
            $livreur->save();
            $livreur->compteLivreur()->save(CompteLivreur::factory()->make());
            $livreur->save();
            $livreur->versements()->saveMany(Versement::factory()->count(20)
                ->for($caisse)
                ->for($boulangerie)
                ->make());

        }

        TypeRecette::factory()->count(2)
            ->for($boulangerie)
            ->create();
        TypeDepense::factory()->count(2)->for($boulangerie)->create();
        $caisse->recettes()->saveMany(Recette::factory()
            ->count(50)
            ->for($boulangerie)
            ->for($caisse)
            ->for(TypeRecette::first())
            ->make());
        $caisse->depenses()->saveMany(Depense::factory()
            ->count(50)
            ->for($boulangerie)
            ->for($caisse)
            ->for(TypeDepense::first())
            ->make());

        Boutique::factory()->count(3)->for($boulangerie)->create();
        Chariot::factory()->count(5)
            ->for($boulangerie)->create();

        Intrant::factory()->count(30)->for($boulangerie)->create();
        Intrant::first()->stock->mouvements()->saveMany(MouveIntrant::factory()->count(20)
            ->for(Intrant::first()->stock)
            ->for($boulangerie)
            ->make());
        $boulangerie->save();
        // other data to seed
        // production pétrissier
        // chariot prod petrisseur
        // production panetier
        // chariot prod panetier
        // distribution panetier




        print "Database seeded successfully\n";


    }
}
