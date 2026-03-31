@extends('Layouts.erp_master')
@section('title', 'Modules')
@section('content')

<div class="page app-projects">
    <div class="page-header pt-4">
        <h1 class="page-title">Modules</h1>
        <div class="page-header-actions">
            @foreach($SysModules as $modules)
            <?php $modules = (object) $modules; ?>
            @if($modules->id == 1)
            <!-- <a href="{{ url('/'.$modules->short_name) }}"> -->
            <a href="javascript:void(0)"
                onclick="setModuleID('{{$modules->id}}','{{ url($modules->module_link) }}','{{ $modules->module_link }}');">
                <button type="button" class="btn btn-tagged btn-lg btn-dark animation-scale">
                    <span class="btn-tag">
                        <i class="icon wb-settings" aria-hidden="true"></i>
                    </span>
                    {{ $modules->name }}
                </button>
            </a>
            @endif
            @endforeach
        </div>
    </div>

    <div class="page-content mt-4">
      <div class="row">
        @foreach($SysModules as $modules)
        <?php $modules = (object) $modules; ?>
        @if($modules->id != 1)
        
            <div class="col-md-4 col-xl-4 col-lg-4">
                <a href="javascript:void(0)"
                    onclick="setModuleID('{{$modules->id}}','{{ url($modules->module_link) }}','{{ $modules->module_link }}');">
                    <div class="card order-card shadow border-radius-2">
                        <div class="card-block">
                            <h4 class="m-b-20 text-white">{{ $modules->name }}</h4>
                            <div class="dashCard">
                                <i class="fa {{ (!empty($modules->icon)) ? $modules->icon : 'fa-cart-arrow-down' }} font text-white"
                                    style="font-size: 80px"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        
        @endif
        @endforeach
        </div>
    </div>

</div>
<style type="text/css">
.dashCard {
    position: absolute;
    right: 6%;
    top: 50%;
    transform: translateY(-50%);
    width: 97px;
    height: 97px;
    border-radius: 2px;
    display: -webkit-flex;
    display: flex;
    -webkit-align-items: center;
    align-items: center;
    -webkit-justify-content: center;
    justify-content: center;
    border-radius: 2rem;
    background: transparent;
}

.order-card {
    height: 110px;
    background: #99b898;
}

.border-radius-2 {
    border-radius: 2rem;
    border-left: 10px solid #474747;
}

.order-card:hover .dashCard {
    border-radius: 2rem;
    background: transparent;
}

.order-card {
    height: 110px;
    background: #99b898;
}

.border-radius-2 {
    border-radius: 2rem;
    border-left: 10px solid #ca6f3891;
}
</style>

<script type="text/javascript">
function setModuleID(id, mname, module_link) {

    $.ajax({
        method: "GET",
        url: "{{url('/modules/ajaxModuleID')}}",
        dataType: "json",
        data: {
            ModuleID: id,
            ModuleLink: module_link
        },
        success: function(data) {
            if (data === 1) {
                window.location.href = mname;
            }
        }
    });
}
</script>
@endsection