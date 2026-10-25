<?php

namespace App\Http\Controllers;

use App\Models\Vacant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;



class VacantController extends Controller
{
    public function index()
    {
        $vacants = Vacant::all();

        if ($vacants->isEmpty()){
            return response()->json([
                'Status' => 'success',
                'Massage' => $vacants->isEmpty() ? 'data kosong' : 'data ditemukan',
                'vacants' => $vacants
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'Status' => "success",
                'Massage' => "data berhasil di ambil",
                'vacants' => $vacants
            ], Response::HTTP_OK);
        }


        
    }
    
    public function show(string $id)
    {
        $vacant = Vacant::findOrFail($id);

        if ($vacant) {
            return response()->json([
                'Status' => "success",
                'Massage' => "data berhasil di ambil",
                'vacant' => $vacant
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'Status' => "failed",
                'Massage' => "data tidak ditemukan",
            ], Response::HTTP_NOT_FOUND);
        };
    } 

    public function post(Request $request)
    {
        $validate = $request->validate([
            'vacant_name' => 'string|required',
            'vacant_desc' => 'string|required',
            'vacant_link' => 'string|required',
        ]);

        $vacant = Vacant::create($validate);

        return response()->json([
            'Status' => "success",
            'Massage' => 'Success added data',
        ], Response::HTTP_OK);
    }

    public function delete(string $id)
    {
        $vacant = Vacant::findOrFail($id);

        $vacant->delete();

        return response()->json([
            'status' => 'success',
            'massage' => 'vacant deleted',
        ], Response::HTTP_OK);

    }

    public function update(Request $request, Vacant $vacant)
    {
        
        $data = $request->validate([
            'vacant_name' => 'string|required|sometimes',
            'vacant_desc' => 'string|required|sometimes',
            'vacant_link' => 'string|required|sometimes',
        ]);

        $vacant->update($data);

        return response()->json([
            'status' => "success",
            'message' => "data berhasil di update",
            'data' => $data,
        ], Response::HTTP_OK);
    }
}
