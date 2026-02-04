use Illuminate\Support\Str;

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Email / password salah'], 401);
    }

    // bikin token
    $token = Str::random(60);

    $user->api_token = hash('sha256', $token);
    $user->save();

    return response()->json([
        'status' => 'login sukses',
        'token' => $token,
        'user' => $user
    ]);
}
