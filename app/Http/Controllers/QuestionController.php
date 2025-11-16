<?php

namespace App\Http\Controllers;

use App\Models\ChapterQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index($chapterId)
    {
        try {
            \Log::info("QuestionController: Fetching questions for chapter {$chapterId}");
            
            $questions = ChapterQuestion::where('chapter_id', $chapterId)
                ->orderBy('order_index')
                ->get();
            
            \Log::info("QuestionController: Found {$questions->count()} questions");
            
            $processedQuestions = [];
            
            foreach ($questions as $question) {
                $data = [
                    'id' => $question->id,
                    'chapter_id' => $question->chapter_id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'correct_answer' => $question->correct_answer,
                    'explanation' => $question->explanation,
                    'points' => $question->points,
                    'order_index' => $question->order_index,
                    'options' => []
                ];
                
                // Handle options safely
                if ($question->options) {
                    if (is_string($question->options)) {
                        $decoded = json_decode($question->options, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $data['options'] = $decoded;
                        }
                    } elseif (is_array($question->options)) {
                        $data['options'] = $question->options;
                    }
                }
                
                $processedQuestions[] = $data;
            }
            
            \Log::info("QuestionController: Processed " . count($processedQuestions) . " questions successfully");
            
            return response()->json($processedQuestions);
        } catch (\Exception $e) {
            \Log::error("QuestionController error: " . $e->getMessage());
            return response()->json([]);
        }
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
        try {
            \Log::info("QuestionController: Looking for question ID {$id}");
            
            // Try ChapterQuestion first
            $question = ChapterQuestion::find($id);
            
            // If not found, try Question model
            if (!$question) {
                $question = \App\Models\Question::find($id);
                if ($question) {
                    \Log::info("QuestionController: Found question ID {$id} in questions table");
                }
            } else {
                \Log::info("QuestionController: Found question ID {$id} in chapter_questions table");
            }
            
            if (!$question) {
                \Log::warning("QuestionController: Question ID {$id} not found in either table");
                return response()->json(['error' => 'Question not found'], 404);
            }
            
            $data = [
                'id' => $question->id,
                'chapter_id' => $question->chapter_id,
                'question_text' => $question->question_text ?? '',
                'question_type' => $question->question_type ?? 'multiple_choice',
                'correct_answer' => $question->correct_answer ?? '',
                'explanation' => $question->explanation ?? '',
                'points' => $question->points ?? 1,
                'order_index' => $question->order_index ?? 1,
                'options' => []
            ];
            
            // Handle options safely
            if ($question->options) {
                if (is_string($question->options)) {
                    $decoded = json_decode($question->options, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $data['options'] = $decoded;
                    } else {
                        // If JSON decode fails, treat as plain text
                        $data['options'] = [$question->options];
                    }
                } elseif (is_array($question->options)) {
                    $data['options'] = $question->options;
                }
            }
            
            \Log::info("QuestionController: Returning data for question ID {$id}: " . json_encode($data));
            
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error("QuestionController show error: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info("QuestionController: Updating question ID {$id}");
            
            // Try ChapterQuestion first
            $question = ChapterQuestion::find($id);
            $isChapterQuestion = true;
            
            // If not found, try Question model
            if (!$question) {
                $question = \App\Models\Question::find($id);
                $isChapterQuestion = false;
            }
            
            if (!$question) {
                \Log::warning("QuestionController: Question ID {$id} not found for update");
                return response()->json(['error' => 'Question not found'], 404);
            }
            
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
            
            \Log::info("QuestionController: Successfully updated question ID {$id}");
            
            return response()->json($question);
        } catch (\Exception $e) {
            \Log::error("QuestionController update error: " . $e->getMessage());
            return response()->json(['error' => 'Update failed'], 500);
        }
    }

    public function destroy($id)
    {
        $question = ChapterQuestion::findOrFail($id);
        $question->delete();
        return response()->json(['message' => 'Question deleted']);
    }
}
