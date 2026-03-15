<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Planeur;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du participant
     */
    public function index()
    {
        $pilote = Auth::guard('pilotes')->user();
        
        if (!$pilote) {
            return redirect()->route('login');
        }

        // Recharger le pilote pour avoir les données à jour (notamment les documents)
        $pilote->refresh();
        
        $planeurs = $pilote->planeurs()->with('piloteProprietaire')->get();

        return view('dashboard', [
            'pilote' => $pilote,
            'planeurs' => $planeurs,
        ]);
    }

    /**
     * Gère l'upload des documents du pilote
     */
    public function uploadDocuments(Request $request)
    {
        $pilote = Auth::guard('pilotes')->user();
        
        if (!$pilote) {
            return redirect()->route('login');
        }

        try {
            $request->validate([
                'autorisation_parentale' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
                'feuille_declarative_qualifications' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
                'visite_medicale_classe_2' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
                'spl_valide' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            ], [
                'autorisation_parentale.file' => 'L\'autorisation parentale doit être un fichier.',
                'autorisation_parentale.mimes' => 'L\'autorisation parentale doit être au format PDF, JPG, JPEG ou PNG.',
                'autorisation_parentale.max' => 'L\'autorisation parentale ne doit pas dépasser 100 Mo.',
                'feuille_declarative_qualifications.file' => 'La feuille déclarative doit être un fichier.',
                'feuille_declarative_qualifications.mimes' => 'La feuille déclarative doit être au format PDF, JPG, JPEG ou PNG.',
                'feuille_declarative_qualifications.max' => 'La feuille déclarative ne doit pas dépasser 100 Mo.',
                'visite_medicale_classe_2.file' => 'La visite médicale doit être un fichier.',
                'visite_medicale_classe_2.mimes' => 'La visite médicale doit être au format PDF, JPG, JPEG ou PNG.',
                'visite_medicale_classe_2.max' => 'La visite médicale ne doit pas dépasser 100 Mo.',
                'spl_valide.file' => 'Le document SPL doit être un fichier.',
                'spl_valide.mimes' => 'Le document SPL doit être au format PDF, JPG, JPEG ou PNG.',
                'spl_valide.max' => 'Le document SPL ne doit pas dépasser 100 Mo.',
            ]);

            // Vérifier qu'au moins un fichier est fourni
            $hasFile = $request->hasFile('autorisation_parentale') 
                    || $request->hasFile('feuille_declarative_qualifications')
                    || $request->hasFile('visite_medicale_classe_2')
                    || $request->hasFile('spl_valide');

            if (!$hasFile) {
                return redirect()->route('dashboard')
                    ->with('error', 'Veuillez sélectionner au moins un document à enregistrer.');
            }

            // Fonction helper pour gérer l'upload de fichiers
            $uploadFile = function($file, $directory) {
                if ($file && $file->isValid()) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs($directory, $filename, 'public');
                    return $path;
                }
                return null;
            };

            $documentsModifies = false;

            // Supprimer les anciens fichiers si de nouveaux sont uploadés
            if ($request->hasFile('autorisation_parentale')) {
                if ($pilote->autorisation_parentale && Storage::disk('public')->exists($pilote->autorisation_parentale)) {
                    Storage::disk('public')->delete($pilote->autorisation_parentale);
                }
                $newPath = $uploadFile($request->file('autorisation_parentale'), 'autorisations');
                if ($newPath) {
                    $pilote->autorisation_parentale = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($request->hasFile('feuille_declarative_qualifications')) {
                if ($pilote->feuille_declarative_qualifications && Storage::disk('public')->exists($pilote->feuille_declarative_qualifications)) {
                    Storage::disk('public')->delete($pilote->feuille_declarative_qualifications);
                }
                $newPath = $uploadFile($request->file('feuille_declarative_qualifications'), 'documents');
                if ($newPath) {
                    $pilote->feuille_declarative_qualifications = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($request->hasFile('visite_medicale_classe_2')) {
                if ($pilote->visite_medicale_classe_2 && Storage::disk('public')->exists($pilote->visite_medicale_classe_2)) {
                    Storage::disk('public')->delete($pilote->visite_medicale_classe_2);
                }
                $newPath = $uploadFile($request->file('visite_medicale_classe_2'), 'documents');
                if ($newPath) {
                    $pilote->visite_medicale_classe_2 = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($request->hasFile('spl_valide')) {
                if ($pilote->spl_valide && Storage::disk('public')->exists($pilote->spl_valide)) {
                    Storage::disk('public')->delete($pilote->spl_valide);
                }
                $newPath = $uploadFile($request->file('spl_valide'), 'documents');
                if ($newPath) {
                    $pilote->spl_valide = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($documentsModifies) {
                $pilote->save();
                // Recharger le pilote pour avoir les données à jour
                $pilote->refresh();
                
                return redirect()->route('dashboard')
                    ->with('success', 'Les documents ont été mis à jour avec succès.');
            } else {
                return redirect()->route('dashboard')
                    ->with('error', 'Aucun document n\'a pu être enregistré. Veuillez vérifier les fichiers sélectionnés.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('dashboard')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement des documents : ' . $e->getMessage());
        }
    }

    /**
     * Gère l'upload des documents du planeur
     */
    public function uploadDocumentsPlaneur(Request $request, $planeurId)
    {
        $pilote = Auth::guard('pilotes')->user();
        
        if (!$pilote) {
            return redirect()->route('login');
        }

        // Vérifier que le planeur appartient bien au pilote connecté
        $planeur = Planeur::whereHas('pilotes', function($query) use ($pilote) {
            $query->where('pilotes.id', $pilote->id);
        })->findOrFail($planeurId);

        try {
            $request->validate([
                'cdn_cen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
                'responsabilite_civile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            ], [
                'cdn_cen.file' => 'Le CDN/CEN doit être un fichier.',
                'cdn_cen.mimes' => 'Le CDN/CEN doit être au format PDF, JPG, JPEG ou PNG.',
                'cdn_cen.max' => 'Le CDN/CEN ne doit pas dépasser 100 Mo.',
                'responsabilite_civile.file' => 'La responsabilité civile doit être un fichier.',
                'responsabilite_civile.mimes' => 'La responsabilité civile doit être au format PDF, JPG, JPEG ou PNG.',
                'responsabilite_civile.max' => 'La responsabilité civile ne doit pas dépasser 100 Mo.',
            ]);

            // Vérifier qu'au moins un fichier est fourni
            $hasFile = $request->hasFile('cdn_cen') || $request->hasFile('responsabilite_civile');

            if (!$hasFile) {
                return redirect()->route('dashboard')
                    ->with('error', 'Veuillez sélectionner au moins un document à enregistrer.');
            }

            // Fonction helper pour gérer l'upload de fichiers
            $uploadFile = function($file, $directory) {
                if ($file && $file->isValid()) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs($directory, $filename, 'public');
                    return $path;
                }
                return null;
            };

            $documentsModifies = false;

            // Supprimer les anciens fichiers si de nouveaux sont uploadés
            if ($request->hasFile('cdn_cen')) {
                if ($planeur->cdn_cen && Storage::disk('public')->exists($planeur->cdn_cen)) {
                    Storage::disk('public')->delete($planeur->cdn_cen);
                }
                $newPath = $uploadFile($request->file('cdn_cen'), 'documents');
                if ($newPath) {
                    $planeur->cdn_cen = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($request->hasFile('responsabilite_civile')) {
                if ($planeur->responsabilite_civile && Storage::disk('public')->exists($planeur->responsabilite_civile)) {
                    Storage::disk('public')->delete($planeur->responsabilite_civile);
                }
                $newPath = $uploadFile($request->file('responsabilite_civile'), 'documents');
                if ($newPath) {
                    $planeur->responsabilite_civile = $newPath;
                    $documentsModifies = true;
                }
            }

            if ($documentsModifies) {
                $planeur->save();
                
                return redirect()->route('dashboard')
                    ->with('success', 'Les documents du planeur ont été mis à jour avec succès.');
            } else {
                return redirect()->route('dashboard')
                    ->with('error', 'Aucun document n\'a pu être enregistré. Veuillez vérifier les fichiers sélectionnés.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('dashboard')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement des documents : ' . $e->getMessage());
        }
    }
}
