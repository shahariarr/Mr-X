<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Profile;
use App\Models\ContactData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        try {
            $profile = Profile::where('user_id', auth()->id())->first();
            $contactData = ContactData::where('user_id', auth()->id())->first();

            if (!$profile) {
                return redirect()->route('profile.create');
            }

            return view('UserProfile.profile.edit', compact('profile', 'contactData'));
        } catch (Exception $e) {
            return redirect()->route('profile.create')->with('error', 'An error occurred while retrieving your profile.');
        }
    }

    public function create()
    {
        try {
            $profile = Profile::where('user_id', auth()->id())->first();

            if ($profile !== null) {
                return redirect()->route('profile.edit')->with('error', 'Profile already exists. You can only edit it.');
            }

            return view('UserProfile.profile.create');
        } catch (Exception $e) {
            return redirect()->route('profile.edit')->with('error', 'An error occurred while creating your profile.');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'age' => 'required|integer',
                'nationality' => 'required|string|max:255',
                'freelance' => 'required|boolean',
                'languages' => 'required|string|max:255',
                'role' => 'required|string|max:255', // Added validation for role
                'birth' => 'required|date', // Added validation for birth
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            $user = Auth::user();

            $profile = Profile::where('user_id', auth()->id())->first();

            if ($profile !== null) {
                return redirect()->route('profile.edit')->with('error', 'Profile already exists. You can only edit it.');
            }

            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->first_name = $request->first_name;
            $profile->last_name = $request->last_name;
            $profile->age = $request->age;
            $profile->nationality = $request->nationality;
            $profile->freelance = $request->freelance;
            $profile->languages = $request->languages;
            $profile->role = $request->role; // Added role field
            $profile->birth = $request->birth; // Added birth field

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('profile_images'), $imageName);
                $profile->image = $imageName;
            }

            $profile->save();

            return redirect()->route('profile.edit')->with('success', 'Profile created successfully.');
        } catch (Exception $e) {
            return redirect()->route('profile.create')->with('error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'age' => 'required|integer',
                'nationality' => 'required|string|max:255',
                'freelance' => 'required|boolean',
                'languages' => 'required|string|max:255',
                'role' => 'required|string|max:255', // Added validation for role
                'birth' => 'required|date', // Added validation for birth
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            $profile = Profile::where('user_id', auth()->id())->first();

            if (!$profile) {
                return redirect()->route('profile.create')->with('error', 'Profile does not exist. Please create one first.');
            }

            $profile->first_name = $request->first_name;
            $profile->last_name = $request->last_name;
            $profile->age = $request->age;
            $profile->nationality = $request->nationality;
            $profile->freelance = $request->freelance;
            $profile->languages = $request->languages;
            $profile->role = $request->role; // Added role field
            $profile->birth = $request->birth; // Added birth field

            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($profile->image && file_exists(public_path('profile_images/' . $profile->image))) {
                    unlink(public_path('profile_images/' . $profile->image));
                }

                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('profile_images'), $imageName);
                $profile->image = $imageName;
            }

            $profile->save();

            return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            return redirect()->route('profile.edit')->with('error', $e->getMessage());
        }
    }
}
