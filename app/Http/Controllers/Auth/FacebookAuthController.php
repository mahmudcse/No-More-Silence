<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class FacebookAuthController extends Controller
{
    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            // In local and testing environments without live Meta credentials, simulate Facebook Login
            if (app()->environment(['local', 'testing'])) {
                $requestedName = trim((string) $request->query('name', ''));
                if ($requestedName === '') {
                    $requestedName = 'Mahmudul Hasan';
                }

                $avatar = 'https://ui-avatars.com/api/?name='.urlencode($requestedName).'&background=1877F2&color=fff';

                $user = User::firstOrCreate(
                    ['email' => 'facebook.demo@nomoresilence.test'],
                    [
                        'name' => $requestedName,
                        'facebook_id' => 'fb_demo_1001',
                        'avatar' => $avatar,
                        'email_verified_at' => now(),
                    ]
                );

                if ($user->name !== $requestedName || $user->avatar !== $avatar) {
                    $user->name = $requestedName;
                    $user->avatar = $avatar;
                    $user->save();
                }

                Auth::login($user, remember: true);

                return redirect()->intended(route('home', absolute: false));
            }

            return redirect()->route('login')->with('error', 'Facebook OAuth is not configured. Please add FACEBOOK_CLIENT_ID and FACEBOOK_CLIENT_SECRET to .env.');
        }

        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $facebookId = (string) $facebookUser->getId();
            $email = $facebookUser->getEmail() ?? "fb_{$facebookId}@facebook.local";
            $rawUser = is_array($facebookUser->user) ? $facebookUser->user : [];
            $name = $facebookUser->getName()
                ?: ($rawUser['name'] ?? null)
                ?: trim(($rawUser['first_name'] ?? '').' '.($rawUser['last_name'] ?? ''))
                ?: ($facebookUser->getNickname() ?? null)
                ?: ($facebookUser->getEmail() ? explode('@', $facebookUser->getEmail())[0] : 'Facebook User');
            $avatar = $facebookUser->getAvatar();

            // Find or create user
            $user = User::where('facebook_id', $facebookId)->first();

            if (! $user) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $user->facebook_id = $facebookId;
                    if (! $user->avatar && $avatar) {
                        $user->avatar = $avatar;
                    }
                    $user->save();
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'facebook_id' => $facebookId,
                        'avatar' => $avatar,
                        'email_verified_at' => now(),
                        'password' => null,
                    ]);
                }
            } else {
                if ($avatar && $user->avatar !== $avatar) {
                    $user->avatar = $avatar;
                    $user->save();
                }
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home', absolute: false));
        } catch (Throwable $e) {
            Log::error('Facebook OAuth Callback Failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('error', 'Facebook login was canceled or failed. Please try again.');
        }
    }
}
