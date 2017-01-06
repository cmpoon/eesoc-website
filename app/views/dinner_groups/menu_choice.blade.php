<?php
    $courses = [
        "starter",
        "main",
        "dessert"
    ];

    $choices = [
      "starter" => [
          1 => "Tomato and Red Pepper Soup (V)",
          2 => "Caprese Salad (V)",
          3 => "Pork and Chicken Liver Pate"
      ], "main" => [
          1 => "Grilled Breast of Chicken",
          2 => "Poached Pangasius Fish",
          3 => "Oven Baked Chicken Supreme",
          4 => "Vegetarian Choice (V)"
      ],"dessert" => [
          1 => "Caramelized Apple and Raisin Bread and Butter Pudding",
          2 => "Lemon Meringue Pie",
          3 => "Trio Chocolate Delight"
      ]
    ];

        $name = ( $member->name == Auth::user()->name ? "Yourself" : $member->name);

/**
    $cls   = function($vegetarian) use ($state) { return $vegetarian == $state ? 'btn-info' : 'btn-default';};
    $menu  = function($vegetarian) use ($course)
    {
        switch ($course)
        {
        case 'starter':
            return $vegetarian ? 'Wild Mushroom' : 'Haddock';

        default:
        case 'main':
            return $vegetarian ? 'Vegetarian' : 'Meat';
        }
    }; **/

?><h3>Menu Choices for {{ $name }}</h3>
<div class="form-horizontal">

    {{ Form::open(['action' => ['DinnerGroupsController@updateMenuChoice']]) }}
<?php

    foreach ($courses as $course){

        $vName = "choice_$course";
        $state = $member->$vName;
        ?>
    <div class="form-group"><label for="{{ $vName }}" class="col-sm-2 control-label">{{ ucfirst($course) }}</label>
        <?php
        if ($state == 0){
            $courseChoices = array_merge(array(0 => "Select ".ucfirst($course)), $choices[$course]);
        }else{
            $courseChoices = $choices[$course];
        }
        echo '<div class="col-sm-10">';
        echo str_replace("value=\"0\"","value=\"0\" disabled selected hidden",Form::select($vName, $courseChoices , $state));
        echo "</div></div>";

    }

    /**

    <button type="submit" name="choice" value="meat" class="btn {{ $cls(false) }}">{{ $menu(false) }}</button>
    <button type="submit" name="choice" value="vegetarian" class="btn {{ $cls(true) }}">{{ $menu(true) }}</button>
    {{ Form::hidden('course', $course) }}

     **/
    ?>

        <div class="form-group">
            <label for="special_req" class="col-sm-2 control-label">Dietary Requirements</label><div class="col-sm-10">
                <p class="help-block">If you have any allergies or preferences, specify these below. If the above choices are unsuitable for your dietary requirements, we'll get in touch with you to confirm your menu. If you don't have any, please leave blank.</p>
            <input type="text" name="special_req" id="special_req" value="{{ $member->special_req }}" maxlength="140" style="width: 90%"/></div>

        </div>


    <button type="submit" class="btn btn-success">Update Choices for {{ $name }}</button>

    {{ Form::hidden('member', $member->id) }}
    {{ Form::close() }}
</div>
</div>