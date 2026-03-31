@extends('Layouts.erp_master')
@section('title', 'HR Dashboard')
@section('content')
<style>
    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 44px;
    }
     .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
</style>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            HR Dashboard
        </div>
        <div class="links">
            <a href="{{ url('gnl') }}">General Configuration</a>
            <a href="{{ url('pos') }}">POS</a>
            <a href="{{ url('acc') }}">ACC</a>
            <a href="{{ url('hr') }}">HR & Payroll</a>
            <a href="{{ url('mf') }}">Micro Finance</a>
            <a href="{{ url('fam') }}">Fixed Asset Management</a>
            <a href="{{ url('inv') }}">Inventory</a>
            <a href="{{ url('proc') }}">Procurement</a>
        </div>
    </div>
</div>
@endsection
