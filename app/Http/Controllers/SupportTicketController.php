<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('=== Support Tickets Index START ===');
            \Log::info('User authenticated: ' . (auth()->check() ? 'yes' : 'no'));
            \Log::info('User ID: ' . (auth()->id() ?? 'null'));
            
            $query = SupportTicket::with(['user', 'replies']);

            if (auth()->check() && auth()->user()->role_id != 1) {
                \Log::info('Filtering tickets for user: ' . auth()->id());
                $query->where('user_id', auth()->id());
            } else {
                \Log::info('Loading all tickets (admin or not authenticated)');
            }

            $tickets = $query->when($request->status, function($q, $status) {
                    return $q->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            \Log::info('Tickets loaded: ' . $tickets->count());
            \Log::info('=== Support Tickets Index END ===');

            // For web view
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return view('admin.support.tickets');
            }

            return response()->json($tickets);
        } catch (\Exception $e) {
            \Log::error('=== Support Tickets Index ERROR ===');
            \Log::error('Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            \Log::info('=== Support Ticket Store START ===');
            \Log::info('Request data: ', $request->all());
            
            $request->validate([
                'subject' => 'required|string|max:255',
                'category' => 'nullable|string',
                'priority' => 'nullable|in:low,medium,high,critical',
                'description' => 'required|string'
            ]);

            \Log::info('Validation passed');

            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'description' => $request->description,
                'email' => auth()->user()->email,
                'priority' => $request->priority ?? 'medium',
                'status' => 'open'
            ]);

            \Log::info('Ticket created: ' . $ticket->id);
            \Log::info('=== Support Ticket Store END ===');

            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            \Log::error('=== Support Ticket Store ERROR ===');
            \Log::error('Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'replies.user'])->findOrFail($id);

        if (auth()->user()->role_id != 1 && $ticket->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($ticket);
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'message' => 'required|string'
        ]);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_staff_reply' => auth()->user()->role_id == 1
        ]);

        $ticket->update(['status' => 'replied']);

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:open,replied,resolved,closed'
        ]);

        $ticket->update([
            'status' => $request->status,
            'resolved_at' => $request->status === 'resolved' ? now() : null
        ]);

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }
}
