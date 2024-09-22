<?php
namespace Database\Seeders;

use App\Models\Abonnement;
use App\Models\Article;
use App\Models\ArticleProdPatisserie;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\CaisseTransaction;
use App\Models\Chariot;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompteClient;
use App\Models\CompteLivreur;
use App\Models\Depense;
use App\Models\Intrant;
use App\Models\Livreur;
use App\Models\MouveIntrant;
use App\Models\ProdPatisserie;
use App\Models\Recette;
use App\Models\StockIntrant;
use App\Models\TypeDepense;
use App\Models\TypeRecette;
use App\Models\User;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/** @noinspection PhpUnused */

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('db:wipe', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);
        // run company seeder
        $company = Company::create([
            'nom' => 'Boulangerie Groupe Sokhna Aida']);
        $boulangerie_names = [
            'Kedougou Emergence',
            'Kédougou Téranga',
            'Kédouga Ecobanck',
            'Kolda Escale',
            'Kolda Khadim',
            'Kaffrine',
            'Dahra Djoloff',
        ];
        $company->boulangeries()->createMany(collect($boulangerie_names)->map(function ($name) {
            $boulangerie = new Boulangerie();
            $boulangerie->nom = $name;
            $boulangerie->prix_pain_livreur = 160;
            $boulangerie->prix_pain_client = 175;
            return $boulangerie;

        })->toArray());


        $boulangerie = Boulangerie::factory()::mockActiveBoulangerie();
        $boulangerie->save();
        $boulangerie->refresh();
        // caisse


        foreach ($company->boulangeries as $boulangerie) {
            $caisse = Caisse::create([
                "nom" => "Caisse " . $boulangerie->nom,
                "boulangerie_id" => $boulangerie->id,
            ]);

            // clients
            $type_recettes = [
                ['nom' => 'Vente Patisserie', 'constant_name' => 'vente_patisserie'],
                ['nom' => 'Versement Livreur', 'constant_name' => 'versement_livreur'],
                ['nom' => 'Versement Client', 'constant_name' => 'versement_client'],
                ['nom' => 'Vente Pain', 'constant_name' => 'vente_pain'],
                ['nom' => 'Paiement Abonnement', 'constant_name' => 'paiement_abonnement'],
                ['nom' => 'Vente Boutique', 'constant_name' => 'vente_boutique'],
                ['nom' => 'Vente de restants', 'constant_name' => 'vente_de_restants'],
                ['nom' => 'Autres recettes', 'constant_name' => 'autres_recettes']
            ];

            $type_depenses = [
                ['nom' => 'Achat Intrant', 'constant_name' => 'achat_intrant'],
                ['nom' => 'Achat Matériel', 'constant_name' => 'achat_materiel'],
                ['nom' => 'Achat de carburant', 'constant_name' => 'achat_de_carburant'],
                ['nom' => 'Achat eau', 'constant_name' => 'achat_eau'],
                ['nom' => 'Facture électricité', 'constant_name' => 'facture_electricite'],
                ['nom' => 'Facture eau', 'constant_name' => 'facture_eau'],
                ['nom' => 'Facture téléphone', 'constant_name' => 'facture_telephone'],
                ['nom' => 'Achat Farine', 'constant_name' => 'achat_farine'],
                ['nom' => 'Paiement ouvrier', 'constant_name' => 'paiement_ouvrier'],
                ['nom' => 'Autre dépense', 'constant_name' => 'autre_depense']
            ];




            TypeRecette::insert(collect($type_recettes)->map(function ($item) use ($boulangerie) {
                return [
                    'nom' => $item['nom'],
                    'constant_name' => $item['constant_name'],
                    'boulangerie_id' => $boulangerie->id,
                ];
            })->toArray());

            TypeDepense::insert(collect($type_depenses)->map(function ($item) use ($boulangerie) {
                return [
                    'nom' => $item['nom'],
                    'constant_name' => $item['constant_name'],
                    'boulangerie_id' => $boulangerie->id,
                ];
            })->toArray());
            Boutique::create([
                'nom' => 'Boutique ' . $boulangerie->nom,
                'boulangerie_id' => $boulangerie->id,
            ]);
            Chariot::insert(
                 [[
                    'nom' => 'Chariot ' . '221'.' pains',
                    'nombre_pain' => 221,
                    'boulangerie_id' => $boulangerie->id,
                ],
                 [
                    'nom' => 'Chariot ' . '204'.' pains',
                    'nombre_pain' => 204,
                    'boulangerie_id' => $boulangerie->id,
                ],]
            );
            $intrant_noms = [
                [
                    "nom" => "Farine",
                    "contant_name" => "farine",
                ],
                [
                    "nom" => "Gasoil",
                    "contant_name" => "gasoil",
                ],
                [
                    "nom" => "Levure",
                    "contant_name" => "levure",
                ],[
                    "nom" => "Améliorant",
                    "contant_name" => "ameliorant",
                ],[
                    "nom" => "Glace",
                    "contant_name" => "glace",
                ],
            ];
            Intrant::insert(collect($intrant_noms)->map(function ($item) use ($boulangerie) {
                return [
                    'nom' => $item['nom'],
                    'constant_name' => $item['contant_name'],
                    'boulangerie_id' => $boulangerie->id,
                ];
            })->toArray());
            foreach (Intrant::whereBoulangerieId($boulangerie->id)->get() as $intrant) {
                $intrant->stock()->create([
                    'nom' => 'Stock ' . $intrant->nom,
                    'code_bar' => $intrant->id.''.rand(10000, 99099),
                    "quantite" => 0,
                    "prix_achat" => 0,
                    "boulangerie_id" => $boulangerie->id,
                ]);
            }
           $articles_patisserie_noms = [
               [
                   "nom"=>"Croissant",
                   'prix'=>500
               ],[
                   "nom"=>"Croissant Beure",
                   'prix'=>750
               ],[
                   "nom"=>"Pain raisin",
                   'prix'=>500
               ],[
                   "nom"=>"Cake",
                   'prix'=>100
               ],
               [
                   "nom"=>"Pain chocolat",
                   'prix'=>500
               ],
               [
                   "nom"=>"Pain au lait 150F",
                   'prix'=>150
               ],[
                   "nom"=>"Pain au lait 100F",
                   'prix'=>100
               ],[
                   "nom"=>"Pain coco",
                   'prix'=>100
               ],[
                   "nom"=>"Gateau anniversaire 15 000F",
                   'prix'=>15000
               ],
               [
                   "nom"=>"Gateau anniversaire 10 000F",
                   'prix'=>10000
               ],

           ];
            Article::insert(collect($articles_patisserie_noms)->map(function ($item) use ($boulangerie) {
                return [
                    'nom' => $item['nom'],
                    'prix' => $item['prix'],
                    'boulangerie_id' => $boulangerie->id,
                ];
            })->toArray());
        }


        print "Database seeded successfully\n";


    }
}
