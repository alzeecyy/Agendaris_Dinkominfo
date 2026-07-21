<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agenda;

class UpdateAgendaLokasiSeeder extends Seeder
{
    /**
     * Run the database seeds to update all existing agenda locations.
     */
    public function run(): void
    {
        $newRooms = [
            'Aula Rapat Dinkominfo',
            'Ruang Pelatihan',
            'Smart Room Graha Satria'
        ];

        // Fetch all agendas
        $agendas = Agenda::all();

        foreach ($agendas as $index => $agenda) {
            // Assign room based on round-robin (index modulo count of new rooms)
            $room = $newRooms[$index % count($newRooms)];
            $agenda->update([
                'lokasi' => $room
            ]);
        }
    }
}
