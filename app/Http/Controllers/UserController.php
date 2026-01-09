<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Lista todos os utilizadores (apenas admin).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $users = User::all();
        return response()->json($users);
    }

    /**
     * Mostra detalhes de um utilizador específico (apenas admin).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $user = User::with('reservas')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Cria um novo utilizador (apenas admin).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|in:admin,user',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if ($request->has('role')) {
            $userData['role'] = $validated['role'];
        }

        $user = User::create($userData);

        return response()->json([
            'message' => 'Utilizador criado com sucesso!',
            'user' => $user
        ], 201);
    }

    /**
     * Atualiza um utilizador existente (apenas admin).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,user',
        ]);

        if ($request->has('name')) {
            $user->name = $validated['name'];
        }

        if ($request->has('email')) {
            $user->email = $validated['email'];
        }

        if ($request->has('password')) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->has('role')) {
            $user->role = $validated['role'];
        }

        $user->save();

        return response()->json([
            'message' => 'Utilizador atualizado com sucesso!',
            'user' => $user
        ]);
    }

    /**
     * Elimina qualquer utilizador do sistema (apenas admin).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDestroy($id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $user = User::findOrFail($id);

        // Não permitir que um admin elimine a si próprio
        if ($user->id === $admin->id) {
            return response()->json(['error' => 'Não é possível eliminar a sua própria conta através desta rota.'], 400);
        }

        // Eliminar todas as reservas do utilizador
        Reserva::where('user_id', $user->id)->delete();

        // Eliminar o utilizador
        $user->delete();

        return response()->json(['message' => 'Utilizador eliminado com sucesso.']);
    }

    /**
     * Elimina a própria conta do utilizador autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroySelf()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Não autenticado.'], 401);
            }

            // Não permitir que um admin elimine a si próprio
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Administradores não podem eliminar suas próprias contas por motivos de segurança.'], 400);
            }

            // Obter o ID do usuário antes de excluí-lo
            $userId = $user->id;
            
            // Eliminar todas as reservas do utilizador
            Reserva::where('user_id', $userId)->delete();

            // Invalidar o token JWT atual
            JWTAuth::invalidate(JWTAuth::getToken());
            
            // Eliminar o utilizador usando o método estático destroy
            $result = User::destroy($userId);
            
            if ($result) {
                return response()->json(['message' => 'Conta eliminada com sucesso!']);
            } else {
                return response()->json(['error' => 'Não foi possível eliminar a conta.'], 500);
            }
        } catch (\Exception $e) {
            // Log do erro para depuração
            Log::error('Erro ao eliminar conta: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro ao eliminar conta. Por favor, tente novamente mais tarde.'
            ], 500);
        }
    }
}




