<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Upload and save user's .p12 digital certificate.
     */
    public function uploadCertificate(Request $request)
    {
        $request->validate([
            'certificado' => [
                'required',
                'file',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['p12', 'pfx'])) {
                        $fail('El archivo debe ser un certificado digital válido (.p12 o .pfx).');
                    }
                },
            ],
        ]);

        $user = $request->user();

        // Eliminar el anterior si existía
        if ($user->certificado_path && \Illuminate\Support\Facades\Storage::exists($user->certificado_path)) {
            \Illuminate\Support\Facades\Storage::delete($user->certificado_path);
        }

        $path = $request->file('certificado')->storeAs(
            'certificados',
            'cert_' . $user->id . '_' . time() . '.p12',
            'local' // Guardar en local (storage/app/certificados), no accesible públicamente
        );

        $user->certificado_path = $path;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'certificate-uploaded');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
