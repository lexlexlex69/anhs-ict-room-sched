<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;


Route::get('/', [AuthController::class, 'landing']);
Route::get('loginfront', [AuthController::class, 'login']);
Route::post('login', [AuthController::class, 'Authlogin']);
Route::get('logout', [AuthController::class, 'logout']);
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'postRegister'])->name('post.register');

// Public reservation routes
Route::post('/reservation/check', [ReservationController::class, 'checkAvailability'])->name('reservation.check');
Route::post('/reservation/store', [ReservationController::class, 'store'])->name('reservation.store');
Route::post('/reservation/suggest-slots', [ReservationController::class, 'suggestOptimalSlots'])
  ->name('reservation.suggest-slots');
Route::post('/reservation/check-status', [ReservationController::class, 'checkTicketStatus'])
  ->name('reservation.check-status');


//notification 

Route::get('/get-notifications', [ScheduleController::class, 'getNotifications']);
Route::post('/mark-notifications-as-read', [ScheduleController::class, 'markNotificationsAsRead']);

Route::group(['middleware' => 'admin'], function () {

  Route::get('admin/schedules/AllList', [DashboardController::class, 'dashboard']);

  //room
  Route::get('admin/room/list', [RoomController::class, 'list']);
  Route::get('admin/room/add', [RoomController::class, 'add']);
  Route::post('admin/room/add', [RoomController::class, 'insert']);
  Route::get('admin/room/edit/{id}', [RoomController::class, 'edit']);
  Route::post('admin/room/edit/{id}', [RoomController::class, 'update']);
  Route::get('admin/room/delete/{id}', [RoomController::class, 'delete']);

  //Teacher
  Route::get('admin/teacher/list', [UserController::class, 'list']);
  Route::get('admin/teacher/add', [UserController::class, 'add']);
  Route::post('admin/teacher/add', [UserController::class, 'insert']);
  Route::get('admin/teacher/edit/{id}', [UserController::class, 'edit']);
  Route::post('admin/teacher/edit/{id}', [UserController::class, 'update']);
  Route::get('admin/teacher/delete/{id}', [UserController::class, 'delete']);


  // Admin view teacher's schedule
  Route::get('admin/teacher/schedule/{id}', [ScheduleController::class, 'viewTeacherSchedule'])->name('admin.teacher.schedule');

  //Admin view all schedule 
  Route::get('admin/schedule/all', [ScheduleController::class, 'allSchedules'])->name('admin.schedule.all');
  Route::get('admin/schedule/calendar', [ScheduleController::class, 'adminCalendar'])->name('admin.schedule.calendar');
  Route::get('admin/calendar/day-details', [ScheduleController::class, 'dayDetails'])->name('admin.calendar.day-details');
  //view all reservations
  Route::get('admin/reservations', [ReservationController::class, 'adminIndex'])->name('admin.reservations');
  Route::post('admin/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('admin.reservations.cancel');

  Route::get('admin/account', [UserController::class, 'MyAccount']);
  Route::post('admin/account', [UserController::class, 'UpdateMyAccount']);
});


Route::group(['middleware' => 'teacher'], function () {


  Route::get('teacher/account', [UserController::class, 'MyAccount']);
  Route::post('teacher/account', [UserController::class, 'UpdateMyAccount']);


  // Teacher weekly schedule routes
  Route::get('teacher/schedule/weekly', [ScheduleController::class, 'weeklyScheduleList'])->name('teacher.schedule.weekly');
  Route::post('teacher/schedule/weekly', [ScheduleController::class, 'storeWeeklySchedule'])->name('teacher.schedule.weekly.store');
  Route::delete('teacher/schedule/weekly/delete/{id}', [ScheduleController::class, 'deleteWeeklySchedule'])->name('teacher.schedule.weekly.delete');

  Route::get('teacher/schedule/today', [ScheduleController::class, 'todaySchedule'])
    ->name('teacher.schedule.today');

  Route::get('teacher/schedule/calendar', [ScheduleController::class, 'teacherScheduleCalendar'])->name('teacher.schedule.calendar');

  Route::get('teacher/reservations/create', [ReservationController::class, 'teacherCreate'])->name('teacher.reservations.create');
});
