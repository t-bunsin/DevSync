<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/*
| Applies the language chosen through the language.switch route.
|
| This has to be middleware, not a service provider. AppServiceProvider::boot()
| used to do the same read, but providers boot before the request pipeline runs,
| so StartSession had not put the session together yet and session('locale')
| always came back empty — the switcher wrote a choice that nothing ever read,
| and both shells stayed in English no matter what was picked. Registered in the
| 'web' group after StartSession, the value is there to be read.
*/
class SetLocale
{
    /**
     * Locales with a directory under lang/. Anything else falls back to
     * config('app.locale'), so an unknown value cannot leave the app looking
     * for translation files that do not exist.
     */
    public const SUPPORTED = ['en', 'kh'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
