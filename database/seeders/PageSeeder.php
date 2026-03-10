<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed static pages: About, Impressum, Datenschutz, Barrierefreiheit.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Ueber mich',
                'slug' => 'ueber-mich',
                'body' => '<p>Willkommen auf meinem Blog. Hier schreibe ich ueber Softwareentwicklung, Technologie und alles, was mich bewegt.</p>',
            ],
            [
                'title' => 'Impressum',
                'slug' => 'impressum',
                'body' => '<p>Angaben gemaess 5 TMG. Kontaktinformationen und verantwortliche Stelle werden hier aufgefuehrt.</p>',
            ],
            [
                'title' => 'Datenschutz',
                'slug' => 'datenschutz',
                'body' => '<p>Diese Datenschutzerklaerung klaert ueber die Art, den Umfang und Zweck der Verarbeitung personenbezogener Daten auf dieser Website auf.</p>',
            ],
            [
                'title' => 'Barrierefreiheit',
                'slug' => 'barrierefreiheit',
                'body' => '',
            ],
        ];

        foreach ($pages as $page) {
            Page::create([
                ...$page,
                'status' => PostStatus::Published,
                'published_at' => now(),
            ]);
        }
    }
}
