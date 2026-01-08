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
      "login.required" => __("validation.required", ["attribute" => "login"]),
      "login.string" => __("validation.string", ["attribute" => "login"]),
      "login.max" => __("validation.max.string", ["attribute" => "login"]),
      "password.required" => __("validation.required", ["attribute" => "password"]),
      "password.string" => __("validation.string", ["attribute" => "password"]),
      "password.min" => __("validation.min.string", ["attribute" => "password"]),
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
      "name.required" => __("validation.required", ["attribute" => "name"]),
      "name.string" => __("validation.string", ["attribute" => "name"]),
      "name.max" => __("validation.max.string", ["attribute" => "name"]),
      "username.required" => __("validation.required", ["attribute" => "username"]),
      "username.string" => __("validation.string", ["attribute" => "username"]),
      "username.min" => __("validation.min.string", ["attribute" => "username"]),
      "username.max" => __("validation.max.string", ["attribute" => "username"]),
      "username.regex" => __("validation.regex", ["attribute" => "username"]),
      "username.unique" => __("validation.unique", ["attribute" => "username"]),
      "email.required" => __("validation.required", ["attribute" => "email"]),
      "email.email" => __("validation.email", ["attribute" => "email"]),
      "email.max" => __("validation.max.string", ["attribute" => "email"]),
      "email.unique" => __("validation.unique", ["attribute" => "email"]),
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

  protected function messagesCreateUser(): array
  {
    return [
      "name.required" => __("validation.required", ["attribute" => "name"]),
      "name.string" => __("validation.string", ["attribute" => "name"]),
      "name.max" => __("validation.max.string", ["attribute" => "name"]),
      "username.required" => __("validation.required", ["attribute" => "username"]),
      "username.string" => __("validation.string", ["attribute" => "username"]),
      "username.min" => __("validation.min.string", ["attribute" => "username"]),
      "username.max" => __("validation.max.string", ["attribute" => "username"]),
      "username.regex" => __("validation.regex", ["attribute" => "username"]),
      "username.unique" => __("validation.unique", ["attribute" => "username"]),
      "email.required" => __("validation.required", ["attribute" => "email"]),
      "email.email" => __("validation.email", ["attribute" => "email"]),
      "email.unique" => __("validation.unique", ["attribute" => "email"]),
      "role.required" => __("validation.required", ["attribute" => "role"]),
      "role.in" => __("validation.in", ["attribute" => "role"]),
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
      "current_password.required" => __("validation.required", ["attribute" => "current password"]),
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
      "token.required" => __("validation.required", ["attribute" => "token"]),
      "token.string" => __("validation.string", ["attribute" => "token"]),
      "token.min" => __("validation.min.string", ["attribute" => "token"]),
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
      "refresh_token.required" => __("validation.required", ["attribute" => "refresh token"]),
      "refresh_token.string" => __("validation.string", ["attribute" => "refresh token"]),
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
      "refresh_token.string" => __("validation.string", ["attribute" => "refresh token"]),
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
      "user_id.required" => __("validation.required", ["attribute" => "user id"]),
      "user_id.integer" => __("validation.integer", ["attribute" => "user id"]),
      "user_id.exists" => __("validation.exists", ["attribute" => "user id"]),
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
      "login.required" => __("validation.required", ["attribute" => "login"]),
      "login.string" => __("validation.string", ["attribute" => "login"]),
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
      "name.required" => __("validation.required", ["attribute" => "name"]),
      "username.required" => __("validation.required", ["attribute" => "username"]),
      "username.string" => __("validation.string", ["attribute" => "username"]),
      "username.min" => __("validation.min.string", ["attribute" => "username"]),
      "username.max" => __("validation.max.string", ["attribute" => "username"]),
      "username.regex" => __("validation.regex", ["attribute" => "username"]),
      "username.unique" => __("validation.unique", ["attribute" => "username"]),
      "avatar.image" => __("validation.image", ["attribute" => "avatar"]),
      "avatar.mimes" => __("validation.mimes", ["attribute" => "avatar"]),
      "avatar.max" => __("validation.max.file", ["attribute" => "avatar"]),
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
      "new_email.required" => __("validation.required", ["attribute" => "email"]),
      "new_email.email" => __("validation.email", ["attribute" => "email"]),
      "new_email.unique" => __("validation.unique", ["attribute" => "email"]),
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
      "uuid.required" => __("validation.required", ["attribute" => "uuid"]),
      "uuid.uuid" => __("validation.uuid", ["attribute" => "uuid"]),
      "token.required" => __("validation.required", ["attribute" => "token"]),
      "token.size" => __("validation.size.string", ["attribute" => "token"]),
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
      "uuid.required_without" => __("validation.required_without", ["attribute" => "uuid", "values" => "token"]),
      "token.required_without" => __("validation.required_without", ["attribute" => "token", "values" => "uuid"]),
      "code.required" => __("validation.required", ["attribute" => "code"]),
    ];
  }
}
