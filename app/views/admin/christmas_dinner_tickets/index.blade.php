@extends('layouts.admin')

@section('content')
  <div class="page-header">
    <h1>Christmas Dinner Tickets</h1>
    <a href="{{{ action('Admin\ChristmasDinnerTicketsController@getNew') }}}" class="btn btn-lg btn-primary">New Order</a>
  </div>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Quantity</th>
        <th>Date/Time</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($sales as $sale)
        <tr>
          <td>{{ $sale->id }}</td>
          <td>
            {{{ $sale->user->name }}}
            <small>
              (Username: {{{ $sale->user->username }}})
            </small>
          </td>
          <td>{{ $sale->quantity }}</td>
          <td>{{ $sale->created_at }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@stop