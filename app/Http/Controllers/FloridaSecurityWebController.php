<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FloridaSecurityWebController extends Controller
{
    public function securityDashboard()
    {
        return view('admin.florida-security');
    }

    public function auditTrail()
    {
        return view('admin.florida-audit');
    }

    public function complianceManager()
    {
        return view('admin.florida-compliance');
    }

    public function dataExportTool()
    {
        return view('admin.florida-data-export');
    }
}
