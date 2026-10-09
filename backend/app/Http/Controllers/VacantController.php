<?php

namespace App\Http\Controllers;

use App\Models\Vacant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;



class VacantController extends Controller
{
    public function index()
    {
        $vacants = Vacant::all();

        return response()->json([
            'Status' => Response::HTTP_OK,
            'Massage' => "Success",
            'vacants' => $vacants

        ]);

        
    }
    public function post(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'vacant_title' => 'string|require',
            'vacant_link' => 'string|require',
        ]);

        $vacant = Vacant::create($validate);

        return response()->json([
            'Status' => Response::HTTP_OK,
            'Massage' => 'Success added data',
        ]);
    }

    public function delete(Request $request, string $id)
    {
        $vacant = Vacant::findOrFail($id);
        $vacant->destroy();

        return response()->json([
            'Status' => Response::HTTP_OK,
            'Massage' => 'vacant deleted',
        ]);

    }
}
