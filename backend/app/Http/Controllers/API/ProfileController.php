<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('roles')
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            // Validasi dasar
            $rules = [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'whatsapp_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];

            // Validasi password hanya jika ada current_password
            if ($request->filled('current_password')) {
                // Cek password saat ini SEBELUM validasi
                if (!Hash::check($request->current_password, $user->password)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Password saat ini tidak sesuai',
                        'errors' => [
                            'current_password' => ['Password saat ini tidak sesuai']
                        ]
                    ], 422);
                }

                $rules['new_password'] = 'required|min:8';
                $rules['new_password_confirmation'] = 'required|same:new_password';
            }

            $request->validate($rules);

            // Update data dasar
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp_number' => $request->whatsapp_number,
                'address' => $request->address,
            ];

            // Update password jika ada
            if ($request->filled('current_password') && $request->filled('new_password')) {
                $updateData['password'] = Hash::make($request->new_password);
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                $updateData['photo'] = $request->file('photo')->store('users', 'public');
            }

            $user->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user->fresh()->load('roles')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
