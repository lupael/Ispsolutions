<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterPackageController extends Controller
{
    public function index() {}
    public function create() {}
    public function store() {}
    public function show($masterPackage) {}
    public function edit($masterPackage) {}
    public function update($masterPackage) {}
    public function destroy($masterPackage) {}
    public function assignToOperators($masterPackage) {}
    public function storeOperatorAssignment($masterPackage) {}
    public function removeOperatorAssignment($masterPackage, $operatorRate) {}
    public function stats($masterPackage) {}
    public function hierarchy() {}
    public function comparison() {}
}
