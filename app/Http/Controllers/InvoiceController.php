<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['payment.user', 'payment.enrollment.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($invoices);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);
        return response()->json($invoice);
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);
        
        $pdf = Pdf::loadView('invoices.template', compact('invoice'));
        
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:payments,id',
                'invoice_number' => 'required|string|unique:invoices',
                'total_amount' => 'required|numeric|min:0',
                'invoice_date' => 'required|date',
            ]);

            $invoice = Invoice::create($request->all());
            return response()->json($invoice->load(['payment.user', 'payment.enrollment.course']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        try {
            $request->validate([
                'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $invoice->id,
                'total_amount' => 'sometimes|numeric|min:0',
                'invoice_date' => 'sometimes|date',
            ]);

            $invoice->update($request->all());
            return response()->json($invoice->load(['payment.user', 'payment.enrollment.course']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function send(Invoice $invoice)
    {
        return $this->emailInvoice($invoice);
    }

    public function emailInvoice(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.template', compact('invoice'));
        
        \Mail::send('emails.invoice', compact('invoice'), function ($message) use ($invoice, $pdf) {
            $message->to($invoice->payment->user->email)
                    ->subject('Invoice ' . $invoice->invoice_number)
                    ->attachData($pdf->output(), $invoice->invoice_number . '.pdf');
        });

        $invoice->update(['sent_at' => now()]);

        return response()->json(['message' => 'Invoice emailed successfully']);
    }

    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['payment.user', 'payment.enrollment.course']);
        
        $pdf = Pdf::loadView('invoices.template', compact('invoice'));
        
        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
