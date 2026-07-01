<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->role->value === 'admin';
        $isSelf = $request->user()?->id === $this->id;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role->value,
        ];

        if ($isAdmin || $isSelf) {
            $data['email'] = $this->email;
            $data['email_verified_at'] = $this->email_verified_at;
            $data['created_at'] = $this->created_at;
        }

        return $data;
    }
}
