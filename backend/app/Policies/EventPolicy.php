<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksFamilyMembership;

class EventPolicy
{
    use ChecksFamilyMembership;
}
