<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Librarian;
use Illuminate\Support\Facades\Hash;

class LibrarianSeeder extends Seeder
{
    public function run(): void
    {
        Librarian::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
