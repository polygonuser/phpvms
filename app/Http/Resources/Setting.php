<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Setting extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'group' => $this->group,
            'order' => $this->order,
            'description' => $this->description,
        ];
    }
}
