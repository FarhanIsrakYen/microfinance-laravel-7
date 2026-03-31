@extends('Layouts.erp_master_full_width')
@section('content')

<form enctype="multipart/form-data" method="post" id="filterFormId">
    @csrf
    @include('elements.report.report_filter_options', ['deliveryPlace' => true,
    'product' => true,
    'supplier' => true,
    'startDate' => true,
    'endDate' => true,
    ])
</form>

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Product Order Report', 'title_excel' =>
            'Product_Order_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th width="5%">Order No</th>
                                <th width="10%"> Order Date</th>
                                <th width="15%">Delivery Date</th>
                                <th width="15%">Delivery Place</th>
                                <th width="15%">Supplier</th>
                                <th width="15%">Product Name</th>
                                <th width="6%">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 0;
                                $TotalQnt = 0;
                                $ProdOrderArr = array();

                                foreach($ProdOrderData as $row){

                                    $TotalQnt += $row->product_quantity;
                                    $rSpam = count($DataSetNew[$row->order_no]);
                            ?>
                            <tr>
                                <?php
                                    if(!in_array($row->order_no, $ProdOrderArr)){
                                        $i++;
                                        array_push($ProdOrderArr, $row->order_no);
                                        ?>
                                <td rowspan="<?=$rSpam?>" class="text-center"><?=$i?></td>
                                <td rowspan="<?=$rSpam?>"><?= $row->order_no ?></td>
                                <td rowspan="<?=$rSpam?>"><?= $row->order_date ?></td>
                                <td rowspan="<?=$rSpam?>"><?= $row->delivery_date ?></td>
                                <td rowspan="<?=$rSpam?>">
                                    <?= (!empty($row->delivery_place))?$row->delivery_place."(".$row->branch_code.")" : ""?>
                                </td>
                                <?php
                                    }
                                    ?>
                                <td><?= $row->sup_name ?></td>
                                <td><?= $row->product_name. " (".$row->prod_barcode.")" ?></td>
                                <td class="text-center"><?= $row->product_quantity ?></td>
                            </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align:right!important;"><strong>Total</strong></td>
                                <td class="text-center"><strong><?=$TotalQnt?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @include('../elements.signature.signatureSet')
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
