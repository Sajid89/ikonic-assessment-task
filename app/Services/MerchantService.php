<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        $data['password'] = $data['api_key'];
        $data['remember_token'] = Str::random(10);
        $data['type'] = User::TYPE_MERCHANT;

        $user = User::create($data);

        return Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Update the user
     *
     * @param User $user
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        $data['password'] = $data['api_key'];
        User::find($user->id)->update($data);

        $user->merchant->update([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        $user = User::UserByEmail($email)->first();
        return $user != null ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        foreach ($affiliate->orders as $order) {
            if ($order->payout_status == Order::STATUS_UNPAID)
            {
                dispatch(new PayoutOrderJob($order));
            }
        }
    }
}
