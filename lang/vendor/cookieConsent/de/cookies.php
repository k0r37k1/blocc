<?php

return [
    'title' => 'Datenschutzeinstellungen',
    'intro' => 'Wir verwenden Cookies und ähnliche Technologien, um unsere Website zu betreiben und Ihr Nutzungserlebnis zu verbessern.',
    'link' => 'Weitere Informationen finden Sie in unserer <a href=":url">Datenschutzerklärung</a>.',

    'essentials' => 'Nur notwendige',
    'all' => 'Alle akzeptieren',
    'customize' => 'Einstellungen anpassen',
    'manage' => 'Cookie-Einstellungen',
    'details' => [
        'more' => 'Details anzeigen',
        'less' => 'Details ausblenden',
    ],
    'save' => 'Auswahl speichern',
    'cookie' => 'Cookie',
    'purpose' => 'Zweck',
    'duration' => 'Laufzeit',
    'year' => 'Jahr|Jahre',
    'day' => 'Tag|Tage',
    'hour' => 'Stunde|Stunden',
    'minute' => 'Minute|Minuten',

    'categories' => [
        'essentials' => [
            'title' => 'Notwendige Cookies',
            'description' => 'Diese Cookies sind für den Betrieb der Website erforderlich und können nicht deaktiviert werden. Sie speichern keine persönlich identifizierbaren Informationen.',
        ],
        'functional' => [
            'title' => 'Funktionale Cookies',
            'description' => 'Diese Cookies ermöglichen erweiterte Funktionen und Personalisierungen, z. B. das Speichern von Anzeigeeinstellungen. Ohne diese Cookies sind einige Funktionen möglicherweise eingeschränkt.',
        ],
        'analytics' => [
            'title' => 'Analyse-Cookies',
            'description' => 'Diese Cookies helfen uns zu verstehen, wie Besucher unsere Website nutzen. Die Daten werden anonymisiert ausgewertet und dienen ausschließlich der Verbesserung unseres Angebots.',
        ],
        'optional' => [
            'title' => 'Optionale Cookies',
            'description' => 'Diese Cookies bieten zusätzliche Funktionen, die Ihr Nutzungserlebnis verbessern können. Ihre Abwesenheit beeinträchtigt die grundlegende Nutzung der Website nicht.',
        ],
        'marketing' => [
            'title' => 'Marketing & Werbung',
            'description' => 'Diese Cookies werden verwendet, um Ihnen auf Ihre Interessen zugeschnittene Werbung anzuzeigen und den Erfolg unserer Werbekampagnen zu messen.',
        ],
    ],

    'defaults' => [
        'consent' => 'Speichert die Cookie-Einstellungen des Nutzers.',
        'session' => 'Identifiziert die aktuelle Browser-Sitzung des Nutzers.',
        'csrf' => 'Schützt die Website vor Cross-Site-Request-Forgery-Angriffen (CSRF).',
        '_ga' => 'Haupt-Cookie von Google Analytics; ermöglicht die Unterscheidung einzelner Besucher.',
        '_ga_ID' => 'Wird von Google Analytics zur Aufrechterhaltung des Sitzungsstatus verwendet.',
        '_gid' => 'Wird von Google Analytics zur Identifizierung des Nutzers verwendet.',
        '_gat' => 'Wird von Google Analytics zur Drosselung der Anfragerate verwendet.',
    ],
];
