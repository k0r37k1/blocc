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
                'body' => '<h2>Erklaerung zur Barrierefreiheit</h2>'
                    .'<p>Diese Website wird mit dem Ziel betrieben, Kopfsalat.blog fuer alle Menschen zugaenglich zu gestalten – unabhaengig von individuellen Faehigkeiten oder verwendeten Technologien.</p>'
                    .'<h3>Anspruch</h3>'
                    .'<p>Diese Website orientiert sich an den <strong>Web Content Accessibility Guidelines (WCAG) 2.2</strong> auf Konformitaetsstufe <strong>AA</strong>. Diese Richtlinien stellen sicher, dass Inhalte fuer ein moeglichst breites Publikum zugaenglich sind, einschliesslich Menschen mit Sehbehinderungen, motorischen Einschraenkungen, kognitiven Beeintraechtigungen und anderen Behinderungen.</p>'
                    .'<h3>Umgesetzte Massnahmen</h3>'
                    .'<p>Folgende Massnahmen zur Barrierefreiheit sind umgesetzt:</p>'
                    .'<ul>'
                    .'<li>Semantisches HTML fuer eine klare Dokumentstruktur</li>'
                    .'<li>Ausreichende Farbkontraste gemaess WCAG AA-Standards</li>'
                    .'<li>Vollstaendige Tastaturbedienbarkeit aller interaktiven Elemente</li>'
                    .'<li>Alternativtexte fuer informative Bilder</li>'
                    .'<li>ARIA-Attribute wo noetig, um assistive Technologien zu unterstuetzen</li>'
                    .'<li>Responsives Design fuer verschiedene Bildschirmgroessen und Zoom-Stufen</li>'
                    .'<li>Unterstuetzung fuer Dark Mode und individuelle Farbschemata</li>'
                    .'</ul>'
                    .'<h3>Bekannte Einschraenkungen</h3>'
                    .'<p>Trotz aller Bemuehungen koennen einzelne Inhalte noch nicht vollstaendig barrierefrei sein. Es wird kontinuierlich daran gearbeitet, bestehende Barrieren zu identifizieren und zu beseitigen.</p>'
                    .'<h3>Feedback &amp; Kontakt</h3>'
                    .'<p>Bei Barrieren oder Verbesserungsvorschlaegen freue ich mich ueber eine Nachricht. Gemeldete Probleme werden zeitnah bearbeitet.</p>'
                    .'<p>Kontaktdaten sind im <a href="/seite/impressum">Impressum</a> zu finden.</p>',
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
