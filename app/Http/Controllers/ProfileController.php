<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu để xác nhận.',
            'password.current_password' => 'Mật khẩu xác nhận không đúng.',
        ]);

        $user = $request->user();

        if (DB::table('datasets')->where('user_id', $user->getKey())->exists()) {
            throw ValidationException::withMessages([
                'password' => 'Tài khoản đang sở hữu bộ dữ liệu nên chưa thể xóa. Vui lòng liên hệ quản trị viên để xử lý dữ liệu trước.',
            ])->errorBag('userDeletion');
        }

        try {
            $user->delete();
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'password' => 'Tài khoản còn dữ liệu liên quan nên chưa thể xóa. Vui lòng liên hệ quản trị viên.',
            ])->errorBag('userDeletion');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
