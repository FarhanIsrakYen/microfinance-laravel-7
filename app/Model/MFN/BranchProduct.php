<?php

namespace App\Model\MFN;

use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    protected $table = 'mfn_branch_products';
    protected $primaryKey = 'branchId';
}
