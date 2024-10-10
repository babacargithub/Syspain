<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    //
    /**
     * @throws Exception
     */
    public function handleLoginAttempt (Request $request) {
        // Validate phone number and password
        $credentials = $request->validate([
            'phone_number' => ['required'], // Expecting phone number instead of email
            'password' => ['required'],
        ]);

        // Attempt authentication using phone_number and password
        if (Auth::attempt(['phone_number' => $credentials['phone_number'], 'password' => $credentials['password']])) {

            // Validate if user is disabled after authentication
            $request->validate([
                'phone_number' => [function ($attribute, $value, $fail) {
                    if (request()->user()->disabled) {
                        $fail("Compte désactivé !");
                        Auth::logout();
                    }
                }],
            ]);

            // Call your login response method
            return $this->loginResponse();

        } else {
            // Invalid credentials response
            return response("Invalid credentials")->setStatusCode(401);
        }
    }
    /**
     * @throws Exception
     */
    protected function loginResponse(): Response
{
    $token = request()->user()->createToken("name", [], Carbon::now()->addDay());
    $params = [];

    $roles = [];
//    foreach (request()->user()->roles as $role) {
//        $roles[] = $role->name;
//
//    }
    $permissions = [];
    /*foreach (request()->user()->permissions as $permission) {
        $roles[] = $permission->name;

    }*/
    $user = request()->user();
    return response(["token" => $token->plainTextToken,
        "tokenExpiresAt"=>$token->accessToken->expires_at,
        "params" => $params,
        "user" => [
            "email" => $user->email,
            "token" => $token->plainTextToken,
            "tokenExpiresAt" => $token->accessToken->expires_at,
            "name" => $user->name,
            "roles" => $roles,
            "permissions" => $permissions,
            "isAuthenticated" => true,
            "is_super_admin" => $user->isSuperAdmin(),
            "is_admin" => $user->isAdmin(),
        ],
        "boulangeries" => Company::requireCompanyOfLoggedInUser()->boulangeries->map(function ($boulangerie) {
            return [
                'id' => $boulangerie->id,
                'nom' => $boulangerie->nom,
            ];
        }),
        "should_change_password" => (Hash::check("0000", $user->password) || Hash::check("1234", $user->password))
    ]);
}
}
