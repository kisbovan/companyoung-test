<?php

use App\Http\Controllers\CompanYoungController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [CompanYoungController::class, 'index']);

Route::get('/add-unique-field', [CompanYoungController::class, 'addUniqueField']);

Route::post('/save-unique-field', [CompanYoungController::class, 'saveUniqueField']);

Route::get('/add-person', [CompanYoungController::class, 'addPerson']);

Route::post('/save-person', [CompanYoungController::class, 'savePerson']);

Route::post('/search', [CompanYoungController::class, 'search']);

Route::post('/sort', [CompanYoungController::class, 'sort']);
