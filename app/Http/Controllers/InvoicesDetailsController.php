<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\invoices_details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class InvoicesDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(invoices_details $invoices_details)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $invoices = invoices::where('id', $id)->first();
        $details = invoices_details::where('Id_invoice', $id)->get();
        $attachments = invoice_attachments::where('invoice_id', $id)->get();
        return view('invoices.details_invoice', compact('attachments', 'details', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, invoices_details $invoices_details)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //

        $invoices = invoice_attachments::findOrFail($request->id_file);
        $invoices->delete();
        Storage::disk('public_uploads')->delete($request->invoice_number . '/' . $request->file_name);
        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    // public function get_file($invoice_number , $file_name)
    // {
    //     $contents= Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
    //     return response()->download( $contents);
    // }

    public function get_file($invoice_number, $file_name)
    {
        $disk = Storage::disk('public_uploads');
        $file_path = $invoice_number . '/' . $file_name;

        // Check if the file exists
        if ($disk->exists($file_path)) {
            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $file_name . '"',
            ];

            return response()->stream(
                function () use ($disk, $file_path) {
                    echo $disk->readStream($file_path);
                },
                200,
                $headers
            );
        } else {
            // Handle the case where the file doesn't exist, e.g., return a 404 response.
            return response()->json(['error' => 'File not found'], 404);
        }
    }

    public function open_file($invoice_number, $file_name)
    {
        // $files = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
        // return response()->file($files);

        $file_path = Storage::disk('public_uploads')->path($invoice_number . '/' . $file_name);

        // Check if the file exists before returning it
        if (file_exists($file_path)) {
            return response()->file($file_path);
        } else {
            // Handle the case where the file doesn't exist, e.g., return a 404 response.
            return response()->json(['error' => 'File not found'], 404);
        }
    }
}
