<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    /**
     * Lista todas as reservas.
     * Admin vê todas, utilizador comum vê apenas as suas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Utilizador não autenticado.'], 401);
            }
            
            if ($user->role === 'admin') {
                // Admin vê todas as reservas com detalhes do utilizador e da viagem
                $reservas = Reserva::with(['user', 'viagem'])->get();
            } else {
                // Utilizador comum vê só as suas reservas com detalhes da viagem
                $reservas = Reserva::with('viagem')
                    ->where('user_id', $user->id)
                    ->get();
            }

            return response()->json([
                'reservas' => $reservas
            ]);
        } catch (\Exception $e) {
            // Log do erro para depuração
            Log::error('Erro ao listar reservas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erro ao listar reservas. Por favor, tente novamente mais tarde.'
            ], 500);
        }
    }

    /**
     * Mostra detalhes de uma reserva específica.
     * Admin vê qualquer reserva, utilizador comum vê apenas as suas.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $reserva = Reserva::with(['user', 'viagem'])->findOrFail($id);
        
        // Verificar se o utilizador tem permissão para ver esta reserva
        if ($user->role !== 'admin' && $reserva->user_id !== $user->id) {
            return response()->json(['error' => 'Acesso negado. Esta reserva não pertence ao utilizador.'], 403);
        }
        
        return response()->json($reserva);
    }

    /**
     * Cria uma nova reserva para o utilizador autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'viagem_id' => 'required|exists:viagens,id',
            'lugares' => 'required|integer|min:1'
        ]);
        
        // Verificar se a viagem existe
        $viagem = Viagem::findOrFail($validated['viagem_id']);
        
        // Criar a reserva
        $reserva = Reserva::create([
            'user_id' => $user->id,
            'viagem_id' => $validated['viagem_id'],
            'lugares' => $validated['lugares']
        ]);
        
        return response()->json([
            'message' => 'Reserva criada com sucesso!',
            'reserva' => $reserva
        ], 201);
    }

    /**
     * Atualiza uma reserva existente.
     * Admin pode atualizar qualquer reserva, utilizador comum apenas as suas.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $reserva = Reserva::findOrFail($id);
        
        // Verificar se o utilizador tem permissão para atualizar esta reserva
        if ($user->role !== 'admin' && $reserva->user_id !== $user->id) {
            return response()->json(['error' => 'Acesso negado. Esta reserva não pertence ao utilizador.'], 403);
        }
        
        $validated = $request->validate([
            'viagem_id' => 'sometimes|exists:viagens,id',
            'lugares' => 'sometimes|integer|min:1',
            'user_id' => 'sometimes|exists:users,id' // Permitir que o admin altere o utilizador da reserva
        ]);
        
        // Atualizar explicitamente cada campo
        if (isset($validated['viagem_id'])) {
            $reserva->viagem_id = $validated['viagem_id'];
        }
        
        if (isset($validated['lugares'])) {
            $reserva->lugares = $validated['lugares'];
        }
        
        if (isset($validated['user_id']) && $user->role === 'admin') {
            $reserva->user_id = $validated['user_id'];
        }
        
        // Salvar as alterações
        $reserva->save();
        
        // Recarregar a reserva da base de dados para garantir que temos os dados atualizados
        $reserva->refresh();
        
        return response()->json([
            'message' => 'Reserva atualizada com sucesso!',
            'reserva' => $reserva
        ]);
    }

    /**
     * Elimina uma reserva.
     * Admin pode eliminar qualquer reserva, utilizador comum apenas as suas.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $reserva = Reserva::findOrFail($id);
        
        // Verificar se o utilizador tem permissão para eliminar esta reserva
        if ($user->role !== 'admin' && $reserva->user_id !== $user->id) {
            return response()->json(['error' => 'Acesso negado. Esta reserva não pertence ao utilizador.'], 403);
        }
        
        $reserva->delete();
        
        return response()->json(['message' => 'Reserva eliminada com sucesso.']);
    }

    /**
     * Elimina qualquer reserva (apenas admin).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDestroy($id)
    {
        $user = Auth::user();
        
        // Verificar se o utilizador é admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem eliminar qualquer reserva.'], 403);
        }
        
        $reserva = Reserva::findOrFail($id);
        $reserva->delete();
        
        return response()->json(['message' => 'Reserva eliminada com sucesso pelo administrador.']);
    }

    /**
     * Atualiza qualquer reserva (apenas admin).
     * Método específico para administradores com mais controle.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminUpdate(Request $request, $id)
    {
        $user = Auth::user();
        
        // Verificar se o utilizador é admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem atualizar qualquer reserva.'], 403);
        }
        
        $reserva = Reserva::findOrFail($id);
        
        $validated = $request->validate([
            'viagem_id' => 'sometimes|exists:viagens,id',
            'lugares' => 'sometimes|integer|min:1',
            'user_id' => 'sometimes|exists:users,id'
        ]);
        
        // Atualizar explicitamente cada campo
        if (isset($validated['viagem_id'])) {
            $reserva->viagem_id = $validated['viagem_id'];
        }
        
        if (isset($validated['lugares'])) {
            $reserva->lugares = $validated['lugares'];
        }
        
        if (isset($validated['user_id'])) {
            $reserva->user_id = $validated['user_id'];
        }
        
        // Salvar as alterações
        $reserva->save();
        
        // Recarregar a reserva para garantir dados atualizados
        $reserva->refresh();
        
        return response()->json([
            'message' => 'Reserva atualizada com sucesso pelo administrador!',
            'reserva' => $reserva
        ]);
    }

    /**
     * Lista todas as reservas (apenas admin).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminIndex()
    {
        $user = Auth::user();
        
        // Verificar se o utilizador é admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem listar todas as reservas.'], 403);
        }
        
        // Admin vê todas as reservas com detalhes do utilizador e da viagem
        $reservas = Reserva::with(['user', 'viagem'])->get();
        
        return response()->json($reservas);
    }

    /**
     * Mostra detalhes de qualquer reserva (apenas admin).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id)
    {
        $user = Auth::user();
        
        // Verificar se o utilizador é admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem ver detalhes de qualquer reserva.'], 403);
        }
        
        $reserva = Reserva::with(['user', 'viagem'])->findOrFail($id);
        
        return response()->json($reserva);
    }

    /**
     * Cria uma reserva para qualquer utilizador (apenas admin).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminStore(Request $request)
    {
        $user = Auth::user();
        
        // Verificar se o utilizador é admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem criar reservas para outros utilizadores.'], 403);
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'viagem_id' => 'required|exists:viagens,id',
            'lugares' => 'required|integer|min:1'
        ]);
        
        // Verificar se a viagem existe
        $viagem = Viagem::findOrFail($validated['viagem_id']);
        
        // Criar a reserva
        $reserva = Reserva::create([
            'user_id' => $validated['user_id'],
            'viagem_id' => $validated['viagem_id'],
            'lugares' => $validated['lugares']
        ]);
        
        return response()->json([
            'message' => 'Reserva criada com sucesso pelo administrador!',
            'reserva' => $reserva
        ], 201);
    }
}




