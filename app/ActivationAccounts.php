<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivationAccounts extends Model {
    protected $table = 'activation_accounts';
    protected $guarded = ['id'];

    /**
     * Generate activation code
     *
     * @return void
     */
    public function generateCode() {
        $code = $this->id % 100 . rand(1, $this->school_id) . str_random(3);
        if ($this->where('code', $code)->exists()) {
            // Generate new code
            $this->generateCode();
        } else {
            $this->code = $code;
            $this->save();
        }
    }
}
