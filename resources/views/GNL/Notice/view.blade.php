@extends('Layouts.erp_master')
@section('content')

<div class="row">
    <div class="col-lg-8 offset-lg-3">

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Title</label>
            <div class="col-lg-5">
                <div class="input-group">
                    <input type="text" class="form-control round" value="{{$noticeData->notice_title}}" readonly />
                </div>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Notice Period</label>
            <div class="col-lg-5">
                <div class="input-group">
                    <select class="form-control clsSelect2" id="notice_period" disabled>
                        <option value="1" {{ ($noticeData->notice_period == 1) ? 'selected' : '' }}>Infinity</option>
                        <option value="2" {{ ($noticeData->notice_period == 2) ? 'selected' : '' }}>Set Time</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:none;" id="periodDiv">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Start Time</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{$noticeData->start_time}}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">End Time</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{$noticeData->end_time}}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Notice</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <textarea type="text" class="form-control round" readonly>{{$noticeData->notice_body}}</textarea>
                </div>
            </div>
        </div>

        <div class="form-row form-group align-items-center">

            <label class="col-lg-3 input-title RequiredStar">Branch</label>

            <div class="col-lg-9 form-group">
                <?php
                            $selBranch = explode(',',$noticeData->branch_id);
                        ?>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="input-group checkbox-custom checkbox-primary">
                            <input type="checkbox" value="0" {{ (in_array(0, $selBranch) ) ? 'checked' : '' }} disabled>
                            <label for="branch_0"><strong>All Branch</strong></label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach($branchList as $branch)
                    <?php
                            $checkText = ( in_array($branch->id, $selBranch) ) ? 'checked' : '';
                        ?>
                    <div class="col-lg-4">
                        <div class="input-group checkbox-custom checkbox-primary">
                            <input type="checkbox" class="branch_cls" disabled {{ $checkText }}>
                            <label for="branch_{{ $branch->id }}">
                                {{ $branch->branch_name. " (".$branch->branch_code.")"}}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <div class="col-lg-2"></div>
            <div class="col-lg-5">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function($) {
    var notice_period = $('#notice_period').val();

    if (notice_period == 2) {
        $('#periodDiv').show('slow');
    } else {
        $('#periodDiv').hide('slow');
    }
});
</script>

@endsection