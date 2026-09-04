<?php

namespace App\Http\Controllers;

use App\Models\Shirt;
use Illuminate\Http\Request;

class ShirtController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'creator_name' => 'nullable|string|max:60',

            'front_text' => 'nullable|string|max:120',
            'front_text_color' => 'nullable|string|max:20',
            'front_image' => 'nullable|image|max:2048', // 2MB
            'front_x' => 'nullable|numeric|min:0|max:100',
            'front_y' => 'nullable|numeric|min:0|max:100',
            'front_width' => 'nullable|numeric|min:0|max:100',
            'front_height' => 'nullable|numeric|min:0|max:100',

            'back_text' => 'nullable|string|max:120',
            'back_text_color' => 'nullable|string|max:20',
            'back_image' => 'nullable|image|max:2048',
            'back_x' => 'nullable|numeric|min:0|max:100',
            'back_y' => 'nullable|numeric|min:0|max:100',
            'back_width' => 'nullable|numeric|min:0|max:100',
            'back_height' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('front_image')) {
            $validated['front_image_path'] = $request->file('front_image')->store('shirt-designs', 'public');
        }

        if ($request->hasFile('back_image')) {
            $validated['back_image_path'] = $request->file('back_image')->store('shirt-designs', 'public');
        }

        $shirt = Shirt::create($validated);

        return redirect()->route('shirts.show', $shirt);
    }
}