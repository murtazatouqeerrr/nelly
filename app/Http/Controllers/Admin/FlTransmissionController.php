<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendFloridaTransmissionJob;
use App\Models\StateTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlTransmissionController extends Controller
{
    /**
     * Display pending and error transmissions.
     */
    public function index(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $query = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $transmissions = $query->paginate(50);

        // Separate by status for display - filter out orphaned transmissions
        $pending = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->pending()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'asc')
            ->paginate(25, ['*'], 'pending_page');

        $errors = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->error()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'error_page');

        $successful = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState('FL')
            ->success()
            ->whereHas('enrollment') // Only include transmissions with valid enrollments
            ->orderBy('sent_at', 'desc')
            ->paginate(25, ['*'], 'success_page');

        return view('admin.fl-transmissions.index', compact('pending', 'errors', 'successful'));
    }

    /**
     * Send a single transmission.
     */
    public function sendSingle(Request $request, $id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'pending') {
            return back()->with('error', 'Only pending transmissions can be sent.');
        }

        try {
            SendFloridaTransmissionJob::dispatch($transmission->id);

            Log::info('Manual transmission dispatch', [
                'transmission_id' => $transmission->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Transmission queued successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to queue transmission: '.$e->getMessage());
        }
    }

    /**
     * Send all pending transmissions.
     */
    public function sendAll(Request $request)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $pendingTransmissions = StateTransmission::forState('FL')
            ->pending()
            ->get();

        if ($pendingTransmissions->isEmpty()) {
            return back()->with('info', 'No pending transmissions to send.');
        }

        $count = 0;
        foreach ($pendingTransmissions as $transmission) {
            try {
                SendFloridaTransmissionJob::dispatch($transmission->id);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to dispatch transmission in batch', [
                    'transmission_id' => $transmission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Batch transmission dispatch', [
            'count' => $count,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', "Successfully queued {$count} transmissions.");
    }

    /**
     * Retry a failed transmission.
     */
    public function retry(Request $request, $id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);

        if ($transmission->status !== 'error') {
            return back()->with('error', 'Only error transmissions can be retried.');
        }

        try {
            // Reset to pending
            $transmission->update([
                'status' => 'pending',
                'response_code' => null,
                'response_message' => null,
            ]);

            // Dispatch job
            SendFloridaTransmissionJob::dispatch($transmission->id);

            Log::info('Manual transmission retry', [
                'transmission_id' => $transmission->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Transmission retry queued successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to retry transmission', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to retry transmission: '.$e->getMessage());
        }
    }

    /**
     * View transmission details.
     */
    public function show($id)
    {
        // Authorization check
        if (! auth()->check() || ! auth()->user()->role || ! in_array(auth()->user()->role->slug, ['super-admin', 'admin', 'school-admin'])) {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::with(['enrollment.user', 'enrollment.course', 'enrollment.floridaCertificate'])
            ->findOrFail($id);

        return view('admin.fl-transmissions.show', compact('transmission'));
    }

    /**
     * Delete a transmission record.
     */
    public function destroy($id)
    {
        // Authorization check - only super admin
        if (! auth()->check() || ! auth()->user()->role || auth()->user()->role->slug !== 'super-admin') {
            abort(403, 'Unauthorized access');
        }

        $transmission = StateTransmission::findOrFail($id);
        $transmission->delete();

        Log::info('Transmission deleted', [
            'transmission_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Transmission deleted successfully.');
    }
}
