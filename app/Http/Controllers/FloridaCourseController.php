<?php

namespace App\Http\Controllers;

use App\Models\FloridaCourse;
use Illuminate\Http\Request;

class FloridaCourseController extends Controller
{
    public function indexWeb()
    {
        $courses = FloridaCourse::where('is_active', true)->get();
        return response()->json($courses);
    }
    
    public function updateWeb(Request $request, $id)
    {
        $course = FloridaCourse::findOrFail($id);
        $course->update($request->all());
        return response()->json($course);
    }
}
