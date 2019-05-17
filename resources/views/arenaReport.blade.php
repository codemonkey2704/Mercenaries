@extends ('layouts.main')
@section('content')

    <div class="col-md-12">
        <hr class="my-4">
        <div class="row text-center p-1">
            @if($won == true)
                <div class="container mx-auto bg-success p-2 w-100"><h1 class="text-white">You won</h1>
                    <div>
                        <p class="text-white">You Earned: {{$cash}} cash and {{$exp}} experience</p>
                    </div>
                </div>
            @else
                <div class="container-fluid mx-auto bg-danger p-2"><h1 class="text-white">You lost</h1></div>
            @endif
        </div>

        <div class="row">
            <div class="card col-md-4 mt-1 mb-1 h-100 justify-content-center mx-auto">
                <img class="card-img-top" src="{{asset('img/enemies/player.png')}}"/>
                <div class="card-body">
                    <div class="card-title">
                        <h4>{{$user->name}}</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <p>HP: </p>
                            <div class="progress">
                                <?php
                                /*Calculate percents for progressbar */
                                $per = $userHP / $user->calcMaxHealth() * 100;
                                ?>
                                <div class="progress-bar bg-danger text-dark" role="progressbar"
                                     style="width: {{$per}}%;"
                                     aria-valuenow="{{$userHP}}" aria-valuemin="0"
                                     aria-valuemax="{{$user->calcMaxHealth()}}">{{$userHP}}
                                    /{{$user->calcMaxHealth()}}</div>
                            </div>
                        </li>
                        <li class="list-group-item">Strength: {{$user->calcStat('strength')}}</li>
                        <li class="list-group-item">Accuracy: {{$user->calcStat('accuracy')}}</li>
                        <li class="list-group-item">Damage: {{$user->minDamage()}} - {{$user->maxDamage()}}</li>
                        <li class="list-group-item">Armor: {{$user->calcStat('defense')}}</li>
                    </ul>
                    <div class="card-body text-center">
                        <a class="btn btn-primary" data-toggle="collapse" href="#userList" role="button"
                           aria-expanded="false" aria-controls="collapseExample">Show/Hide</a>
                    </div>
                    <ul class="list-group collapse" id="userList">
                        @foreach($userHits as $uHit => $val)
                            <li class="list-group-item">{{$val}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-4 text-center mt-5 mx-auto">
                <h1>VS</h1>
            </div>
            <div class="card col-md-4 mt-1 mb-1 h-100 justify-content-center mx-auto">
                <img class="card-img-top" src="{{asset('img/enemies/player.png')}}">
                <div class="card-body">
                    <div class="card-title">
                        <h4>{{$enemy->name}}</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <p>HP: </p>
                            <div class="progress">
                                <?php
                                /*Calculate percents for progressbar */
                                $per = $enemyHP / $enemy->calcMaxHealth() * 100;
                                ?>
                                <div class="progress-bar bg-danger text-dark" role="progressbar"
                                     style="width: {{$per}}%;"
                                     aria-valuenow="{{$enemyHP}}" aria-valuemin="0"
                                     aria-valuemax="{{$enemy->getStat('currHp')}}">{{$enemyHP}}
                                    /{{$enemy->calcMaxHealth()}}</div>
                            </div>
                        </li>
                        <li class="list-group-item">Strength: {{$enemy->calcStat('strength')}}</li>
                        <li class="list-group-item">Accuracy: {{$enemy->calcStat('accuracy')}}</li>
                        <li class="list-group-item">Damage: {{$enemy->minDamage()}} - {{$enemy->maxDamage()}}</li>
                        <li class="list-group-item">Armor: {{$enemy->calcStat('defense')}}</li>
                    </ul>
                    <div class="card-body text-center">
                        <a class="btn btn-primary" data-toggle="collapse" href="#enemyList" role="button"
                           aria-expanded="false" aria-controls="collapseExample">Show/Hide</a>
                    </div>
                    <ul class="list-group collapse" id="enemyList">
                        @foreach($enemyHits as $eHit => $val)
                            <li class="list-group-item">{{$val}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>


        </div>

    </div>



@stop