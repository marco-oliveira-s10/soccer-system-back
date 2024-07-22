<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends Controller
{

    public function listEventPagination(Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);

        $events = Event::with('location')
        ->orderBy('id_event', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($events);
    }

    public function filterEventsByName(Request $request)
    {
        try {

            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);

            $name = $request->query('name');

            if (empty($name)) {
                throw new Exception('Name was not provided.');
            }
            $locations = Event::with('location')->where('name_event', 'like', '%' . $name . '%')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($locations);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createEvent(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedEvent = $request->validate([
                'name_event' => 'required|string|max:255',
                'id_location' => 'required|integer|exists:locations,id_location',
                'date_event' => 'required|date',
                'teams' => 'required|array',
                'teams.*.name_team' => 'required|string|max:255',
                'teams.*.level_team' => 'required|integer',
                'teams.*.players' => 'required|array',
                'teams.*.players.*' => 'required|integer|exists:players,id_player'
            ]);

            $event = Event::create([
                'name_event' => $validatedEvent['name_event'],
                'id_location' => $validatedEvent['id_location'],
                'date_event' => $validatedEvent['date_event'],
            ]);

            foreach ($validatedEvent['teams'] as $teamData) {
                $team = Team::create([
                    'id_event' => $event->id_event,
                    'name_team' => $teamData['name_team'],
                    'level_team' => $teamData['level_team'],
                ]);

                foreach ($teamData['players'] as $playerId) {
                    DB::table('players_teams')->insert([
                        'id_team' => $team->id_team,
                        'id_player' => $playerId
                    ]);
                }
            }

            DB::commit();

            $eventWithTeams = Event::with(['teams.players'])->find($event->id_event);

            return response()->json($eventWithTeams, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createTeam(Request $request)
    {
        $validated = $request->validate([
            'id_event' => 'required|integer|exists:events,id_event',
            'name_team' => 'required|string|max:255',
            'level_team' => 'required|integer',
        ]);

        $team = Team::create($validated);

        return response()->json($team, 201);
    }

    public function addPlayerToTeam(Request $request)
    {
        $validated = $request->validate([
            'id_team' => 'required|integer|exists:teams,id_team',
            'id_player' => 'required|integer|exists:players,id_player',
        ]);

        $existing = \DB::table('players_teams')
        ->where('id_team', $validated['id_team'])
        ->where('id_player', $validated['id_player'])
        ->exists();

        if ($existing) {
            return response()->json(['message' => 'Player is already in this team.'], 400);
        }

        \DB::table('players_teams')->insert($validated);

        return response()->json(['message' => 'Player added to team.'], 201);
    }

    public function getTotalEvents()
    {
        $total = Event::count();

        return response()->json(['total' => $total]);
    }

    public function delete($id)
    {
        try {
            $event = Event::findOrFail($id);
            \DB::table('players_teams')->whereIn('id_team', function ($query) use ($id) {
                $query->select('id_team')->from('teams')->where('id_event', $id);
            })->delete();

            Team::where('id_event', $id)->delete();

            $event->delete();

            return response()->json(['message' => 'Event deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not found.'], 404);
        }
    }

    public function findById($id)
    {
        $event = Event::with(['teams.players', 'location'])
        ->where('id_event', $id)
        ->firstOrFail();

        return response()->json($event);
    }

    public function createPlayerDraw()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['confirmados']) || !isset($data['numeroplayersPorTime'])) {

            return response()->json(["error" => "Invalid playload"]);
            exit;
        }

        $confirmados = $data['confirmados'];
        $numeroPlayersPorTime = $data['numeroplayersPorTime'];
        $goleiros = [];
        $outrosJogadores = [];

        foreach ($confirmados as $jogador) {
            if ($jogador['position_player'] == 'GOL') {
                $goleiros[] = $jogador;
            } else {
                $outrosJogadores[] = $jogador;
            }
        }

        list($timesPrincipais, $reservas) = $this->distributePlayersInTimes($outrosJogadores, $numeroPlayersPorTime, $goleiros);

        $response = [
            'principais' => $timesPrincipais,
            'reservas' => $reservas
        ];

        return response()->json($response);
    }

    public function shufflePlayers($players) {
        shuffle($players);
        return $players;
    }

    public function distributePlayersInTimes($jogadores, $numeroPorTime, &$goleiros) {

        $times = [];
        $reservas = []; 
        $jogadores = $this->shufflePlayers($jogadores); 
        $goleiros = $this->shufflePlayers($goleiros);

        if (empty($jogadores) && empty($goleiros)) {
            return response()->json(["error" => "There are no players to distribute."]);
            exit;
        }

        while (count($jogadores) >= $numeroPorTime || !empty($goleiros)) {
            $timeAtual = [];

            if (empty(array_filter($timeAtual, function($jogador) {

                return $jogador['position_player'] == 'GOL';

            }))) {

                if (!empty($goleiros)) {

                    $timeAtual[] = array_shift($goleiros);

                } else {

                    if (!empty($jogadores)) {

                        $reservas[] = [
                            'time' => 'Reserva',
                            'pontos' => array_reduce($jogadores, function($sum, $jogador) {

                                return $sum + $jogador['level_player'];

                            }, 0),
                            'jogadores' => $jogadores
                        ];

                        $jogadores = [];
                    }

                    break;
                }
            }

            while (count($timeAtual) < $numeroPorTime && !empty($jogadores)) {

                $timeAtual[] = array_shift($jogadores);
            }

            $temGoleiro = array_filter($timeAtual, function($jogador) {

                return $jogador['position_player'] == 'GOL';

            });

            if (count($timeAtual) == $numeroPorTime && $temGoleiro) { 

                $pontos = array_reduce($timeAtual, function($sum, $jogador) {

                    return $sum + $jogador['level_player'];

                }, 0);

                $times[] = [
                    'time' => count($times) + 1,
                    'pontos' => $pontos,
                    'jogadores' => $timeAtual,
                ];

            } else {

                $reservas[] = [
                    'time' => 'Reserva',
                    'pontos' => array_reduce($timeAtual, function($sum, $jogador) {
                        return $sum + $jogador['level_player'];
                    }, 0),
                    'jogadores' => $timeAtual,
                ];
            }
        }

        if (!empty($jogadores)) {

            $reservas[] = [
                'time' => 'Reserva',
                'pontos' => array_reduce($jogadores, function($sum, $jogador) {
                    return $sum + $jogador['level_player'];
                }, 0),
                'jogadores' => $jogadores,
            ];

        }

        if (!empty($goleiros)) {
            $reservas[] = [
                'time' => 'Reserva',
                'pontos' => array_reduce($goleiros, function($sum, $jogador) {
                    return $sum + $jogador['level_player'];
                }, 0),
                'jogadores' => $goleiros,
            ];
        }

        return [$times, $reservas];
    }
}