<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppUser\UpdateAppUser;
use App\Models\AppUser;
use App\Models\Country;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CsvController extends Controller
{
    public function __construct()
    {

    }

    public function read()
    {
        $file = public_path('files/countries.csv');

        $customerArr = $this->csvToArray($file);
        foreach ($customerArr as $images) {

                $name = substr($images['ImageURL'], strrpos($images['ImageURL'], '/') + 1);
                Country::where('name_en',$images['Country'])->update(['flag' => $name]);

        }

        return 'Jobi done or what ever';
    }

    public  function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
