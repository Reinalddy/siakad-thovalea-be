<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index()
    {
        $rooms = $this->roomService->getAll();
        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data ruangan',
            'data'    => $rooms,
        ], 200);
    }

    public function store(RoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Ruangan berhasil ditambahkan',
            'data'    => $room,
        ], 201);
    }

    public function update(RoomRequest $request, $id)
    {
        try {
            $room = $this->roomService->update($id, $request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Ruangan berhasil diperbarui',
                'data'    => $room,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->roomService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Ruangan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}