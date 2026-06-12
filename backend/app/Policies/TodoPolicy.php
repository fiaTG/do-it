<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksFamilyMembership;

class TodoPolicy
{
    use ChecksFamilyMembership;
}
