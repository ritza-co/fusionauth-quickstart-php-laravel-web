<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('index');
});

// Route::get('/auth/redirect', function () {
Route::get('/login', function () {
    return Socialite::driver('fusionauth')->redirect();
    // $url = Socialite::driver('fusionauth')->stateless()->redirect()->getTargetUrl();
    // $url = str_replace('http://host.docker.internal', 'http://localhost', $url);
    // return redirect($url);
})->name('login');

Route::get('/auth/callback', function () {
    error_log( 'callback 1');

    /** @var \SocialiteProviders\Manager\OAuth2\User $user */
    $user = Socialite::driver('fusionauth')->user();

    error_log( 'callback 2');

    // Let's create a new entry in our users table (or update if it already exists) with some information from the user
    // $user = User::updateOrCreate([
    //     'fusionauth_id' => $user->id,
    // ], [
    //     'name' => $user->name,
    //     'email' => $user->email,
    //     'fusionauth_access_token' => $user->token,
    //     'fusionauth_refresh_token' => $user->refreshToken,
    // ]);

    $localUser = new User();
    $localUser->name = $user->name;
    $localUser->email = $user->email;
    // $localUser->password = 'plain_text_password'; // You can set the password without hashing
    $localUser->fusionauth_id = $user->id;
    $localUser->fusionauth_access_token = $user->token;
    $localUser->fusionauth_refresh_token = $user->refreshToken;

    Auth::login($user);
    return redirect('/account');
});

// Route::get('/login', function () {
//     return view('index');
// });

// Route::get('/logout', function () {
//     return view('index');
// });

Route::get('/account', function () {
    return view('account', ['email' => Auth::user()->email]);
});

Route::get('/change', function () {
    $state = [
        'error' => false,
        'hasChange' => false,
        'total' => '',
        'nickels' => '',
        'pennies' => '',
    ];
    return view('change', ['state' => $state, 'email' => 'temp@example.com']);
});

Route::post('/change', function (Request $request) {
    $amount = $request->input('amount');
    $state = [
        'error' => false,
        'hasChange' => true,
        'total' => '',
        'nickels' => '',
        'pennies' => '',
    ];

    $total = floor(floatval($amount) * 100) / 100;
    $state['total'] = is_nan($total) ? '' : number_format($total, 2);

    $nickels = floor($total / 0.05);
    $state['nickels'] = number_format($nickels);

    $pennies = ($total - (0.05 * $nickels)) / 0.01;
    $state['pennies'] = ceil(floor($pennies * 100) / 100);

    $state['error'] = !preg_match('/^(\d+(\.\d*)?|\.\d+)$/', $amount);

    return view('change', ['state' => $state, 'email' => 'temp@example.com']);
});
