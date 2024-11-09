@extends('layouts.student')

@section('title', 'Home')

@section('links')
<!-- Include any additional CSS or JS links if needed -->
@endsection

@section('content')
    <!-- Start col -->
    <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="card m-b-30">
            <div class="card-header bg-primary">
            <h5 class="card-title text-white">Course Survey</h5> <!-- Changed text-secondry to text-white -->
            </div>
            <div class="card-body">
                
                <p class="font-weight-bold">Course: <span class="text-primary">Introduction to Computer Science</span></p>
                <p class="font-weight-bold">Deadline: <span class="text-danger">November 30, 2024</span></p>
                <a href="#" class="btn btn-primary">Go to Survey</a>
            </div>
            
        </div>
    </div>
    @livewire('search-users')
@endsection

@section('scripts')
<!-- Include any additional JS if needed -->
@endsection
