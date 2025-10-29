<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\MyAgendaController;
use App\Http\Controllers\API\WhatsAppController;
use App\Http\Controllers\API\ParticipantController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\OfficeAgendaController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // Users - Only Super Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/restore', [UserController::class, 'restore']);

    });

    // Rooms - All authenticated users can view, Super Admin & Admin can manage
    Route::get('rooms', [RoomController::class, 'index']);
    Route::get('rooms/{id}', [RoomController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('rooms', [RoomController::class, 'store']);
        Route::put('rooms/{id}', [RoomController::class, 'update']);
        Route::delete('rooms/{id}', [RoomController::class, 'destroy']);
    });

    // Participants - All authenticated users can view, Super Admin & Admin can manage
    Route::get('participants', [ParticipantController::class, 'index']);
    Route::get('participants/{id}', [ParticipantController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('participants', [ParticipantController::class, 'store']);
        Route::put('participants/{id}', [ParticipantController::class, 'update']);
        Route::delete('participants/{id}', [ParticipantController::class, 'destroy']);
    });


// Office Agenda routes
Route::apiResource('office-agendas', OfficeAgendaController::class);
Route::middleware('role:super_admin,admin')->group(function () {
    // Delete specific attachment
    Route::delete('office-agendas/{officeAgenda}/attachments', [OfficeAgendaController::class, 'deleteAttachment']);
});

    // My Agendas - All authenticated users
    Route::apiResource('my-agendas', MyAgendaController::class);
    Route::get('public-agendas', [MyAgendaController::class, 'publicAgendas']);

    // Announcements - Super Admin & Admin can manage, all can view
    Route::get('announcements', [AnnouncementController::class, 'index']);
    Route::get('announcements/{id}', [AnnouncementController::class, 'show']);
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('announcements', [AnnouncementController::class, 'store']);
        Route::put('announcements/{id}', [AnnouncementController::class, 'update']);
        Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy']);
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('whatsapp/status', [WhatsAppController::class, 'status']);
        Route::get('whatsapp/logs', [WhatsAppController::class, 'logs']);
        Route::post('whatsapp/send-test', [WhatsAppController::class, 'sendTest']);
    });
});
