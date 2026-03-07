<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Fonts
    |--------------------------------------------------------------------------
    |
    | Curated list of self-hosted fonts for heading and body text selection.
    | All fonts are served from /public/fonts/ (GDPR-safe, no external CDN).
    | Font files: woff2 format with latin subset.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Code Themes (Phiki)
    |--------------------------------------------------------------------------
    |
    | Curated pairs of light/dark syntax highlighting themes.
    | Keys are display labels, values are [light, dark] Phiki theme values.
    |
    */

    'code_themes' => [
        'GitHub' => ['github-light', 'github-dark'],
        'Dracula' => ['dracula-soft', 'dracula'],
        'Nord' => ['nord', 'nord'],
        'One Dark' => ['one-light', 'one-dark-pro'],
        'Solarized' => ['solarized-light', 'solarized-dark'],
        'Material' => ['material-theme-lighter', 'material-theme-ocean'],
        'Catppuccin' => ['catppuccin-latte', 'catppuccin-mocha'],
        'Gruvbox' => ['gruvbox-light-medium', 'gruvbox-dark-medium'],
        'Vitesse' => ['vitesse-light', 'vitesse-dark'],
        'Rose Pine' => ['rose-pine-dawn', 'rose-pine-moon'],
        'Tokyo Night' => ['tokyo-night', 'tokyo-night'],
        'Monokai' => ['monokai', 'monokai'],
    ],

    'fonts' => [

        // Sans-Serif
        'Inter' => '"Inter", sans-serif',
        'DM Sans' => '"DM Sans", sans-serif',
        'Lato' => '"Lato", sans-serif',
        'Poppins' => '"Poppins", sans-serif',
        'Source Sans 3' => '"Source Sans 3", sans-serif',

        // Serif
        'Lora' => '"Lora", serif',
        'Merriweather' => '"Merriweather", serif',
        'Playfair Display' => '"Playfair Display", serif',

    ],

];
