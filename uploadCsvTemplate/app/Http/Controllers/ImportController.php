<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \GuzzleHttp\Client;
use Mail;
class ImportController extends Controller
{
  /**
     * read csv file and send to api.
     *
     * @param  array $request
     * @return array $response
     */
    public function fnImport(Request $request)
    {
    ini_set('max_execution_time', 3000);
    $file = $request->file('excel');
    if($file->getClientOriginalExtension() !="csv"){
        $request->session()->flash('error', 'Please choose a valid csv file to submit.');
    }
    else{
        $row           = 1;
        $intSuccessRow = 0;
        $arrayError    = [];
        $arrayModules  = [];

        if (($handle = fopen($file, "r")) !== FALSE) {
            $strHeader1 = "";
            $strHeader2 = "";
            $strHeader3 = "";
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($row > 1){
                    if($data[0]==""||$data[1]==""||$data[2]==""){
                        $arrayError[$row][$strHeader1] = $data[0];
                        $arrayError[$row][$strHeader2] = $data[1];
                        $arrayError[$row][$strHeader3] = $data[2];
                        $arrayError[$row]["errorType"] = "invalid_column";
                    }
                    else{
                        $client = new Client([
                            "headers"=>["content-type"=>"application/json","accept"=>"application/json"],
                        ]);
                        $response = $client->request('GET', 'http://localhost/uploadCsvApi/api/importCsv', [
                            'json' => [
                                'moduleCode' => $data[0],
                                'moduleName' => $data[1],
                                'moduleTerm' => $data[2],
                            ]
                        ]);
                        if($response->getBody()=="Success"){
                            $intSuccessRow++;
                        }
                        else{
                            $arrayError[$row][$strHeader1] = $data[0];
                            $arrayError[$row][$strHeader2] = $data[1];
                            $arrayError[$row][$strHeader3] = $data[2];
                            $arrayError[$row]["errorType"] = "unable_to_save";
                        }
                    }
                }
                else{
                    $strHeader1   = $data[0];
                    $strHeader2   = $data[1];
                    $strHeader3   = $data[2];
                    $arrayModules = $data;
                }
                $row++;
            }
            $errorArray = $this->customValidation($arrayError,$arrayModules);
            $request->session()->flash('error', implode(",\n\r",$errorArray));
            $request->session()->flash('status', $intSuccessRow.' rows imported successfully!');
            $errorMailText   = implode(",\n\r",$errorArray);
            $successMailText = $intSuccessRow.' rows imported successfully!';
             Mail::send('mailTemplate', ['error' => $errorMailText,'success'=>$successMailText], function ($message) use ($data) {
                $message->from('pmukesh15@gmail.com', 'Mukesh');
                $message->to('charush@accubits.com', "charush")->subject('CSV LOG');
            });
        }
    }
    return view('welcome');
    }
    /*
    * CSV upload custom validation
    */
    public function customValidation($arrayError){
        $errArray = [];
        foreach($arrayError as $key=>$error){
            foreach($error as $key2=>$err){
                if($err==""){
                    $errArray[] = "Header column (".$key2." at column ".$key.") is empty or incorrect in csv file";
                }
            }
        }
        return $errArray;
    }
}
