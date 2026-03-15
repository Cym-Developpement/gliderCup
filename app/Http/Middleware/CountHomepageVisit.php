<?php

namespace App\Http\Middleware;

use App\Models\Competition;
use App\Models\Counter;
use App\Models\VisiteurUnique;
use Closure;
use Illuminate\Http\Request;

class CountHomepageVisit
{
    public function handle(Request $request, Closure $next)
    {
        // Compter uniquement les visites sur la page d'accueil (GET /)
        if ($request->isMethod('get') && $request->routeIs('inscription.index')) {
            // Visiteur "unique" = une session PHP
            // On incrémente une seule fois par session (et par compétition active).
            $competition = Competition::active();
            $competitionSuffix = $competition ? (string) $competition->id : 'unknown';

            $sessionKey = 'stats.home_visit_counted:competition:' . $competitionSuffix;
            $sessionKeyToday = 'stats.home_visit_counted_today:competition:' . $competitionSuffix . ':date:' . date('Y-m-d');

            if (!$request->session()->has($sessionKey)) {
                Counter::incrementKey('home_visits:competition:' . $competitionSuffix);
                $request->session()->put($sessionKey, true);
            }

            // Compter aussi les visites du jour (peut être plusieurs fois par jour si la session expire)
            if (!$request->session()->has($sessionKeyToday)) {
                Counter::incrementKey('home_visits:competition:' . $competitionSuffix . ':date:' . date('Y-m-d'));
                $request->session()->put($sessionKeyToday, true);
            }

            // Enregistrer le visiteur unique avec IP et user agent
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $dateVisite = now()->toDateString();

            // Vérifier si ce visiteur (même IP + user agent + compétition + date) n'existe pas déjà
            $visiteurExistant = VisiteurUnique::where('competition_id', $competition?->id)
                ->where('ip_address', $ipAddress)
                ->where('user_agent', $userAgent)
                ->where('date_visite', $dateVisite)
                ->exists();

            if (!$visiteurExistant) {
                VisiteurUnique::create([
                    'competition_id' => $competition?->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'date_visite' => $dateVisite,
                ]);
            }
        }

        return $next($request);
    }
}

