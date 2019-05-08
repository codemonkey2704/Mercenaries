<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'action_points', 'last_energy_update'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationships for User
     *
     */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function stats()
    {
        return $this->belongsToMany(Stat::class)->withPivot('value');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function isAdmin()
    {
        if ($this->role->name == 'admin')
            return true;
        return false;
    }

    public function calcEnergy()
    {

        $last = Carbon::parse($this->last_energy_update);
        $diff = $last->diffInMinutes(Carbon::now());
        $energy = $this->action_points;
        floor($diff);
        if ($diff >= 10) {
            for ($i = $diff / 10; $i > 0; $i--) {

                if ($energy < 100)
                    $energy++;

            }
            $this->action_points = $energy;
            $this->last_energy_update = Carbon::now()->toDateTimeString();
            $this->save();
        }
    }

    public function getStat($stat)
    {
        return $this->stats->where('name', $stat)->first()->pivot->value;
    }

    public function calcStat($stat)
    {

        $val = $this->getStat($stat);
        if ($this->hasEquipped('weapon'))
            if ($this->getEquipped('weapon')->stats->where('name', $stat)->first()) {
                $val += $this->getEquipped('weapon')->stats->where('name', $stat)->first()->pivot->value;
            }

        if ($this->hasEquipped('armor'))
            if ($this->getEquipped('armor')->stats->where('name', $stat)->first()) {
                $val += $this->getEquipped('armor')->stats->where('name', $stat)->first()->pivot->value;
            }


        return $val;
    }

    public function setStat($stat, $val)
    {
        return $this->stats()->updateExistingPivot($this->stats->where('name', $stat)->first()->id, ['value' =>$val]);
    }

    public function calcItemBonus($stat)
    {
        $val = 0;
        if ($this->hasEquipped('weapon'))
            if ($this->getEquipped('weapon')->stats->where('name', $stat)->first()) {
                $val += $this->getEquipped('weapon')->stats->where('name', $stat)->first()->pivot->value;
            }

        if ($this->hasEquipped('armor'))
            if ($this->getEquipped('armor')->stats->where('name', $stat)->first()) {
                $val += $this->getEquipped('armor')->stats->where('name', $stat)->first()->pivot->value;
            }


        return $val;
    }


    public function MinDamage()
    {
        return $this->calcItemBonus('damage') - 0.1 * $this->calcItemBonus('damage');
    }

    public function MaxDamage()
    {
        return $this->calcItemBonus('damage') + 0.1 * $this->calcItemBonus('damage');
    }

    public function checkLvlUp()
    {
        if($this->expToNext()!= 'max')
        if($this->getStat('experience')>=$this->expToNext())
            $this->lvlUp();
    }

    public function lvlUp()
    {
        $this->setStat('experience', $this->getStat('experience')-$this->expToNext());
        $this->setStat('level', $this->getStat('level')+1);
    }

    public function expToNext()
    {
        if(!ExpToNext::find($this->getStat('level')+1))
            return 'max';
        else
             return ExpToNext::find($this->getStat('level')+1)->experience;
    }

    public function calcMaxHealth()
    {
        $max = 100+10*$this->getStat('level');
        $i = $this->calcStat('strength');

        for ($i; $i > 1; $i--) {
            $max = $max + $max * 0.1;
        }

        $max = round($max += $this->calcItemBonus('health'));
        if ($this->stats->find(4)->pivot->value > $max)
            $this->stats()->updateExistingPivot(4, ['value' => $max]);
        return $max;
    }


    public function calcTimeForJS()
    {
        $last = Carbon::parse($this->last_energy_update);
        $last->addMinutes(10);
        if ($this->action_points < 100)
            return $last;
        return false;
    }

    public function calcTrainingCost($stat)
    {
        switch ($stat) {
            case 1:
                return round($this->stats->find($stat)->pivot->value * 100);
                break;

            case 2:
                return round($this->stats->find($stat)->pivot->value * 100);
                break;

            case 3:
                $val = $this->stats->find($stat)->pivot->value;
                return round($val * 100 + (15 / 100 * ($val + 50)));
                break;

            default:

                echo "Error";
        }

    }

    /*--------------------------
     *ITEM RELATED FUNCTIONS
     *-------------------------- */

    public function hasEquipped($type)
    {
        if ($this->stats->where('name', $type)->first()->pivot->value != 0) {
            return true;
        }
        return false;
    }

    public function getEquipped($type)
    {
        return Item::find($this->stats->where('name', $type)->first()->pivot->value);
    }

    public function getAllItems()
    {
        $list = $this->items;
        foreach ($list as $item) {
            if ($item->id == $this->stats->where('name', 'armor')->first()->pivot->value || $item->id == $this->stats->where('name', 'weapon')->first()->pivot->value) {
                $list = $list->reject($item);
            }

        }
        return $list;
    }

    public function unEquip($item)
    {
        $id = $this->stats->where('name', $item->base_item->type)->first()->id;
        $this->stats()->updateExistingPivot($id, ['value' => 0]);
        return true;
    }

    public function equip($item)
    {
            $id = $this->stats->where('name', $item->base_item->type)->first()->id;
            $this->stats()->updateExistingPivot($id, ['value' => $item->id]);
            return true;
    }

}


