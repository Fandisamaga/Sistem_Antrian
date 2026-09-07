<?php

namespace Database\Seeders;

use App\Models\Meja;
use App\Models\QueueCounter;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach (range(1, 20) as $number) {
            Meja::firstOrCreate([
                'nama_meja' => 'Meja '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            ]);
        }

        QueueCounter::firstOrCreate(['id' => 1], ['current_number' => 0]);

        User::updateOrCreate(
            ['email' => 'admin@dukcapil.test'],
            ['name' => 'Administrator', 'password' => 'password', 'role' => 'admin', 'meja_id' => null],
        );
        User::updateOrCreate(
            ['email' => 'cs@dukcapil.test'],
            ['name' => 'Customer Service', 'password' => 'password', 'role' => 'cs', 'meja_id' => null],
        );

        foreach (Meja::orderBy('id')->get() as $index => $meja) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['email' => "operator{$number}@dukcapil.test"],
                [
                    'name' => "Operator {$number}",
                    'password' => 'password',
                    'role' => 'operator',
                    'meja_id' => $meja->id,
                ],
            );
        }
    }
}
