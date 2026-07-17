<?php

namespace App\Support;

use RuntimeException;

/** iCal-Text ließ sich nicht parsen – Message ist nutzertauglich (deutsch). */
class IcsParseException extends RuntimeException {}
