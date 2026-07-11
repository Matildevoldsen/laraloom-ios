# Laraloom Mobile

The fully native iOS and Android client for [Laraloom](https://laraloom-production-sbummi.laravel.cloud): a community feed for Laravel news, packages, projects and people.

Laraloom Mobile is built with Laravel 13, [NativePHP Mobile](https://nativephp.com), and a small [NativeUI fork](https://github.com/Matildevoldsen/native-ui/tree/codex/primary-context-menu). Its screens are PHP components rendered as real SwiftUI and Jetpack Compose controls—complete with Liquid Glass navigation, semantic light/dark themes, native search and pull-to-refresh.

## Features

- Live, searchable Laravel feed and project directory
- Secure registration and sign-in using scoped Laravel Sanctum tokens
- Hardware-backed token storage through NativePHP Secure Storage
- Following, reactions, bookmarks and community publishing
- Replies, conversations and reposts
- Native long-press post menus on iOS and Android
- Owner-only post editing and deletion
- Role-gated moderation tools shared with the Laraloom web app
- In-app account deletion
- Branded light and dark launch experiences

## Architecture

The app never scrapes content or ships copied articles. It consumes the versioned Laraloom API at `/api/v1`; the server remains authoritative for validation, ownership, moderation and source attribution.

`LaraloomApiClient` is the single network boundary. `SecureTokenStore` is the only component allowed to access Keychain or Android Keystore state. Native components remain small and screen-focused.

## Local development

Requirements:

- PHP 8.4 and Composer
- Xcode for iOS or Android Studio for Android
- NativePHP Mobile access
- A NativePHP Secure Storage license for Composer installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan native:install ios
php artisan native:run ios --watch
```

Set `LARALOOM_API_URL` to another `/api/v1` endpoint when developing against a local backend.

For Android, replace `ios` with `android` in the install and run commands.

## Quality checks

```bash
vendor/bin/pint --format agent
php artisan test --compact
php artisan view:cache
php artisan native:validate --component=Laraloom/Today
```

## License

Laraloom Mobile is open source under the [MIT License](LICENSE).
