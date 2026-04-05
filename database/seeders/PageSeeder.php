<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed static pages: About, Impressum, Datenschutz, Barrierefreiheit.
     * Uses firstOrNew(slug) so re-seeding updates content without duplicates.
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
                'body' => $this->datenschutzBody(),
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

        foreach ($pages as $pageData) {
            $page = Page::firstOrNew(['slug' => $pageData['slug']]);
            $page->title = $pageData['title'];
            $page->body = $pageData['body'];
            $page->status = PostStatus::Published;
            if ($page->published_at === null) {
                $page->published_at = now();
            }
            $page->save();
        }
    }

    /**
     * Datenschutz-HTML (öffentlicher Blog + Kommentare, Newsletter, Gravatar).
     * IP-Aufbewahrung: 30 Tage (siehe config/comments.php und Artisan comments:anonymize-ips).
     */
    private function datenschutzBody(): string
    {
        return <<<'HTML'
<h2>Datenschutzerklärung</h2>

<h3>1. Verantwortlicher</h3>
<p>Alex Korezki<br>
c/o Postflex #10164<br>
Emsdettener Str. 10<br>
48268 Greven<br>
E-Mail: <a href="mailto:hallo@kopfsalatblog.de">hallo@kopfsalatblog.de</a></p>

<h3>2. Allgemeines zur Datenverarbeitung</h3>
<p>Diese Website wird auf einem Shared-Hosting-Server betrieben. Beim Aufruf der Website werden durch den Webserver automatisch Informationen in sogenannten Server-Logfiles gespeichert, die der Browser übermittelt. Dies sind:</p>
<ul>
<li>IP-Adresse des anfragenden Rechners</li>
<li>Datum und Uhrzeit des Zugriffs</li>
<li>Name und URL der abgerufenen Datei</li>
<li>übertragene Datenmenge</li>
<li>Browsertyp und -version</li>
<li>verwendetes Betriebssystem</li>
<li>Referrer-URL (zuvor besuchte Seite)</li>
</ul>
<p>Diese Daten werden ausschließlich zur Sicherstellung eines störungsfreien Betriebs der Website und zur Erkennung von Missbrauch erhoben. Eine Zusammenführung mit anderen Datenquellen erfolgt nicht. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse).</p>

<h3>3. Cookies und lokale Speicherung</h3>
<p><strong>Cookies (technisch notwendig):</strong> Diese Website verwendet technisch notwendige Cookies für die Sitzungsverwaltung (Session-Cookie, z. B. für eingeloggte Administratoren, Formulare und interaktive Bereiche) und die Sprachauswahl. Diese Cookies sind für den Betrieb der Website erforderlich. Es werden keine Tracking- oder Marketing-Cookies zu Werbezwecken eingesetzt. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.</p>
<p><strong>LocalStorage:</strong> Zusätzlich nutzt diese Website den lokalen Speicher des Browsers (<code>localStorage</code>) für die Dark-Mode-Einstellung sowie ein temporäres Bearbeitungs-Token für eigene Kommentare (Bearbeiten/Löschen innerhalb von 60 Minuten nach dem Absenden). Diese Daten verbleiben auf Ihrem Endgerät und werden nicht automatisch an den Server übermittelt; das Token wird nur bei entsprechenden Aktionen (Bearbeiten/Löschen) verwendet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.</p>

<h3>4. Kommentarfunktion</h3>
<p>Auf diesem Blog können Sie Kommentare zu Beiträgen hinterlassen. Dabei werden folgende Daten verarbeitet:</p>
<ul>
<li>Nickname (Pflichtfeld)</li>
<li>E-Mail-Adresse (freiwillig)</li>
<li>Kommentartext</li>
<li>IP-Adresse des abgebenden Anschlusses</li>
<li>Zeitpunkt der Erstellung</li>
<li>ein technisches Bearbeitungs-Token (gebunden an den Kommentar, für die in Abschnitt 3 genannte Bearbeitungsfunktion)</li>
</ul>
<p>Die <strong>IP-Adresse</strong> wird aus Sicherheitsgründen und zur Abwehr von Missbrauch sowie für den Fall gespeichert, dass durch einen Kommentar Rechte Dritter verletzt werden. Sie wird <strong>nach 30 Tagen ab Zeitpunkt der Kommentarerstellung automatisch gelöscht</strong> (technische Anonymisierung durch Entfernen des gespeicherten Werts). Die Frist entspricht der technischen Voreinstellung dieser Website und kann bei Bedarf angepasst werden; maßgeblich ist die jeweils hier veröffentlichte Fassung dieser Erklärung.</p>
<p>Kommentare von nicht eingeloggten Besuchern werden vor der öffentlichen Sichtbarkeit <strong>moderiert</strong>. Nach dem Absenden können Sie einen eigenen Kommentar <strong>innerhalb von 60 Minuten</strong> bearbeiten oder löschen, sofern das Bearbeitungs-Token im Browser vorhanden ist. Danach sind Änderungen nur noch durch den Betreiber möglich.</p>
<p>Zum Schutz vor automatisiertem Missbrauch können Anfragen (z. B. Kommentare, Newsletter) <strong>begrenzt werden</strong> (Rate-Limiting). Dabei kann die IP-Adresse vorübergehend in einem geschützten Systemzähler verarbeitet werden. Rechtsgrundlage für die Kommentarverarbeitung ist Art. 6 Abs. 1 lit. f DSGVO; soweit Sie eine E-Mail angeben, kann zusätzlich Art. 6 Abs. 1 lit. a DSGVO (Einwilligung) einschlägig sein.</p>

<h3>5. Newsletter</h3>
<p>Auf dieser Website können Sie einen kostenlosen Newsletter abonnieren. Dabei wird die <strong>E-Mail-Adresse</strong> erhoben und verarbeitet.</p>
<p>Das Abonnement erfolgt im <strong>Double-Opt-in-Verfahren (DOI)</strong>: Nach der Eingabe erhalten Sie eine E-Mail mit Bestätigungslink. Erst nach Klick auf diesen Link wird die Adresse für den Versand verwendet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. a DSGVO (Einwilligung).</p>
<p>Der Versand und die Verwaltung der Liste erfolgen über <strong>Brevo</strong> (Brevo SAS, Frankreich; vormals Sendinblue). Ihre E-Mail-Adresse wird dorthin übermittelt und dort gespeichert. Aktuelle Firmen- und Kontaktangaben: <a href="https://www.brevo.com/legal-notice/" rel="noopener noreferrer">brevo.com/legal-notice</a>. Mit Brevo besteht ein Auftragsverarbeitungsvertrag gemäß Art. 28 DSGVO. Die Datenschutzhinweise von Brevo: <a href="https://www.brevo.com/de/legal/privacypolicy/" rel="noopener noreferrer">brevo.com/de/legal/privacypolicy</a>.</p>
<p>Die Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen, z. B. über den Abmeldelink in jedem Newsletter oder per E-Mail an die oben genannte Adresse. Nach Widerruf wird die Adresse aus dem Verteiler entfernt, soweit keine gesetzlichen Aufbewahrungspflichten entgegenstehen.</p>

<h3>6. Gravatar (Kommentar-Avatare)</h3>
<p>Wenn Sie beim Kommentieren eine E-Mail-Adresse angeben, kann Ihr Browser zur Anzeige eines Profilbilds eine Anfrage an den Dienst <strong>Gravatar</strong> (Betreiber: Automattic Inc., USA) richten. Dabei wird typischerweise ein aus der E-Mail-Adresse gebildeter Hash in der URL verwendet. Es kann eine Übermittlung in ein Drittland stattfinden. Die Datenschutzhinweise von Automattic finden Sie unter <a href="https://automattic.com/privacy/" rel="noopener noreferrer">automattic.com/privacy</a>. Wird kein Bild gefunden, wird ein neutraler Platzhalter angezeigt. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (Darstellung der Kommentarfunktion). Sie können die Übermittlung vermeiden, indem Sie keine E-Mail-Adresse angeben (der Kommentar bleibt grundsätzlich möglich, sofern die Funktion dies vorsieht).</p>

<h3>7. Kontaktaufnahme per E-Mail</h3>
<p>Wenn Sie uns per E-Mail kontaktieren, verarbeiten wir die von Ihnen mitgeteilten Daten zur Bearbeitung der Anfrage. Eine Weitergabe an Dritte erfolgt nicht, soweit nicht gesetzlich vorgeschrieben. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO und ggf. Art. 6 Abs. 1 lit. b DSGVO bei vertragsbezogenen Anfragen.</p>

<h3>8. Administrationsbereich (Redaktion)</h3>
<p>Für die Pflege des Blogs wird ein geschützter Administrationsbereich genutzt. Zugangsdaten und Aktivitäten dort werden nur zur Bereitstellung und Sicherung des Dienstes verarbeitet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO bzw. bei Vertragsbeziehung mit Mitwirkenden Art. 6 Abs. 1 lit. b DSGVO.</p>

<h3>9. Ihre Rechte</h3>
<p>Sie haben – soweit die gesetzlichen Voraussetzungen erfüllt sind – folgende Rechte:</p>
<ul>
<li>Auskunft (Art. 15 DSGVO)</li>
<li>Berichtigung (Art. 16 DSGVO)</li>
<li>Löschung (Art. 17 DSGVO)</li>
<li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
<li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
<li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
</ul>
<p>Zur Ausübung Ihrer Rechte wenden Sie sich an die unter Ziffer 1 genannte Kontaktadresse. Außerdem haben Sie das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren.</p>

<h3>10. Hosting</h3>
<p>Die Website wird bei einem externen Dienstleister gehostet. Dabei können Zugriffs- und Serverdaten (u. a. IP-Adressen) auf dessen Systemen verarbeitet werden. Der Einsatz des Hosters erfolgt zur sicheren und effizienten Bereitstellung. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO. Mit dem Hoster besteht – soweit erforderlich – ein Auftragsverarbeitungsvertrag gemäß Art. 28 DSGVO.</p>

<h3>11. Speicherdauer und Löschung (Überblick)</h3>
<ul>
<li><strong>Server-Logfiles:</strong> werden beim Hosting-Provider nach <strong>14 Tagen</strong> automatisch gelöscht, sofern der Anbieter nichts Abweichendes vorsieht (Stand der Konfiguration zum Zeitpunkt dieser Erklärung).</li>
<li><strong>Session-Daten (Cookies):</strong> werden nach <strong>120 Minuten</strong> Inaktivität beendet bzw. entfernt (entspricht der eingestellten Session-Lebensdauer der Anwendung).</li>
<li><strong>Kommentar-IP-Adressen:</strong> automatische Löschung nach <strong>30 Tagen</strong> ab Kommentarzeitpunkt (siehe Ziffer 4).</li>
<li><strong>Kommentarinhalte</strong> (Nickname, Text, freiwillige E-Mail): werden gespeichert, solange der Kommentar besteht bzw. die Website ihn führt; eine Löschung können Sie anfragen (siehe Ziffer 8). Gesetzliche Aufbewahrungspflichten bleiben unberührt.</li>
<li><strong>Newsletter:</strong> E-Mail wird gelöscht bzw. aus der Liste entfernt nach Widerruf der Einwilligung, vorbehaltlich gesetzlicher Aufbewahrung.</li>
</ul>

<h3>12. Änderungen</h3>
<p>Diese Datenschutzerklärung wird bei Bedarf angepasst, insbesondere bei technischen oder rechtlichen Änderungen. Es gilt die jeweils auf dieser Seite veröffentlichte Fassung.</p>

<p><em>Stand: April 2026</em></p>
HTML;
    }
}
