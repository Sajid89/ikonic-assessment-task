<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate):Affiliate
    {
        // TODO: Complete this method

        $data = [
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->apiService->createDiscountCode($merchant)['code'],
        ];

        $user = User::UserByEmail($email)->first();
        if ($user == null) {
            $userData = [
                'name' => $name,
                'email' => $email,
                'type' => User::TYPE_AFFILIATE
            ];

            $newUser = User::create($userData);
            $data['user_id'] = $newUser->id;
            return Affiliate::create($data);
        }
        else {

            if ($user->affiliate != null) {
                return $user->affiliate;
            }

            $data['user_id'] = $merchant->user->id;
            return Affiliate::create($data);
        }
    }
}
