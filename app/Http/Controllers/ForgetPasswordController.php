<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;

class ForgetPasswordController extends Controller
{
    public function forgetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:Users'
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email' => 'Email không hợp lệ',
            'email.exists' => 'Email không tồn tại',
        ]);

        $token  = Str::random(64);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => "Vui lòng kiểm tra email"
            ]);
        }
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => "Mật khẩu không được để trống",
            'password.confirmed' => "Xác nhận mật khẩu không khớp.",
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'status' => "SUCCESS"
            ]);
        } else {
        }
        return response()->json([
            'error' => 'Mã thông báo đặt lại mật khẩu này đã hết hạn.'
        ], 400);
    }


    public function forgetPasswordAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:admin,email'
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email' => 'Email không hợp lệ',
            'email.exists' => 'Email không tồn tại',
        ]);

        $token  = Str::random(64);
        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => "Vui lòng kiểm tra email"
            ]);
        }
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function resetPasswordAdmin(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => "Mật khẩu không được để trống",
            'password.confirmed' => "Xác nhận mật khẩu không khớp.",
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin) use ($request) {
                $admin->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($admin));
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'status' => "SUCCESS"
            ]);
        } else {
        }
        return response()->json([
            'error' => 'Mã thông báo đặt lại mật khẩu này đã hết hạn.'
        ], 400);
    }
}
