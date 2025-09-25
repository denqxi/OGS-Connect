<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\WeeklyDataImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new WeeklyDataImport, $request->file('file'));

        return back()->with('success', 'Weekly Excel processed into daily schedule!');
    }
}
