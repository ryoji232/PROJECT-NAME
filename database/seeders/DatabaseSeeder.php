<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // If you still want test users uncomment this:
        // \App\Models\User::factory(10)->create();

        // Call your librarian seeder
        $this->call([
            LibrarianSeeder::class,
        ]);

         $this->call([
        BooksTableSeeder::class,
    ]);
    }
    
    
}
