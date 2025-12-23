<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Viewer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'user_agent',
        'location',
        'session_id',
    ];


    public static function recordView(Request $request)
    {
        try {
            if (Viewer::where('session_id', $request->session()->getId())->exists()) {
                return response()->json(['message' => 'Viewer data already exists'], 200);
            }

            $viewer = Viewer::create([
                'ip' => $request->ip(), // Get IP from request method
                'user_agent' => $request->header('User-Agent'),
                'location' =>  "",
                'session_id' => $request->session()->getId(),
            ]);
            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}
