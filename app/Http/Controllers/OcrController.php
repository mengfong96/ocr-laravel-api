<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;
use App\Models\Ocr;

class OcrController extends Controller
{
    public function __construct()
    {
        // add middleware for future functionality
    }

    public function readImage(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:jpg,png|max:2048',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if ($request->file('file')) {
            $originalName = $request->file('file')->getClientOriginalName();
            $originalExt = $request->file('file')->getClientOriginalExtension();
            $originalPathName = pathinfo($originalName, PATHINFO_FILENAME);

            $originalFileName = $originalPathName . "_" . time() . "." . $originalExt;
            $file = $request->file('file')->storeAs('public/documents', $originalFileName);

            if ($file) {
                try {
                    $read = (new TesseractOCR($request->file('file')))->run();
                    if ($read) {

                        //Upload log
                        Ocr::create([
                            'file_name' => $originalFileName,
                            'output_text' => $read,
                        ]);

                        return response()->json([
                            'status' => 'success',
                            'message' => 'Text extracted successfully',
                            'text' => $read
                        ], 201);
                    }
                } catch (UnsuccessfulCommandException $e) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Error:' . $e,
                    ], 403);
                }
            }
        }
    }
}
