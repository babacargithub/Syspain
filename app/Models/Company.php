<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * @throws \Exception
     */
    public static function requireCompanyOfLoggedInUser()
    {
        $user = auth()->user();
        if ($user === null) {
            if (app()->runningUnitTests()) {
                return Company::first()??Company::factory()->create();

            }
            throw new \Exception('Require company of logged in user failed, User not logged in');
        }
        // find company
        return CompanyUser::where('user_id',$user->id)->firstOrFail()->company;
    }

    public function boulangeries(): HasMany
    {
        return $this->hasMany(Boulangerie::class);
    }

}
