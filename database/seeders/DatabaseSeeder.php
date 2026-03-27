<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Post::create([
            'title'    => 'Getting Started with Laravel',
            'category' => 'Technology',
            'author'   => 'Juan dela Cruz',
            'body'     => 'Laravel is a powerful PHP framework that makes web development enjoyable. It provides an expressive and elegant syntax that helps you build modern applications quickly and efficiently.',
            'status'   => 'published',
        ]);

        Post::create([
            'title'    => 'Why Vue.js is Great for Frontend',
            'category' => 'Technology',
            'author'   => 'Maria Santos',
            'body'     => 'Vue.js is a progressive JavaScript framework that is easy to pick up and integrates well with other libraries. It is perfect for building interactive user interfaces and single-page applications.',
            'status'   => 'published',
        ]);

        Post::create([
            'title'    => 'Understanding REST APIs',
            'category' => 'Tutorial',
            'author'   => 'Pedro Reyes',
            'body'     => 'A REST API is an architectural style for building web services. It uses HTTP methods like GET, POST, PUT, and DELETE to perform operations on resources represented as URLs.',
            'status'   => 'published',
        ]);

        Post::create([
            'title'    => 'My Draft Article on MySQL',
            'category' => 'Database',
            'author'   => 'Juan dela Cruz',
            'body'     => 'MySQL is one of the most popular relational database systems. This article is still being written...',
            'status'   => 'draft',
        ]);
    }
}