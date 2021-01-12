<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use thiagoalessio\TesseractOCR\TesseractOCR;
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

            $file = $request->file('file')->store('documents', 'public');

            if ($file) {
                $read = (new TesseractOCR($request->file('file')))->run();
                if ($read) {

                    //Upload log
                    Ocr::create([
                        'file_name' => $file,
                        'output_text' => $read,
                    ]);

                    return response()->json([
                        'message' => 'Text extracted successfully',
                        'text' => $read
                    ], 201);
                }
            }
        }
    }
}
