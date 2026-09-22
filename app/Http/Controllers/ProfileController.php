<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile()->firstOrCreate([]);

        $this->authorize('view', $profile);

        return view('career-profile.edit', compact('profile'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);

        $this->authorize('update', $profile);

        $data = $request->validated();

        $removePhoto = $request->boolean('remove_photo');

        if ($request->hasFile('photo')) {
            if ($profile->photo_path && Storage::disk('private')->exists($profile->photo_path)) {
                Storage::disk('private')->delete($profile->photo_path);
            }

            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            if (! $extension) {
                $extension = $file->guessExtension() ?? 'jpg';
            }
            $filename = Str::uuid()->toString() . '.' . strtolower($extension);
            $path = 'profile-photos/' . $filename;

            Storage::disk('private')->put($path, file_get_contents($file->getRealPath()));

            $data['photo_path'] = $path;
        } elseif ($removePhoto) {
            if ($profile->photo_path && Storage::disk('private')->exists($profile->photo_path)) {
                Storage::disk('private')->delete($profile->photo_path);
            }
            $data['photo_path'] = null;
        }

        unset($data['photo'], $data['remove_photo']);

        $profile->fill($data);
        $profile->save();

        return redirect()->route('career-profile.edit')->with('status', 'profile-updated');
    }

    public function photo(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile()->first();

        if (! $profile || ! $profile->photo_path) {
            abort(404);
        }

        $this->authorize('view', $profile);

        $disk = Storage::disk('private');

        if (! $disk->exists($profile->photo_path)) {
            abort(404);
        }

        $mime = $disk->mimeType($profile->photo_path) ?? 'image/jpeg';

        return response()->stream(function () use ($disk, $profile) {
            echo $disk->get($profile->photo_path);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function destroyPhoto(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile()->first();

        if (! $profile) {
            abort(404);
        }

        $this->authorize('update', $profile);

        if ($profile->photo_path && Storage::disk('private')->exists($profile->photo_path)) {
            Storage::disk('private')->delete($profile->photo_path);
        }

        $profile->update(['photo_path' => null]);

        return redirect()->route('career-profile.edit')->with('status', 'photo-removed');
    }
}
