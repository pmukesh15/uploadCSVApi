<?php

namespace App\Http\Controllers\Apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportCsvController extends Controller
{
    /**
     * save imported data to db
     *
     * @param  array  $request
     * @return json $response
     */
    protected function importCsv(Request $request)
    {
        $moduleCode = $request->input("moduleCode");
        $moduleName = $request->input("moduleName");
        $moduleTerm = $request->input("moduleTerm");
        ini_set('max_execution_time', 3000);
        DB::table('tbl_modules')->insert(
            ['vchr_module_code' => $moduleCode, 'vchr_module_name' => $moduleName, 'vchr_module_terms' => $moduleTerm]
        );
        return "Success";
    }
}
