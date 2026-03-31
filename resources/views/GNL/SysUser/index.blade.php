@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\RoleService as Role;
?>
<div class="row table-responsive">
    <ul class="dropdown-role">
        <?php
            $i = 0;
            $role_data = array();
            foreach ($user_role as $row) {
                $i++;
                $role_data[] = array(
                    'id' => $row->id,
                    'name' => $row->role_name,
                    'is_active' => $row->is_active,
                    'child_role' => Role::childRoles($row->id, $GlobalRole),
                );
            }

            // echo Role::roleGetData(0, $role_data, $GlobalRole);
            echo Role::roleGetDataUser(0, $role_data, $GlobalRole);
    ?>
    </ul>
</div>

<style type="text/css">
    
    .dropdown-role>li {
        position: relative;
        -webkit-user-select: none;
        /* Chrome/Safari */
        -moz-user-select: none;
        /* Firefox */
        -ms-user-select: none;
        /* IE10+ */
        -o-user-select: none;
        user-select: none;
        cursor: pointer;
        list-style: none;
    }

    .dropdown-role .sub-menu {
        position: relative;
        display: none;
        margin-top: -1px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left-color: #fff;
        box-shadow: none;
        list-style: none;
    }

    .right-caret:after,
    .left-caret:after {
        content: "";
        border-bottom: 5px solid transparent;
        border-top: 5px solid transparent;
        display: inline-block;
        height: 0;
        vertical-align: middle;
        width: 0;
        margin-left: 5px;
    }

    .right-caret:after {
        border-left: 5px solid #ffaf46;
    }

    .left-caret:after {
        border-right: 5px solid #ffaf46;
    }

    .dropdown-role li a {
        padding: 3px 1px
    }

    .dropdown-role li a i.fa {
        padding: 0 3px;
    }

    .dropdown-role {
        display: block;
        width: 100%;
    }

    table {
        width: 100%;
    }

    table tr {
        border: 1px solid #f0b533;
    }

    table tr td {
        padding: 10px 10px;
    }

    .dropdown-role {
        position: relative;
    }

    .pagination>li {
        width:5%;
        text-align: center;
    }
</style>

<script type="text/javascript">
    $(function() {
        $('.dropdown-role .my-link').attr("href", "javascript:void(0);");

        $(".dropdown-role > li > a.trigger").on("click", function(e) {
            var current = $(this).next();
            var grandparent = $(this).parent().parent();
            if ($(this).hasClass('left-caret') || $(this).hasClass('right-caret'))
                $(this).toggleClass('right-caret left-caret');
            grandparent.find('.left-caret').not(this).toggleClass('right-caret left-caret');
            grandparent.find(".sub-menu:visible").not(current).hide();
            current.toggle();
            e.stopPropagation();
        });

        $(".dropdown-role > li > a:not(.trigger)").on("click", function() {
            var root = $(this).closest('.dropdown');
            root.find('.left-caret').toggleClass('right-caret left-caret');
            root.find('.sub-menu:visible').hide();
        });
    });

function fnDelete(RowID) {
    /**
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('gnl/sys_user/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}

$(document).ready(function() {
    $('.clsDataTable').dataTable( {
    "language": {
        "paginate": {
        "previous": "<",
        "next": ">",
        },
    },
    "columnDefs": [
        { "orderable": false, "targets": [1, 7] }
    ]
} );

} );
</script>
@endsection