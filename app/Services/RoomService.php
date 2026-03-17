<?php

namespace App\Services;

use App\Models\Room;
use Exception;

class RoomService
{
    public function getAll()
    {
        return Room::orderBy('kode_ruang', 'asc')->get();
    }

    public function create(array $data)
    {
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = Room::find($id);

        if (!$room) {
            throw new Exception('Ruangan tidak ditemukan.', 404);
        }

        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        $room = Room::find($id);
        
        if (!$room) {
            throw new Exception('Ruangan tidak ditemukan.', 404);
        }

        return $room->delete();
    }
}