@extends('layouts.application')

@section('content')
<div class="page-header">
    <h1>Dinner Seating &amp; Menu Planner</h1>
</div>
<div>
    <h2>Group #{{ $group->id }}</h2>
    @if ($group->owner_id == Auth::user()->id)
        <br>
        <span class="alert alert-info">This group belongs to you.</span>
        <br><br>
    @endif
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Menu Choices</h4></div>
        <div class="panel-body">
            <p>Please choose your menu options from the choices for each of your guests below:</p>
            <h5><strong>Starters</strong></h5>
            <ul>
                <li><strong>Tomato and Red Pepper Soup</strong>, served with basil oil and herb crouton (<strong>V</strong>)</li>
                <li><strong>Caprese Salad</strong>, served with basil pesto drizzled with aged balsamic (<strong>V</strong>)</li>
                <li><strong>Pork and Chicken Liver Pate</strong>, served with spiced fruit chutney and spring salad</li>
            </ul>
            <h5><strong>Mains</strong></h5>
            <ul>
                <li><strong>Grilled Breast of Chicken, Marinated in Lemon and Garlic</strong><br/>Served with sautéed potatoes, green beans and vichy carrots with creamy tarragon jus</li>
                <li><strong>Poached Pangasius Fish</strong><br/>Served with carrots, broccoli, saffron mash and chive cream sauce</li>
                <li><strong>Oven Baked Chicken Supreme</strong><br/>Served with sauté potatoes, green beans, carrot and mushroom cream sauce</li>
                <li><strong> Wild Mushroom and Asparagus Risotto</strong> (<strong>V</strong>)</li>
            </ul>
            <h5><strong>Desserts</strong></h5><ul>
                <li><strong>Caramelized Apple and Raisin Bread and Butter Pudding</strong>, served with custard sauce (<strong>V</strong>)</li>
                <li><strong>Lemon Meringue Pie</strong> (<strong>V</strong>)</li>
                <li><strong>Trio Chocolate Delight</strong>, served with berry coulis (<strong>V</strong>)</li>
            </ul>
        </div>
    </div>
    @if (!$group->members->count())
        <p><em>(Empty Group)</em></p>
    @else
        <table class='table'>
            <tr><th>#</th><th>Name</th><th>Menu Choice</th><th></th></tr>
        @foreach ($group->members as $i => $member)
            <tr>
            <td>{{$i + 1}}</td>
            <td>
            @if ($member->is_owner)
                <strong>
                @endif
                {{ $member->name }}
                @if ($member->is_owner)
                </strong>
            @endif
            </td><td>
            @if ($member->ticket_purchaser_id == Auth::user()->id)
              @include('dinner_groups.menu_choice', ['member' => $member])
            @endif
            </td><td>
            @if ($member->ticket_purchaser_id == Auth::user()->id && (!$member->is_owner || DinnerGroup::CAN_LEAVE_OWN_GRP))
                {{Form::open(['action' => ['DinnerGroupsController@removeMember']])}}
                <button type="submit" name="remove" value="{{$member->id}}"  class="btn btn-danger">Remove</button>
                {{Form::close()}}
            @endif
            </td>
            </tr>
        @endforeach
        </table>
    @endif
    <hr>
</div>
<p>
<?php $user = Auth::user(); $user->getUnclaimedDinnerTicketsCountAttribute(); ?>
@if ($user->unclaimed_dinner_tickets_count > 1 || ($user->dinnerGroupMember && $user->unclaimed_dinner_tickets_count == 1))
{{Form::open(['action' => ['DinnerGroupsController@addMember']])}}
<div class="input-group col-xs-4" style="padding-left: 0;">
    <span class="input-group-addon">Add a new guest:</span>
    <input type="text" size="10" class="form-control" name="new_guest" placeholder="Your guest's name">
    <input type="hidden" name="group" value="{{$group->id}}">
</div>
<button type="submit" class="btn btn-primary">Add guest</button>
{{Form::close()}}<br>
@endif
  @if (DinnerPermission::user(Auth::user())->canJoinGroup($group))
    {{ Form::model($group, array('route' => array('dashboard.dinner.groups.update', $group->id), 'method' => 'patch')) }}
      {{ Form::hidden('user_id', Auth::user()->id) }}
      {{ Form::submit('Join this Group', ['class' => 'btn btn-primary']) }}
    {{ Form::close() }}
    <br>
  @endif
  @if (DinnerPermission::user(Auth::user())->canLeaveGroup($group))
    {{ Form::model($group, array('route' => array('dashboard.dinner.groups.destroy', $group->id), 'method' => 'delete')) }}
      {{ Form::hidden('user_id', Auth::user()->id) }}
      {{ Form::submit('Leave this Group', ['class' => 'btn btn-danger']) }}
    {{ Form::close() }}
    <br>
  @endif
    <a href="{{ route('dashboard.dinner.groups.index') }}" class="btn btn-default">Go Back</a>
</p>
@stop
