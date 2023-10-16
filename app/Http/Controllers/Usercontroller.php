<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log; // Import the Log facade for logging

class UserController extends Controller
{
    public function Login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $login = DB::table('users')
            ->where('username', $request->username)
            ->select('users.password', 'users.username')
            ->first();

        if ($login) {
            if (Hash::check($request->password, $login->password)) {
                $passwordGrantClient = Client::where('password_client', 1)->first();
                $response = [
                    'grant_type'    => 'password',
                    'client_id'     => $passwordGrantClient->id,
                    'client_secret' => $passwordGrantClient->secret,
                    'username'      => $request->username,
                    'password'      => $request->password,
                    'scope'         => '*',
                ];

                if (Auth::attempt($credentials)) {
                    $tokenRequest = Request::create('/oauth/token', 'post', $response);

                    try {
                        $response = app()->handle($tokenRequest);
                        $data = json_decode($response->getContent());

                        if ($data && property_exists($data, 'access_token')) {
                            $token = $data->access_token;
                            $responseContent = [
                                'message' => 'success',
                                'token'   => $token,
                            ];
                            return response()->json($responseContent, 200);
                        } else {
                            return response()->json(['message' => 'Unable to obtain access token.'], 500);
                        }
                    } catch (\Exception $e) {
                        Log::error('Token request failed: ' . $e->getMessage());
                        return response()->json(['message' => 'Token request failed.'], 500);
                    }
                }
            } else {
                return response()->json(['message' => 'Incorrect Password.']);
            }
        } else {
            return response()->json(['message' => 'The username is incorrect.']);
        }
    }

    public function Register(Request $request)
    {
        $User = new User();
        $User->username = $request['username'];
        $User->password = bcrypt($request['password']);
        $User->save();

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function Logout(Request $request)
    {
        $user = $request->user();
        $user->token()->revoke();
        return response()->json(['message' => 'User logged out successfully']);
    }
}
