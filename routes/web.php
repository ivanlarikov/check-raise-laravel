<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    /*Mail::raw('Text to e-mail', function($message)
    {
        //$message->from('keyur@rigicglobalsolutions.com', 'Laravel');
    
        $message->to('keyur@rigicglobalsolutions.com')->cc('hiral@rigicgspl.com')->subject('Welcome!');
    });
    echo "Hello";*/
});
