<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    public function listPlayersPagination(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);

            $players = Player::where('position_player', '!=', '')
            ->orderBy('id_player', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json($players);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function filterPlayersByName(Request $request)
    {
        try {
            $name = $request->query('name');

            if (empty($name)) {
                throw new \Exception('Player name was not provided.');
            }

            $players = Player::where('position_player', '!=', '')
            ->where('name_player', 'like', '%' . $name . '%')
            ->get();

            return response()->json($players);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTotalPlayers()
    {
        try {
            $total = Player::where('position_player', '!=', '')->count();
            return response()->json(['total' => $total]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $data = $request->validate([
                'playerName' => 'required|string',
                'playerLevel' => 'required|integer',
                'playerPosition' => 'required|string',
                'playerAge' => 'required|integer',
            ]);

            $player = Player::create([
                'name_player' => $data['playerName'],
                'level_player' => $data['playerLevel'],
                'position_player' => $data['playerPosition'],
                'age_player' => $data['playerAge'],
            ]);

            return response()->json($player, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $player = Player::findOrFail($id);
            $player->update([
                'name_player' => 'Player Removed',
                'level_player' => 0,
                'position_player' => '',
                'age_player' => 0,
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function findById($id)
    {
        try {
            $player = Player::where('id_player', $id)
            ->where('position_player', '!=', '')
            ->firstOrFail();

            return response()->json($player);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'playerName' => 'required|string',
                'playerLevel' => 'required|integer',
                'playerPosition' => 'required|string',
                'playerAge' => 'required|integer',
            ]);

            $player = Player::findOrFail($id);
            $player->update([
                'name_player' => $data['playerName'],
                'level_player' => $data['playerLevel'],
                'position_player' => $data['playerPosition'],
                'age_player' => $data['playerAge'],
            ]);

            return response()->json($player);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
