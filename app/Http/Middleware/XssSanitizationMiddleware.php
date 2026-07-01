<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Mews\Purifier\Facades\Purifier;

class XssSanitizationMiddleware
{
    /**
     * Liste des champs à NE PAS nettoyer (ex: les mots de passe)
     */
    protected array $except = [
        'password',
        'password_confirmation',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            // On évite de toucher aux mots de passe ou aux champs exclus
            if (in_array($key, $this->except, true)) {
                return;
            }

            if (is_string($value)) {
                // HTMLPurifier nettoie le XSS sans casser le texte brut ni le HTML safe
                $value = Purifier::clean($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
