<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ChangePassword extends Data
{
    public function __construct(
        #[Required]
        public string $old_password,

        #[Required, Min(8)]
        public string $new_password,
    ) {}
}