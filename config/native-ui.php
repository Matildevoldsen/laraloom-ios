<?php

/**
 * Native UI — Theme Tokens
 *
 * Published via `php artisan vendor:publish --tag=native-ui-config`.
 * Edit to customize your app's visual identity in one place.
 *
 * For dynamic per-tenant theming, use Nativephp\NativeUi\Theme::merge([...])
 * from a service provider. Runtime merges deep-merge on top of these values.
 *
 * Decision log: /docs/NATIVE-UI-REWRITE-PLAN.md (D — theme layer)
 */

return [

    /*
    |---------------------------------------------------------------------------
    | Theme
    |---------------------------------------------------------------------------
    |
    | 14 color tokens, 4 radii, 4 font sizes, font family.
    |
    | "on-X" means "color of content placed ON a surface of color X"
    |   — i.e., text/icons on that background.
    |
    | Dark mode is auto-derived from `light` when `dark` is not set. To opt
    | into explicit dark tokens, fill out the `dark` block.
    |
    */

    'theme' => [

        'light' => [
            // Primary brand color — used for filled buttons, active states, key accents.
            'primary' => '#DB2777',
            'on-primary' => '#FFFFFF',

            // Secondary / muted action color.
            'secondary' => '#7C3AED',
            'on-secondary' => '#FFFFFF',

            // Surface = cards, sheets, dialogs. Background = page root.
            'surface' => '#FFFFFF',
            'on-surface' => '#000000',
            'background' => '#FAF9FB',
            'on-background' => '#000000',

            // Surface variant = filled text fields, muted tonal surfaces.
            // on-surface-variant = muted label/hint text on those surfaces.
            'surface-variant' => '#F1EDF3',
            'on-surface-variant' => '#475569',

            // Outline = neutral borders (text fields, dividers, cards).
            'outline' => '#DDD5E2',

            // Destructive / error actions and messages.
            'destructive' => '#DC2626',
            'on-destructive' => '#FFFFFF',

            // Tertiary accent — for highlights, badges, emphasis not covered by primary.
            'accent' => '#F43F8C',
            'on-accent' => '#FFFFFF',
        ],

        'dark' => [
            // Leave empty or partial to auto-derive from `light` (luminance inversion).
            // Specify any token here to override the derived value.
            'primary' => '#F43F8C',
            'on-primary' => '#FFFFFF',

            'secondary' => '#A78BFA',
            'on-secondary' => '#FFFFFF',

            'surface' => '#17131D',
            'on-surface' => '#FFFFFF',
            'background' => '#0B0910',
            'on-background' => '#FFFFFF',

            'surface-variant' => '#241B2B',
            'on-surface-variant' => '#B9AFBF',

            'outline' => '#3B3243',

            'destructive' => '#cf1729',
            'on-destructive' => '#FFFFFF',

            'accent' => '#F472B6',
            'on-accent' => '#18020D',
        ],

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
        'radius-full' => 9999,

        // Font size scale (points / sp).
        'font-sm' => 14,
        'font-md' => 16,
        'font-lg' => 20,
        'font-xl' => 24,

        // 'System' resolves to the platform default (San Francisco on iOS, Roboto on Android).
        // Use a specific family name to load a custom font.
        'font-family' => 'System',
    ],

];
