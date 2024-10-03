<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Client;
use App\Models\CompteClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $clients = Client::with('abonnement')->get();

        return response()->json($clients);
    }

    /**
     * Store a newly created client in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
        ]);


        $client = new Client($data);
        $client->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $client->save();
        // create compte client
        $compteClient = new  CompteClient();
        $compteClient->solde_pain = 0;
        $compteClient->solde_reliquat = 0;
        $compteClient->client()->associate($client);
        $compteClient->save();


        return response()->json($client, 201);
    }

    /**
     * Display the specified client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function show(Client $client)
    {
        return response()->json($client);
    }

    /**
     * Update the specified client in storage.
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'telephone' => 'sometimes|required|string|max:255',
        ]);

        $client->update($data);

        return response()->json($client);
    }

    /**
     * Remove the specified client from storage.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(null, 204);
    }

    /**
     * Toggle the active status of the specified client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function toggle(Client $client)
    {
        $client->is_active = !$client->is_active;
        $client->save();

        return response()->json($client);
    }
}
