<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\CallbackRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function callback(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:50',
            'message' => 'nullable|string|max:2000',
        ]);

        $adminEmail = config('mail.admin_email', 'admin@talenteed.io');

        Mail::to($adminEmail)->send(new CallbackRequestMail(
            $validated['name'],
            $validated['email'],
            $validated['phone'],
            $validated['message'] ?? ''
        ));

        return response()->json(['message' => 'Votre demande a bien été envoyée. Nous vous contacterons très prochainement.']);
    }
}
