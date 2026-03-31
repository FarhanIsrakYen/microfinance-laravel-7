@extends('Layouts.erp_master')

@section('content')

@if ($isContentSet)
<style>
    .page-header-actions{
        display: none;
    }
</style>
@endif

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped">
        <thead>
            <tr>
                <th style="width: 5%;"class="text-center">SL</th>
                <th>Designations</th>
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <?php  
                    $designations = array();
                    foreach ($fieldOfficers as $row) {
                        $designations[] = $row->name;
                    }
                    echo implode(', ',$designations);
                    ?>
                </td>
                <td class="text-center">
                    <a href="{{ url('mfn/fieldofficer/edit/5') }}" title="Edit">
                        <i class="icon wb-edit mr-2 blue-grey-600"></i>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
