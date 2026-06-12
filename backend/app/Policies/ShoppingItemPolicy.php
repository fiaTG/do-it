<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksFamilyMembership;

class ShoppingItemPolicy
{
    use ChecksFamilyMembership;
}
