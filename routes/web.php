<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminTicketPdfController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('customer.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::livewire('/dashboard', 'pages::customer.dashboard')->name('dashboard');
        Route::livewire('/tickets', 'pages::customer.ticket-index')->name('tickets.index');
        Route::livewire('/tickets/create', 'pages::customer.ticket-create')->name('tickets.create');
        Route::livewire('/tickets/{ticket}/edit', 'pages::customer.ticket-edit')->name('tickets.edit');
        Route::livewire('/tickets/{ticket}', 'pages::customer.ticket-show')->name('tickets.show');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::livewire('/dashboard', 'pages::admin.dashboard')->name('dashboard');
        Route::livewire('/tickets', 'pages::admin.ticket-index')->name('tickets.index');
        Route::livewire('/tickets/{ticket}', 'pages::admin.ticket-show')->name('tickets.show');

        Route::get('/tickets/{ticket}/pdf', [AdminTicketPdfController::class, 'export'])
            ->name('tickets.pdf');
    });

require __DIR__.'/settings.php';
