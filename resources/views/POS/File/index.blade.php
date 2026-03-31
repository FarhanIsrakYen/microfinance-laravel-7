@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
?>
<!-- Page -->
<div class="row">
    <div class="col-lg-12">

        <table class="table w-full table-hover table-bordered table-striped clsDataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th style="width:5%;">SL</th>
                    <th>File Name</th>
                    <th>File Size(Mb)</th>
                    <!-- <th>File Type</th> -->
                    <th style="width:15%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; 
                
                $isDownload = array_search(3, array_column($GlobalRole, 'set_status'));

                if($isDownload != false){
                    unset($GlobalRole[$isDownload]);
                }
                ?>
                @foreach ($FileData as $Row)
                <tr>
                    <td scope="row"> {{++$i}}</td>
                    <td>
                        <?php
                        // dd($Row->file_url);
                        if($isDownload){
                            ?>
                            <a href="{{ asset($Row->file_url) }}" download> {{ $Row->file_name}}</a>
                            <?php
                        }
                        else{
                            echo $Row->file_name;
                        }
                        ?>
                    </td>
                    <td class="text-center">{{ number_format($Row->file_size / (1024 * 1024), 2) }}</td>
                    <!-- <td>{{$Row->file_type}}</td> -->

                    <td class="text-center">
                        <!-- if want to deactive action ['edit','view', 'delete', 'destroy'] -->
                        {!! Role::roleWisePermission($GlobalRole, $Row->id) !!}

                        <?php
                        if($isDownload){
                            ?>
                            <a href="{{ asset($Row->file_url) }}" download>
                                <i class="fa fa-download" aria-hidden="true"></i>
                            </a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- End Page -->

<script>
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
        "{{url('pos/file_management/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID
    );
}
</script>

@endsection