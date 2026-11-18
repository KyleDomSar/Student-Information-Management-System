<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Create the first administrator only when credentials are configured locally.
     *
     * Never commit real credentials or use this seeder with real student data.
     */
    public function run(): void
    {
        $email = env('SIMS_ADMIN_EMAIL');
        $password = env('SIMS_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn(
                'No initial administrator was created. Set SIMS_ADMIN_EMAIL and SIMS_ADMIN_PASSWORD in your local .env, then run php artisan db:seed.'
            );

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SIMS_ADMIN_NAME', 'SIMS Administrator'),
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );

        $this->command?->info('Initial SIMS administrator account is ready.');
    }
}
