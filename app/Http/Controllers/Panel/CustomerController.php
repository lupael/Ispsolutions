<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard() {}
    public function profile() {}
    public function updateProfile() {}
    public function submitDocumentVerification() {}
    public function billing() {}
    public function usage() {}
    public function tickets() {}
    public function viewPackages() {}
    public function requestUpgrade() {}
    public function requestDowngrade() {}
    public function downloadInvoicePdf($invoice) {}
    public function viewInvoicePdf($invoice) {}
    public function downloadPaymentReceiptPdf($payment) {}
    public function accountStatementPdf() {}
}
