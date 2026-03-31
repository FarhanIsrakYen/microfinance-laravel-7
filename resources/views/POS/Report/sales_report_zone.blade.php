@extends('Layouts.erp_master_full_width')
@section('content')
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off" id="filterFormId">
    @csrf
    @include('elements.report.report_filter_options', ['zone' => true,
    'startDate' => true,
    'endDate' => true,
    ])
</form>

<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Zone Wise Sales Report', 'title_excel' =>
            'Zone_Sales_Report'])

            <div class="row ExportDiv">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead>
                        <tr>
                            <th width="3%" rowspan="2">SL</th>
                            <th rowspan="2">Branch Name</th>
                            <th colspan="2">Number Of Customer</th>
                            <th colspan="2">Number Of Quantity</th>
                            <th colspan="2">Amount</th>
                            <th colspan="3">Total</th>
                        </tr>
                        <tr>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Cash</th>
                            <th>Credit</th>
                            <th>Customer</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <?php
                        $final_ttl_cash_cust = 0;
                        $final_ttl_credit_cust = 0;
                        $final_ttl_cash_qtn = 0;
                        $final_ttl_credit_qtn = 0;
                        $final_ttl_cash_amt = 0;
                        $final_ttl_credit_amt = 0;
                        $final_ttl_cust = 0;
                        $final_ttl_qtn = 0;
                        $final_ttl_amt = 0;
                    ?>

                    @if(!empty($DataSet))
                    @foreach($DataSet as $key => $row)
                    <?php
                                $i = 1;
                                $ttl_cash_cust = 0;
                                $ttl_credit_cust = 0;
                                $ttl_cash_qtn = 0;
                                $ttl_credit_qtn = 0;
                                $ttl_cash_amt = 0;
                                $ttl_credit_amt = 0;
                                $ttl_cust = 0;
                                $ttl_qtn = 0;
                                $ttl_amt = 0;
                            ?>
                    <tr>
                        <td colspan="11">
                            <h5>{{ $key }}</h5>
                        </td>
                    </tr>
                    @foreach($row as $data)
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td>
                            {{ $data['branch_name'] }}
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_cash_cust'] }}
                            <?php $ttl_cash_cust += $data['ttl_cash_cust'] ?>
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_credit_cust'] }}
                            <?php $ttl_credit_cust += $data['ttl_credit_cust'] ?>
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_cash_qtn'] }}
                            <?php $ttl_cash_qtn += $data['ttl_cash_qtn'] ?>
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_credit_qtn'] }}
                            <?php $ttl_credit_qtn += $data['ttl_credit_qtn'] ?>
                        </td>
                        <td class="text-right">
                            {{ $data['ttl_cash_amt'] }}
                            <?php $ttl_cash_amt += $data['ttl_cash_amt'] ?>
                        </td>
                        <td class="text-right">
                            {{ $data['ttl_credit_amt'] }}
                            <?php $ttl_credit_amt += $data['ttl_credit_amt'] ?>
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_cust'] }}
                            <?php $ttl_cust += $data['ttl_cust'] ?>
                        </td>
                        <td class="text-center">
                            {{ $data['ttl_qtn'] }}
                            <?php $ttl_qtn += $data['ttl_qtn'] ?>
                        </td>
                        <td class="text-right">
                            {{ $data['ttl_amt'] }}
                            <?php $ttl_amt += $data['ttl_amt'] ?>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right"><b>{{ $reqData['total'] }}</b></td>
                        <td id="ttl_cust_c" class="text-center text-dark font-weight-bold">
                            {{ $ttl_cash_cust }}
                            <?php $final_ttl_cash_cust += $ttl_cash_cust ?>
                        </td>
                        <td id="ttl_cust_cr" class="text-center text-dark font-weight-bold">
                            {{ $ttl_credit_cust }}
                            <?php $final_ttl_credit_cust += $ttl_credit_cust ?>
                        </td>

                        <td id="ttl_qtn_c" class="text-center text-dark font-weight-bold">
                            {{ $ttl_cash_qtn }}
                            <?php $final_ttl_cash_qtn += $ttl_cash_qtn ?>
                        </td>
                        <td id="ttl_qtn_cr" class="text-center text-dark font-weight-bold">
                            {{ $ttl_credit_qtn }}
                            <?php $final_ttl_credit_qtn += $ttl_credit_qtn ?>
                        </td>
                        <td id="ttl_amt_c" class="text-right text-dark font-weight-bold">
                            {{ $ttl_cash_amt }}
                            <?php $final_ttl_cash_amt += $ttl_cash_amt ?>
                        </td>
                        <td id="ttl_amt_cr" class="text-right text-dark font-weight-bold">
                            {{ $ttl_credit_amt }}
                            <?php $final_ttl_credit_amt += $ttl_credit_amt ?>
                        </td>
                        <td id="ttl_ttl_cust" class="text-center text-dark font-weight-bold">
                            {{ $ttl_cust }}
                            <?php $final_ttl_cust += $ttl_cust ?>
                        </td>
                        <td id="ttl_ttl_qtn" class="text-center text-dark font-weight-bold">
                            {{ $ttl_qtn }}
                            <?php $final_ttl_qtn += $ttl_qtn ?>
                        </td>
                        <td id="ttl_ttl_amt" class="text-right text-dark font-weight-bold">
                            {{ $ttl_amt }}
                            <?php $final_ttl_amt += $ttl_amt ?>
                        </td>
                    </tr>
                    </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right"><b>TOTAL</b></td>
                            <td id="ttl_cust_c" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cash_cust }}
                            </td>
                            <td id="ttl_cust_cr" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_credit_cust }}
                            </td>

                            <td id="ttl_qtn_c" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cash_qtn }}
                            </td>
                            <td id="ttl_qtn_cr" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_credit_qtn }}
                            </td>
                            <td id="ttl_amt_c" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_cash_amt }}
                            </td>
                            <td id="ttl_amt_cr" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_credit_amt }}
                            </td>
                            <td id="ttl_ttl_cust" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_cust }}
                            </td>
                            <td id="ttl_ttl_qtn" class="text-center text-dark font-weight-bold">
                                {{ $final_ttl_qtn }}
                            </td>
                            <td id="ttl_ttl_amt" class="text-right text-dark font-weight-bold">
                                {{ $final_ttl_amt }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                @include('../elements.signature.signatureSet')
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {

        var zoneId = "{{ isset($zoneId) ? $zoneId : '' }}";
        if (zone_id != '') {
            $('#zone_id').val(zoneId).attr("selected", "selected");
        }

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
