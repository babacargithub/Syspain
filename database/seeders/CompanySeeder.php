<?php
namespace Database\Seeders;

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\Chariot;
use App\Models\Client;
use App\Models\Company;
use App\Models\Livreur;
use App\Models\TypeDepense;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
/** @noinspection PhpUnused */

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('db:wipe', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);
        // run company seeder
        $company = Company::factory()->make();
        $company->save();
        $boulangerie = Boulangerie::factory()->make([
            "nom" => "Boulangerie Lamam 1",
        ]);
       $boulangerie->company()->associate($company);
       $boulangerie->save();

       // créer caisses
       $boulangerie->caisses()->save(
           Caisse::factory()->make()
       );
       //créer boutiques
         $boulangerie->boutiques()->saveMany(
              Boutique::factory()->count(5)->make()
         );

       // Créer chariots
       $boulangerie->chariots()->saveMany(
           Chariot::factory()->count(5)->make()
       );

       TypeDepense::factory()->count(3)->create([
       ]);


       // Créer livreur et clients
       Livreur::factory()->count(5)
           ->has(Versement::factory()->count(20))
           ->create([
               "boulangerie_id" => $boulangerie->id
           ]);
       foreach (Livreur::all() as $livreur) {
           $livreur->compteLivreur()->create([
                "solde_pain" => 0,
                "dette" => 0,

           ]);
       }
       Client::factory()->count(5)->create([
           "boulangerie_id" => $boulangerie->id
       ]);
       // create compte clients
         foreach (Client::all() as $client) {
              $client->compteClient()->create([
                "solde_pain" => 0,
                "dette" => 0,
              ]);
                $abonnement = Abonnement::factory()->make();
                $abonnement->client()->associate($client);
                $abonnement->save();
         }

       // Créer une deuxième boulangerie


    }
}
