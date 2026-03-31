<?php

namespace App\Model\INV;

use App\BaseModel;

class Supplier extends BaseModel
{

    protected $table = 'inv_suppliers';
    protected $fillable = [
        'sup_name',
        'company_id',
        'supplier_type',
        'comission_percent',
        'sup_comp_name',
        'sup_email',
        'sup_email_notify',
        'sup_phone',
        'sup_addr',
        'sup_web_add',
        'sup_desc',
        'sup_ref_no',
        'sup_attentionA',
        'sup_attentionB',
        'sup_attentionC',
        'created_by',
		'updated_by'
    ];

    /* Here Insert Created By & Update By */
    public static function boot()
    {
        parent::boot();
    }

}
