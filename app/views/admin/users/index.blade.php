@extends('layouts.admin')

@section('content')
  <div class="row">
    <div class="col-lg-12">
      <div class="page-header">
        <div class="pull-right btn-group">
          <a href="#" class="btn btn-default btn-info btn-large">
            <span class="glyphicon glyphicon-download-alt"></span>
            Synchronize with eActivities
          </a>
        </div>
        <h1>Users</h1>
      </div>
      <ul class="nav nav-pills">
        <li
          @if ( ! in_array(Input::get('filter'), array('admins', 'non-admins', 'members', 'non-members')))
            class="active"
          @endif
        >
          <a href="{{ URL::route('admin.users.index') }}">
            <span class="badge pull-right">{{ $everybody_count }}</span>
            Everybody
          </a>
        </li>
        <li {{ (Input::get('filter') === 'admins') ? 'class="active"' : '' }}>
          <a href="{{ URL::route('admin.users.index', array('filter' => 'admins')) }}">
            <span class="badge pull-right">{{ $admins_count }}</span>
            Admins
          </a>
        </li>
        <li {{ (Input::get('filter') === 'non-admins') ? 'class="active"' : '' }}>
          <a href="{{ URL::route('admin.users.index', array('filter' => 'non-admins')) }}">
            <span class="badge pull-right">{{ $non_admins_count }}</span>
            Non-Admins
          </a>
        </li>
        <li {{ (Input::get('filter') === 'members') ? 'class="active"' : '' }}>
          <a href="{{ URL::route('admin.users.index', array('filter' => 'members')) }}">
            Members
          </a>
        </li>
        <li {{ (Input::get('filter') === 'non-members') ? 'class="active"' : '' }}>
          <a href="{{ URL::route('admin.users.index', array('filter' => 'non-members')) }}">
            Non-Members
          </a>
        </li>
      </ul>
      <hr>
      <table class="table table-striped table-hover users-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Name</th>
            <th class="text-center">Signed In At Least Once?</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        @foreach($users as $user)
          <tr>
            <td>{{ $user->id }}</td>
            <td>
              {{{ $user->username }}}
              @if ($user->isAdmin())
                <span class="label">Admin</span>
              @endif
            </td>
            <td>
              {{{ $user->name }}}
            </td>
            <td class="text-center">
              @if ($user->last_sign_in_at === null)
                <span class="glyphicon glyphicon-remove text-danger"></span>
              @else
                <span class="glyphicon glyphicon-ok text-success"></span>
              @endif
            </td>
            <td class="text-right">
              @if ($user->id === Auth::user()->id)
                It's me :-)
              @else
                <div class="btn-toolbar">
                  @if ($user->isAdmin())
                    {{ Form::open(array('action' => array('AdminUsersController@putDemote', $user->username), 'method' => 'put')) }}
                      <button type="submit" class="btn btn-danger btn-mini">
                        <span class="glyphicon glyphicon-certificate"></span>
                        Demote from Admin
                      </button>
                    {{ Form::close() }}
                  @else
                    {{ Form::open(array('action' => array('AdminUsersController@putPromote', $user->username), 'method' => 'put')) }}
                      <button type="submit" class="btn btn-warning btn-mini">
                        <span class="glyphicon glyphicon-certificate"></span>
                        Promote to Admin
                      </button>
                    {{ Form::close() }}
                  @endif
                  <a href="mailto:{{{ $user->email }}}" class="btn btn-primary btn-mini">
                    <span class="glyphicon glyphicon-envelope"></span>
                    Email
                  </a>
                </div>
              @endif
            </td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>
@stop