<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class YearlyReportController extends Controller
{
    public function index() {}
    public function cardDistributorPayments() {}
    public function cashIn() {}
    public function cashOut() {}
    public function operatorIncome() {}
    public function expenses() {}
    public function exportExcel($reportType) {}
    public function exportPdf($reportType) {}
}
