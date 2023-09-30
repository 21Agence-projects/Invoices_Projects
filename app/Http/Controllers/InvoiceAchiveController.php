<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\invoices;


class InvoiceAchiveController extends Controller
{
    //

    public function index(){

        $invoices = invoices::onlyTrashed()->get();
        return view('Invoices.Archive_Invoices' , compact('invoices'));

    }

    public function update(Request $request){
        $id = $request->invoice_id;
        $flight = invoices::withTrashed()->where('id' , $id)->restore();

        session()->flash('restore_invoice');
        return redirect('/invoices');

    }

  public function destroy(Request $request) {

        $invoice = invoices::withTrashed()->findOrFail($request->invoice_id);
        $invoice->forceDelete();

        session()->flash('delete_invoice');
        return redirect('/Archive');
}

}
