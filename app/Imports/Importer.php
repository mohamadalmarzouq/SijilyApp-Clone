<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Models\Inventory;
use App\Models\Module;
use App\Models\Status;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Validation\RulesInventory as Rules;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithValidation;

class Importer implements ToModel, WithHeadingRow, WithValidation
{

    /**
     * @param Collection $collection
     */
    public function __construct($user_id, $status_id, $rules)
    {
        $this->rules = $rules;
        $this->user_id = $user_id;
        $this->status_id = $status_id;

    }

    public function model(array $row)
    {
        global $data_to_insert;

        !$this->status_id ?: $row['status_id'] = $this->status_id;

        $row['user_id'] = $this->user_id;

        $row['created_at'] = date('Y-m-d H:i:s');

        $row['updated_at'] = date('Y-m-d H:i:s');

        $data_to_insert[] = $row;

    }

    public function rules(): array
    {
        return $this->rules;
    }
}
