<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BooksTableSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => 'Industrial psychology',
                'author' => 'Emmanuel B. De leon',
            ],
            [
                'title' => 'General psychology',
                'author' => 'Consuelo G. Sevilla',
            ],
            [
                'title' => 'Man in his nature condition',
                'author' => 'Eddie R. Babor',
            ],
            [
                'title' => 'Philosophy of man',
                'author' => 'Manuel B. Dy, Jr.',
            ],
            [
                'title' => 'Pscyhology applied to business and industry',
                'author' => 'C. Sanchez',
            ],
            [
                'title' => 'Human resource development',
                'author' => 'Carmela D. Ortigas',
            ],
            [
                'title' => 'Economics: Principles and applications',
                'author' => 'Achilles C. Costales',
            ],
            [
                'title' => 'Anagerial economics',
                'author' => 'Bernardo M. Villegas',
            ],
            [
                'title' => 'Labor economics',
                'author' => 'Leopoldo J. Dejillaz',
            ],
            [
                'title' => 'Income taxation',
                'author' => 'Edwin G. Valencia',
            ],
            [
                'title' => 'Textbook on family planning',
                'author' => 'Lydia Quirolgico',
            ],
            [
                'title' => 'Contemporary social problems and issues',
                'author' => 'Custodiosa A. Sanchez',
            ],
            [
                'title' => 'Current social issues',
                'author' => 'Jaraba Perez',
            ],
            [
                'title' => 'Theory and practice of public administration in the philippines',
                'author' => 'Avelino P. Tendero',
            ],
            [
                'title' => 'Career civil service sub-professional and professional reviewer',
                'author' => 'a.v.b printing press',
            ],
            [
                'title' => 'Solutions to problems in college algebra',
                'author' => 'Matias A. Arreola',
            ],
            [
                'title' => 'Filipino 1: sining ng komunikasyon para sa tersyara',
                'author' => 'Evelyn B.',
            ],
            [
                'title' => 'Retorika',
                'author' => 'Simplicio P. Bisa',
            ],
            [
                'title' => 'Sining ng pagbigkas at pagsulat na pakikipagtalastasan',
                'author' => 'Elenta Decal-Mendoza',
            ],
            [
                'title' => 'Ang dinig ng pakikipagtalastasan sa kolehiyo',
                'author' => 'Erlinda Mariano Santiago',
            ],
            [
                'title' => 'Physical Fitness for College freshman',
                'author' => 'Virgina D. Oyco',
            ],
            [
                'title' => 'Physical education handbook',
                'author' => 'Nicanor Reyes, Sr.',
            ],
            [
                'title' => 'Business statistics for MBA by matt L. Martin',
                'author' => 'Virgina D. Oyco',
            ],
            [
                'title' => 'Basic electronics',
                'author' => 'Ricardo C. Asin',
            ],
            [
                'title' => 'Homo Sapiens structure and function',
                'author' => 'Santos',
            ],
        ];

        
        foreach ($books as $key => $book) {
            $books[$key] = array_merge($book, [
                'barcode' => 'BK' . str_pad($key + 1, 4, '0', STR_PAD_LEFT), // Simple sequential barcode
                'copies' => rand(1, 5), // Random copies between 1-5
                'available_copies' => rand(1, 5), // Set available copies
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        DB::table('books')->insert($books);
    

}
}