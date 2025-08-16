<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class MenuPermission extends Model
{
    protected $table = 'menu_permission';
    protected $fillable = ['menu_id', 'permission'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
