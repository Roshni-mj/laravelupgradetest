<?php

namespace App\Http\Controllers;
use App\Dummy;
use Illuminate\Http\Request;
use DB;
use Excel;
use File;
class DummyController extends Controller
{
    public function importdata() 
    {
        return view('importdata');
    }
    public function import(Request $request) 
    {
        //validate the xls file
        $this->validate($request, array(
            'file' => 'required'
        ));

        if ($request->hasFile('file')) {

            $extension = File::extension($request->file->getClientOriginalName());

            if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {

                $path = $request->file->getRealPath();

                $excel = Excel::load($path)->all()->toArray();

                if (!empty($excel) && count($excel)) {

                    foreach ($excel as $excel_data) {

                      

                        $insert_array[] = [
                            'name' =>  $excel_data['name'],
                             'email' => $excel_data['email'],
                          'created_at' =>date("Y-m-d H:i:s"),
                          'updated_at' =>date("Y-m-d H:i:s"),
                        ];
                    }

                    if (!empty($insert_array)) {
                        foreach ($insert_array as $insert) {
                            $insertData = DB::table('dummies')->insert($insert);
                        }

                        if ($insertData) {
                            return redirect()->route('importdata')
                                            ->with('success', 'Your Data has successfully imported');
                        } else {

                            return redirect()->route('importdata')
                                            ->with('error', 'Error inserting the data..');
                        }
                    }
                }

                return back();
            } else {
                return redirect()->route('importdata')
                                ->with('error', 'File is a ' . $extension . ' file.!! Please upload a valid xls/csv file..!!');
            }
        }
    }

}
