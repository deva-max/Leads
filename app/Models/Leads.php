<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Leads extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];   

    protected $fillable = [];

    protected $casts = ['status' => 'string'];

    public function getTableColumns(){
        $columns = Schema::getColumnListing($this->getTable());
        return array_diff($columns, $this->guarded);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = $this->getTableColumns() ?: [];
    }

    
}
