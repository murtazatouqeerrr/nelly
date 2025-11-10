<?php

namespace App\Http\Controllers;

use App\Models\ChapterQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index($chapterId)
    {
        $questions = ChapterQuestion::where('chapter_id', $chapterId)
            ->orderBy('order_index')
            ->get();
        
        \Log::info("QuestionController: Fetching questions for chapter {$chapterId}, found: " . $questions->count());
        
        return response()->json($questions);
    }

    public function store(Request $request, $chapterId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false',
            'options' => 'required|string',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'order_index' => 'required|integer|min:1'
        ]);

        $validated['chapter_id'] = $chapterId;
        $question = ChapterQuestion::create($validated);
        
        return response()->json($question, 201);
    }

    public function show($id)
    {
        $question = ChapterQuestion::findOrFail($id);
        return response()->json($question);
    }

    public function update(Request $request, $id)
    {
        $question = ChapterQuestion::findOrFail($id);
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false',
            'options' => 'required|string',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'order_index' => 'required|integer|min:1'
        ]);

        $question->update($validated);
        return response()->json($question);
    }

    public function destroy($id)
    {
        $question = ChapterQuestion::findOrFail($id);
        $question->delete();
        return response()->json(['message' => 'Question deleted']);
    }
}
