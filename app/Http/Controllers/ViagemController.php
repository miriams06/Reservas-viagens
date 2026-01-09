<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViagemController extends Controller
{
    /**
     * Lista todas as viagens disponíveis.
     * Acessível por todos os utilizadores.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $viagens = Viagem::all();
        return response()->json($viagens);
    }

    /**
     * Mostra detalhes de uma viagem específica.
     * Acessível por todos os utilizadores.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $viagem = Viagem::with('reservas')->findOrFail($id);
        return response()->json($viagem);
    }

    /**
     * Cria uma nova viagem.
     * Apenas administradores podem executar esta ação.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem criar viagens.'], 403);
        }

        $validated = $request->validate([
            'destino' => 'required|string|max:255',
            'data_partida' => 'required|date',
            'data_regresso' => 'required|date|after_or_equal:data_partida',
            'preco' => 'required|numeric|min:0'
        ]);

        $viagem = Viagem::create($validated);

        return response()->json([
            'message' => 'Viagem criada com sucesso!',
            'viagem' => $viagem
        ], 201);
    }

    /**
     * Atualiza os dados de uma viagem existente.
     * Apenas administradores podem executar esta ação.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem editar viagens.'], 403);
        }
    
        $viagem = Viagem::findOrFail($id);
    
        $validated = $request->validate([
            'destino' => 'sometimes|string|max:255',
            'data_partida' => 'sometimes|date',
            'data_regresso' => 'sometimes|date|after_or_equal:data_partida',
            'preco' => 'sometimes|numeric|min:0',
        ]);
    
        $viagem->update($validated);
    
        return response()->json([
            'message' => 'Viagem atualizada com sucesso.',
            'viagem' => $viagem
        ]);
    }

    /**
     * Elimina uma viagem existente.
     * Apenas administradores podem executar esta ação.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem apagar viagens.'], 403);
        }
    
        $viagem = Viagem::findOrFail($id);
        
        // Eliminar a viagem (as reservas associadas serão eliminadas automaticamente devido à restrição onDelete('cascade'))
        $viagem->delete();
    
        return response()->json([
            'message' => 'Viagem eliminada com sucesso.'
        ]);
    }
}

