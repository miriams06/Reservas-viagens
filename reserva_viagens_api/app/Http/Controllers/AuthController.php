<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Factory;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            // Gerar token manualmente sem usar o Carbon internamente
            $credentials = ['email' => $request->email, 'password' => $request->password];
            if (!$token = auth('api')->attempt($credentials)) {
                throw new \Exception('Erro ao gerar token');
            }

            return response()->json([
                'message' => 'Utilizador registado com sucesso!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600, // 1 hora em segundos (fixo para evitar problemas)
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erro de validação',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro no registro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao registrar utilizador: ' . $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $user = auth('api')->user();
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Usando config() em vez de factory()
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    public function perfil()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json($user);
    }

    public function logout()
    {
        try {
            // Invalidar o token atual
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json(['message' => 'Logout com sucesso']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Falha ao fazer logout'], 500);
        }
    }

    /**
     * Retorna o token JWT com informações adicionais.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Tempo em segundos
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }
}
















