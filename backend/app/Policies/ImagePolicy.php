<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksFamilyMembership;

class ImagePolicy
{
    use ChecksFamilyMembership;
}
