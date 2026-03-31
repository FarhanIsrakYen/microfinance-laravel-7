@extends('Layouts.erp_master_full_width')
@section('content')

<form enctype="multipart/form-data" method="post" id="filterFormId">
    @csrf
    @include('elements.report.report_filter_options', ['reqFrom' => true,
    'product' => true,
    'supplier' => true,
    'startDate' => true,
    'endDate' => true,
    'refresh' => true
    ])
</form>

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Requisition Report', 'title_excel' =>
            'Requisition_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="5%">Requisition Date</th>
                                <th width="10%">Requisition No</th>
                                <th width="10%">Requisition From</th>
                                <th width="15%">Supplier</th>
                                <th width="15%">Product Name</th>
                                <th width="5%">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $TotalQnt = 0;
                            $RequisitionArr = array();

                            $sl_no = '';
                            $requisition_date = '';
                            $requisition_no = '';
                            $branch_from = '';

                            foreach($requisitionData as $row){
                                $TotalQnt += $row->product_quantity;
                                $rSpam = count($DataSetNew[$row->requisition_no]);
                        ?>
                            <tr>
                                <?php
                                if(!in_array($row->requisition_no, $RequisitionArr)){
                                    $i++;
                                    array_push($RequisitionArr, $row->requisition_no);
                                    ?>
                                <td rowspan="<?=$rSpam?>" class="text-center"><?=$i?></td>
                                <td rowspan="<?=$rSpam?>"><?= (new DateTime($row->requisition_date))->format('d-m-Y') ?></td>
                                <td rowspan="<?=$rSpam?>"><?= $row->requisition_no ?></td>
                                <td rowspan="<?=$rSpam?>">
                                    <?= (!empty($row->branch_from))?$row->branch_from."(".$row->branch_code.")":"" ?>
                                </td>
                                <?php
                                }
                                else{
                                    ?>
                                <td style="display:none;" class="text-center"></td>
                                <td style="display:none;" class="text-center"></td>
                                <td style="display:none;" class="text-center"></td>
                                <td style="display:none;" class="text-left"></td>
                                <?php
                                }
                                ?>
                                <td class="text-left"><?= $row->sup_name ?></td>
                                <td class="text-left"><?= $row->product_name. " (".$row->prod_barcode.")" ?></td>
                                <td class="text-center"><?= $row->product_quantity ?></td>
                            </tr>
                            <?php
                            }
                        ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align:right!important;"><strong>Total</strong></td>
                                <td class="text-center"><strong><?=$TotalQnt?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Page -->

<script type="text/javascript">
    $(document).ready(function () {

        var branchId = "{{ isset($branchId) ? $branchId : '' }}";
        if (branchId != '') {
            $('#branch_id').val(branchId).attr("selected", "selected");
        }

        var productId = "{{ isset($productId) ? $productId : '' }}";
        if (productId != '') {
            $('#product_id').val(productId).attr("selected", "selected");
        }

        var supplierId = "{{ isset($supplierId) ? $supplierId : '' }}";
        if (supplierId != '') {
            $('#supplier_id').val(supplierId).attr("selected", "selected");
        }

        $('#reportBranch').html($('#branch_id').find("option:selected").text());

        var startDate = "{{ isset($startDate) ? $startDate : '' }}";
        if (startDate != '') {
            $('#start_date_txt').html(startDate);
            $('#start_date').val(startDate);

            setTimeout(function () {
                $(".wb-minus").trigger('click');
            }, 10);
        }

        var endDate = "{{ isset($endDate) ? $endDate : '' }}";
        if (endDate != '') {
            $('#end_date_txt').html(endDate);
            $('#end_date').val(endDate);
        }

        // // // Loader In-Active
        fnLoading(false);
    });

</script>
@endsection
