@extends('layouts.application')

<?php $page_title = $user->name; ?>

@section('content')
  <div class="page-header">
    <h1>
      {{{ $user->name }}}
      <small>{{{ $user->email }}}</small>
    </h1>
  </div>
  <div class="row">
    <div class="col-lg-8">
      <pre>{{{ $user->extras }}}</pre>
    </div>
    <div class="col-lg-4">
    @if ( DinnerPermission::user(Auth::user())->canManageGroups())
      <a href="{{{ route('dashboard.dinner.groups.index') }}}" class="btn btn-success btn-lg btn-block">
        <span class="glyphicon glyphicon-glass"></span>
        Dinner Seating Preferences
      </a>
    @endif
      <a href="{{{ action('LockersController@getIndex') }}}" class="btn btn-info btn-lg btn-block">
        <span class="glyphicon glyphicon-tower"></span>
        Rent a Locker
      </a>
      <a href="{{{ route('dashboard.books.index') }}}" class="btn btn-info btn-lg btn-block">
        <span class="glyphicon glyphicon-book"></span>
        Buy/Sell Books
      </a>
    </div>
  </div>
@stop
