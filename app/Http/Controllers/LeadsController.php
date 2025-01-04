<?php

namespace App\Http\Controllers;

use App\Models\Leads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeadsController extends Controller
{
    public function importExcel(Request $request)
    {
        // Validate the incoming request for file
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        // Load the uploaded file
        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file);

        // Get the active sheet and its data
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip the header row and insert the data into the database
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;  // Skip header row

            $sanitizedData = [
                'name'  => strip_tags(trim($row[0] ?? '')),
                'email' => filter_var(trim($row[1] ?? '')),
                'phone' => preg_replace('/\D/', '' ,$row[2] ?? ''),
                'status' => strip_tags(trim($row[3]) ?? ''),
            ];

            $validator = Validator::make($sanitizedData,  [
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:leads,email',
                'phone' => 'nullable|regex:/^[0-9]{10}$/', // Example for a 10-digit phone number
                'status' => 'nullable|string|max:50',
            ]);
        
            if ($validator->fails()) {
                // Log or collect the error for reporting
                \Log::warning("Validation failed for row $index", [
                    'errors' => $validator->errors()->toArray(),
                    'row' => $row,
                ]);
                continue; // Skip to the next row
            }

            Leads::create([
                'name'  => $row[0],  // Assuming the second column is 'name'
                'email' => $row[1],  // Assuming the third column is 'email'
                'phone' => $row[2],  // Assuming the fourth column is 'phone'
                'status' => $row[3], // Assuming the fifth column is 'status'
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Leads imported successfully');
    }

    public function exportExcel()
    {
        
        \Log::info('export excel');
        $leads = Leads::get(); // Get all leads or any filtered data

        if ($leads->isEmpty()) {
            \Log::warning('No leads found for export.');
            return response()->json(['message' => 'No leads found to export.'], 404);
        }
        

        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header to sheet
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Phone');
        $sheet->setCellValue('D1', 'Status');

        // Add data to sheet
        $row = 2;  // Start from row 2 (after header)
        foreach ($leads as $lead) {
            $sheet->setCellValue('A' . $row, $lead->name);
            $sheet->setCellValue('B' . $row, $lead->email);
            $sheet->setCellValue('C' . $row, $lead->phone);
            $sheet->setCellValue('D' . $row, $lead->status);
            $row++;
        }

        // Set headers to download the file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'leads_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        // Send the response to browser to download the Excel file
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
        
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $leads = Leads::all();
            return DataTables::of($leads)
                ->addColumn('actions', function ($lead) {
                    return '
                        <a href="' . route('leads.edit', $lead->id) . '" class="btn btn-primary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-button" data-id="' . $lead->id . '">Delete</button>
                    ';
                })
                ->rawColumns(['actions']) // Ensure actions column renders HTML
                ->make(true);
        }

        return view('leads.index');
    }

    
    public function show($id)
    {
        $lead = Leads::findOrFail($id);  // Find the lead by ID or fail if not found
        return view('leads.show', compact('lead'));  // Pass the lead to the view
    }

    public function create(){
        return view('leads.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'nullable|regex:/^[0-9]{10}$/',
            'status' => 'required|in:New,In Progress,Closed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        // Sanitize input data (example sanitization)
        $sanitizedData = [
            'name'  => htmlspecialchars($validatedData['name']),
            'email' => strtolower(filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL)),
            'phone' => preg_replace('/[^0-9]/', '', $validatedData['phone']), // Keep only digits
            'status' => strip_tags($validatedData['status']),
        ];

        Leads::create($sanitizedData);

        return redirect()->route('leads.index')->with('success', 'Leads Created Successfully!');
    }

    public function edit($id){
        $lead = Leads::find($id);
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(),[
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:leads,email,'. $id,
            'phone' => 'sometimes|regex:/^[0-9]{10}$/',
            'status' => 'sometimes',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        $sanitizedData = [
            'name'  => htmlspecialchars($validatedData['name']),
            'email' => strtolower(filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL)),
            'phone' => preg_replace('/[^0-9]/', '', $validatedData['phone']),
            'status' => strip_tags($validatedData['status']),
        ];

        $lead = Leads::findOrFail($id);

        $lead->update($sanitizedData);        

        return redirect()->route('leads.index')->with('success', 'Leads Updated Successfully!');
    }

    public function destroy($id) {
        $lead = Leads::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully!');
    }
}
