<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\invoices;
use App\Models\sections;
use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use App\Models\invoices_details;
use App\Notifications\AddInvoice;
use Illuminate\Support\Facades\DB;
use App\Models\invoice_attachments;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage; // Add this line

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $sections = sections::all();

        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $invoiceDate = date('Y-m-d', strtotime($request->invoice_Date));

        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $invoiceDate,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        $invoice_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);

        if ($request->hasFile('pic')) {
            $this->validate(
                $request,
                ['pic' => 'required|max:10000'],
                ['pic.mines' => 'تم حفظ'],
            );

            $invoice_id = invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new invoice_attachments();

            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;

            $attachments->save();

            //mov pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number) , $imageName);
        }

        $user = User::first();

        Notification::send($user, new AddInvoice($invoice_id));

        session()->flash('Add' , 'success Add invoice');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //

        $invoices = invoices::where('id' , $id)->first();

        return view('invoices.Status_Update' , compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //

        $invoices = invoices::where('id' , $id)->first();
        $sections = sections::all();

        return view('invoices.edit_invoice' , compact('invoices' , 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $invoices = invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        session()->flash('edit' ,  'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
        $id = $request->invoice_id;
        $invoices = invoices::where('id', $id)->first();
        $Details = invoice_attachments::where('invoice_id', $id)->first();

         $id_page =$request->id_page;

         if (!$id_page==2) {

             if (!empty($Details->invoice_number)) {
            Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number. '/' . $Details->file_name);
        }

        $invoices->forceDelete();
        session()->flash('delete_invoice');
        return redirect('/invoices');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }
    }

    public function getproducts($id)
    {
        $states = DB::table('products')->where('section_id', $id)->pluck("product_name", "id");

        return json_encode($states);
    }

  public function Status_Update($id, Request $request)
    {
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            // Convert the date to the correct format (YYYY-MM-DD)
            $paymentDate = date('Y-m-d', strtotime($request->Payment_Date));

            $invoices->update([
                'Value_Status' => $request->Status === 'مدفوعة' ? 1 : 3,
                'Status' => $request->Status,
                'Payment_Date' => $paymentDate,
            ]);

            invoices_Details::create([
            'id_Invoice' => $request->invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => $request->Status,
            'Value_Status' => $request->Status === 'مدفوعة' ? 1 : 3,
            'note' => $request->note,
            'Payment_Date' => $paymentDate,
            'user' => Auth::user()->name,
        ]);
        }

        else {
                $paymentDate = date('Y-m-d', strtotime($request->Payment_Date));
            $invoices->update([
                'Value_Status' => $request->Status === 'مدفوعة' ? 1 : 3,
                'Status' => $request->Status,
                'Payment_Date' => $paymentDate,
            ]);
            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $paymentDate,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }


    public function Invoice_Paid() {
        $invoices = invoices::where('Value_Status' , 1)->get();
        return view('invoices.invoices_Paid' , compact('invoices'));
    }


    public function Invoice_UnPaid() {
        $invoices = invoices::where('Value_Status' , 2)->get();
        return view ('invoices.invoices_UnPaid' , compact('invoices'));
    }



    public function Invoice_Partial() {
        $invoices = invoices::where('Value_Status' , 3)->get();
        return view('invoices.invoices_Partial' , compact('invoices'));

    }

    public function Print_invoice($id){
        $invoices = invoices::where('id' , $id)->first();
        return view('invoices.Print_invoice' , compact('invoices'));
    }


     public function export()
    {
       return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }


}