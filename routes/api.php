<?php

use App\Http\Controllers\Api\AdminAnnouncementController;
use App\Http\Controllers\Api\AdminJuriController;
use App\Http\Controllers\Api\AdminKaryaController;
use App\Http\Controllers\Api\AdminTeamController;
use App\Http\Controllers\Api\AdminTicketController;
use App\Http\Controllers\api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\CriteriaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JuriController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PesertaController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ScoreboardController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:sanctum', 'role:rol_1a2b3c,rol_4d5e6f,rol_7g8h9i,rol_kobh21j', 'throttle:api'])->group(function () {
    Route::get('/admin/competitions', [CompetitionController::class, 'index']);
    Route::post('/admin/competitions/', [CompetitionController::class, 'store']);
    Route::put('/admin/competitions/{id}', [CompetitionController::class, 'update']);
    Route::delete('/admin/competitions/{id}', [CompetitionController::class, 'destroy']);

    Route::post('/admin/staff', [StaffController::class, 'store']);
    Route::get('/admin/staff', [StaffController::class, 'index']);
    Route::put('/admin/staff/{id}', [StaffController::class, 'update']);
    Route::delete('/admin/staff/{id}', [StaffController::class, 'destroy']);

    Route::get('/admin/teams/all', [AdminTeamController::class, 'getAllTeams']);
    Route::get('/admin/team-folders', [AdminTeamController::class, 'getFolders']);
    Route::get('/admin/team-folders/{competitionId}', [AdminTeamController::class, 'getTeams']);
    Route::put('/admin/teams/{id}/status', [AdminTeamController::class, 'updateStatus']);

    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::get('/admin/karya-folders', [AdminKaryaController::class, 'getFolders']);
    Route::get('/admin/karya-folders/{competitionId}', [AdminKaryaController::class, 'getKarya']);

    Route::post('/admin/announcements', [AdminAnnouncementController::class, 'store']);
    Route::put('/admin/announcements/{id}', [AdminAnnouncementController::class, 'update']);
    Route::get('/admin/announcements', [AdminAnnouncementController::class, 'index']);
    Route::delete('/admin/announcements/{id}', [AdminAnnouncementController::class, 'destroy']);

    Route::get('/admin/teams/{id}/announcements', [AdminAnnouncementController::class, 'getTeamAnnouncements']);

    Route::get('/juri-management', [AdminJuriController::class, 'index']);
    Route::post('/juri-management', [AdminJuriController::class, 'store']);
    Route::delete('/juri-management/{id}', [AdminJuriController::class, 'destroy']);

    Route::put('/juri-management/{id}', [AdminJuriController::class, 'update']);

    Route::get('/criteria/{competitionId}', [CriteriaController::class, 'index']);
    Route::post('/criteria', [CriteriaController::class, 'store']);
    Route::put('/criteria/{id}', [CriteriaController::class, 'update']);
    Route::delete('/criteria/{id}', [CriteriaController::class, 'destroy']);

    Route::get('/admin/leaderboard/{competitionId}', [LeaderboardController::class, 'index']);
    Route::post('/admin/leaderboard/{competitionId}/finalize', [LeaderboardController::class, 'finalize']);

    Route::post('/admin/scan-ticket', [AdminTicketController::class, 'scanTicket']);
    Route::get('/admin/attendance', [AdminTicketController::class, 'getAttendanceList']);

    Route::post('/admin/competitions/{competition_id}/generate-all-scoreboards', [ScoreboardController::class, 'generateCompetitionBundle']);

    Route::prefix('admin/attendance')->group(function () {
        Route::get('/events', [AttendanceController::class, 'getEvents']);
        Route::post('/events', [AttendanceController::class, 'createEvent']);
        Route::get('/events/{eventId}/attendees', [AttendanceController::class, 'getAttendees']);
        Route::post('/events/{eventId}/scan', [AttendanceController::class, 'scanQr']);

        Route::put('/events/{eventId}', [AttendanceController::class, 'updateEvent']);
        Route::delete('/events/{eventId}', [AttendanceController::class, 'deleteEvent']);
    });
});

Route::middleware(['auth:sanctum', 'role:4', 'throttle:api'])->group(function () {
    Route::prefix('registration')->group(function () {
        Route::post('/step1', [RegistrationController::class, 'storeStep1']);
        Route::post('/upload', [RegistrationController::class, 'uploadFile']);
        Route::delete('/upload', [RegistrationController::class, 'deleteFile']);
        Route::get('/draft/{competitionId}', [RegistrationController::class, 'getDraft']);
        Route::post('/finalize', [RegistrationController::class, 'finalizeRegistration']);
    });
    Route::get('/dashboard-status', [PesertaController::class, 'getDashboardStatus']);
    Route::get('/tiket-finalis', [PesertaController::class, 'getTiketFinalis']);
    Route::post('/submission/upload', [SubmissionController::class, 'upload']);
    Route::get('/submission', [SubmissionController::class, 'get']);
    Route::get('/team-profile', [TeamController::class, 'getProfile']);

    Route::get('/peserta/announcements', [AdminAnnouncementController::class, 'getAnnouncements']);
});

Route::middleware(['auth:sanctum', 'role:rol_jms02ks6', 'throttle:api'])->group(function () {
    Route::prefix('juri')->group(function () {
        Route::get('/dashboard', [JuriController::class, 'getDashboard']);
        Route::get('/teams', [JuriController::class, 'getDaftarTim']);
        Route::get('/penilaian/{teamId}', [JuriController::class, 'getFormPenilaian']);
        Route::post('/penilaian/{teamId}', [JuriController::class, 'submitPenilaian']);
        Route::post('/assignments/{assignment_id}/signature', [ScoreboardController::class, 'saveJurySignature']);
        Route::get('/standings', [JuriController::class, 'getStandings']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-password', [AuthController::class, 'updatePassword']);
    Route::get('/me', [AuthController::class, 'getMe']);
});

Route::get('/competitions', [CompetitionController::class, 'getActiveCompetitions']);
Route::get('/competitions/{slug}', [CompetitionController::class, 'show']);
