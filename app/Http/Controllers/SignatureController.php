<?php

namespace App\Http\Controllers;

use App\Models\Shirt;
use Illuminate\Http\Request;

class SignatureController extends Controller
{

    public function store(Request $request, Shirt $shirt)
    {
        $validated = $request->validate([
            'side' => 'required|in:front,back',
            'mode' => 'required|in:typed,drawn',
            'x' => 'required|numeric|min:0|max:100',
            'y' => 'required|numeric|min:0|max:100',
            'rotation' => 'nullable|numeric|min:-45|max:45',
            'color' => 'required|string|max:20',
            'typed_text' => 'required_if:mode,typed|nullable|string|max:40',
            'drawn_path' => 'required_if:mode,drawn|nullable|string|max:20000',
            'signer_name' => 'nullable|string|max:60',
        ]);

        if ($shirt->signatures()->count() >= 500) {
            return response()->json(['message' => 'This shirt is full.'], 422);
        }

        $signature = $shirt->signatures()->create($validated);

        return response()->json($signature, 201);
    }
}