<?php

namespace App\Imports;

use App\Dummy;
use Maatwebsite\Excel\Concerns\ToModel;

class DummyImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Dummy([
            'name'     => $row[0],
            'email'    => $row[1], 
        ]);
    }
}
