<?php

declare(strict_types=1);


namespace Modules\Auth\Http\Requests\Concerns;

use Modules\Common\Http\Requests\Concerns\HasCommonValidationMessages;

trait HasAuthRequestRules
{
  use HasCommonValidationMessages;
  use HasPasswordRules;

  protected function rulesLogin(): array
  {
    return [
      "login" => ["required", "string", "max:255"],
      "password" => ["required", "string", "min:8"],
    ];
  }

  protected function messagesLogin(): array
  {
    return array_merge($this->commonMessages(), [
      "login.required" => "Login wajib diisi (email atau username).",
      "login.string" => "Login harus berupa teks.",
      "login.max" => "Login maksimal 255 karakter.",
      "password.required" => "Password wajib diisi.",
      "password.string" => "Password harus berupa teks.",
      "password.min" => "Password minimal 8 karakter.",
    ]);
  }

  protected function rulesRegister(): array
  {
    return [
      "name" => ["required", "string", "max:255"],
      "username" => [
        "required",
        "string",
        "min:3",
        "max:50",
        'regex:/^[a-z0-9_\.\-]+$/i',
        "unique:users,username",
      ],
      "email" => ["required", "email", "max:255", "unique:users,email"],
      "password" => $this->passwordRulesRegistration(),
    ];
  }

  protected function messagesRegister(): array
  {
    return array_merge($this->commonMessages(), $this->passwordMessages(), [
      "name.required" => "Nama wajib diisi.",
      "name.string" => "Nama harus berupa teks.",
      "name.max" => "Nama maksimal 255 karakter.",
      "username.required" => "Username wajib diisi.",
      "username.string" => "Username harus berupa teks.",
      "username.min" => "Username minimal 3 karakter.",
      "username.max" => "Username maksimal 50 karakter.",
      "username.regex" =>
        "Username hanya boleh mengandung huruf, angka, titik, garis bawah, dan garis sambung. Tidak boleh mengandung spasi.",
      "username.unique" => "Username sudah digunakan.",
      "email.required" => "Email wajib diisi.",
      "email.email" => "Format email tidak valid.",
      "email.max" => "Email maksimal 255 karakter.",
      "email.unique" => "Email sudah digunakan.",
    ]);
  }

  protected function rulesCreateUser(): array
  {
    return [
      "name" => ["required", "string", "max:255"],
      "username" => [
        "required",
        "string",
        "min:3",
        "max:255",
        'regex:/^[a-z0-9_\.\-]+$/i',
        "unique:users,username",
      ],
      "email" => ["required", "email", "max:255", "unique:users,email"],
      "role" => ["required", "string", "in:Student,Instructor,Admin,Superadmin"],
    ];
  }

  protected function messagesCreateManagedUser(): array
  {
    return [
      "name.required" => "Nama wajib diisi.",
      "name.string" => "Nama harus berupa teks.",
      "name.max" => "Nama maksimal 255 karakter.",
      "username.required" => "Username wajib diisi.",
      "username.string" => "Username harus berupa teks.",
      "username.min" => "Username minimal 3 karakter.",
      "username.max" => "Username maksimal 255 karakter.",
      "username.regex" =>
        "Username hanya boleh mengandung huruf, angka, titik, garis bawah, dan garis sambung. Tidak boleh mengandung spasi.",
      "username.unique" => "Username sudah digunakan.",
      "email.required" => "Email wajib diisi.",
      "email.email" => "Format email tidak valid.",
      "email.unique" => "Email sudah digunakan.",
      "role.required" => "Role wajib diisi.",
      "role.in" => "Role tidak valid.",
    ];
  }

  protected function rulesChangePassword(): array
  {
    return [
      "current_password" => ["required", "string"],
      "password" => $this->passwordRulesStrong(),
    ];
  }

  protected function messagesChangePassword(): array
  {
    return array_merge($this->passwordMessages(), [
      "current_password.required" => "Password lama wajib diisi.",
    ]);
  }

  protected function rulesResetPassword(): array
  {
    return [
      "token" => ["required", "string", "min:32"],
      "password" => $this->passwordRulesStrong(),
    ];
  }

  protected function messagesResetPassword(): array
  {
    return array_merge($this->passwordMessages(), [
      "token.required" => "Token reset wajib diisi.",
      "token.string" => "Token reset harus berupa string.",
      "token.min" => "Token reset tidak valid.",
    ]);
  }

  protected function rulesRefresh(): array
  {
    return [
      "refresh_token" => ["nullable", "string"],
    ];
  }

  protected function messagesRefresh(): array
  {
    return [
      "refresh_token.required" => "Refresh token wajib diisi.",
      "refresh_token.string" => "Refresh token harus berupa teks.",
    ];
  }

  protected function rulesLogout(): array
  {
    return [
      "refresh_token" => ["nullable", "string"],
    ];
  }

  protected function messagesLogout(): array
  {
    return [
      "refresh_token.string" => "Refresh token harus berupa teks.",
    ];
  }

  protected function rulesResendCredentials(): array
  {
    return [
      "user_id" => ["required", "integer", "exists:users,id"],
    ];
  }

  protected function messagesResendCredentials(): array
  {
    return [
      "user_id.required" => "User ID wajib diisi.",
      "user_id.integer" => "User ID harus berupa angka.",
      "user_id.exists" => "User ID tidak ditemukan.",
    ];
  }

  protected function rulesForgotPassword(): array
  {
    return [
      "login" => ["required", "string"],
    ];
  }

  protected function messagesForgotPassword(): array
  {
    return [
      "login.required" => "Email atau username wajib diisi.",
      "login.string" => "Email atau username harus berupa teks.",
    ];
  }

  protected function rulesUpdateProfile(): array
  {
    $userId = optional(auth("api")->user())->id ?? null;

    return [
      "name" => ["required", "string", "max:100"],
      "username" => [
        "required",
        "string",
        "min:3",
        "max:50",
        'regex:/^[a-z0-9_\.\-]+$/i',
        \Illuminate\Validation\Rule::unique("users", "username")->ignore($userId),
      ],
      "avatar" => ["nullable", "image", "mimes:jpg,jpeg,png,webp", "max:2048"],
    ];
  }

  protected function messagesUpdateProfile(): array
  {
    return [
      "name.required" => "Nama wajib diisi.",
      "username.required" => "Username wajib diisi.",
      "username.string" => "Username harus berupa teks.",
      "username.min" => "Username minimal 3 karakter.",
      "username.max" => "Username maksimal 50 karakter.",
      "username.regex" =>
        "Username hanya boleh mengandung huruf, angka, titik, garis bawah, dan garis sambung. Tidak boleh mengandung spasi.",
      "username.unique" => "Username sudah digunakan.",
      "avatar.image" => "Avatar harus berupa gambar.",
      "avatar.mimes" => "Avatar harus berformat jpg, jpeg, png, atau webp.",
      "avatar.max" => "Ukuran avatar maksimal 2MB.",
    ];
  }

  protected function rulesRequestEmailChange(): array
  {
    $userId = optional(auth("api")->user())->id ?? null;

    return [
      "new_email" => [
        "required",
        "email:rfc",
        "max:191",
        \Illuminate\Validation\Rule::unique("users", "email")->ignore($userId),
      ],
    ];
  }

  protected function messagesRequestEmailChange(): array
  {
    return [
      "new_email.required" => "Email baru wajib diisi.",
      "new_email.email" => "Format email tidak valid.",
      "new_email.unique" => "Email tersebut sudah digunakan.",
    ];
  }

  protected function rulesVerifyEmailChange(): array
  {
    return [
      "uuid" => ["required", "string", "uuid"],
      "token" => ["required", "string", "size:16"],
    ];
  }

  protected function messagesVerifyEmailChange(): array
  {
    return [
      "uuid.required" => "UUID wajib diisi.",
      "uuid.uuid" => "UUID tidak valid.",
      "token.required" => "Token wajib diisi.",
      "token.size" => "Token harus 16 karakter.",
    ];
  }

  protected function rulesRequestAccountDeletion(): array
  {
    return [
      "password" => ["required", "string"],
    ];
  }

  protected function rulesConfirmAccountDeletion(): array
  {
    return [
      "uuid" => ["required", "string", "uuid"],
      "token" => ["required", "string", "size:16"],
    ];
  }

  protected function rulesVerifyEmail(): array
  {
    return [
      "uuid" => ["required_without:token", "string"],
      "token" => ["required_without:uuid", "string"],
      "code" => ["required", "string"],
    ];
  }

  protected function messagesVerifyEmail(): array
  {
    return [
      "uuid.required_without" => "UUID atau token wajib diisi.",
      "token.required_without" => "Token atau UUID wajib diisi.",
      "code.required" => "Kode wajib diisi.",
    ];
  }
}
