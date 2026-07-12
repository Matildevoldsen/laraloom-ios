<?php

use App\NativeComponents\Laraloom\Admin;
use App\NativeComponents\Laraloom\Compose;
use App\NativeComponents\Laraloom\DeleteAccount;
use App\NativeComponents\Laraloom\EditPost;
use App\NativeComponents\Laraloom\Login;
use App\NativeComponents\Laraloom\PostDetail;
use App\NativeComponents\Laraloom\Profile;
use App\NativeComponents\Laraloom\ProjectDetail;
use App\NativeComponents\Laraloom\Projects;
use App\NativeComponents\Laraloom\PublicProfile;
use App\NativeComponents\Laraloom\Register;
use App\NativeComponents\Laraloom\Today;
use App\NativeComponents\Layouts\LaraloomTabsLayout;
use App\NativeComponents\Layouts\NativeStackLayout;
use Illuminate\Support\Facades\Route;

Route::nativeGroup(LaraloomTabsLayout::class, function (): void {
    Route::native('/', Today::class)->name('today');
    Route::native('/projects', Projects::class)->name('projects');
    Route::native('/profile', Profile::class)->name('profile');
    Route::native('/people/{id}', PublicProfile::class)->name('people.show');
    Route::native('/compose', Compose::class)->name('compose');
    Route::native('/admin', Admin::class)->name('admin');
    Route::native('/account/delete', DeleteAccount::class)->name('account.delete');
    Route::native('/posts/{id}/edit', EditPost::class)->name('posts.edit');
    Route::native('/posts/{id}', PostDetail::class)->name('posts.show');
    Route::native('/projects/{slug}', ProjectDetail::class)->name('projects.show');
});

Route::native('/login', Login::class)
    ->layout(NativeStackLayout::class)
    ->name('login');

Route::native('/register', Register::class)
    ->layout(NativeStackLayout::class)
    ->name('register');
