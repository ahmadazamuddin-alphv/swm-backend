<?php

namespace Database\Seeders;

use App\Services\OperationsDemo;
use Illuminate\Database\Seeder;

class OperationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        app(OperationsDemo::class)->seed();
    }
}
