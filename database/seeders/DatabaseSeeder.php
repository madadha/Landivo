<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $account = Account::firstOrCreate(['slug' => 'landivo-demo'], [
            'name' => 'Landivo Demo',
            'description' => 'الحساب التجريبي لمنصة Landivo',
        ]);

        foreach ([
            ['new', 'جديد', 'New', 'blue', false],
            ['contacted', 'تم التواصل', 'Contacted', 'yellow', false],
            ['confirmed', 'مؤكد', 'Confirmed', 'green', false],
            ['cancelled', 'ملغي', 'Cancelled', 'red', true],
        ] as $index => [$slug, $nameAr, $nameEn, $color, $isFinal]) {
            OrderStatus::firstOrCreate(
                ['account_id' => $account->id, 'slug' => $slug],
                ['name_ar' => $nameAr, 'name_en' => $nameEn, 'color' => $color, 'sort_order' => $index, 'is_final' => $isFinal],
            );
        }
        $user = User::firstOrCreate(
            ['email' => 'admin@landivo.test'],
            [
                'account_id' => $account->id,
                'name' => 'Landivo Admin',
                'password' => 'password',
            ],
        );

        $user->update(['account_id' => $account->id]);
        $user->assignRole('Super Admin');
    }
}
