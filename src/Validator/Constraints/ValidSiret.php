<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidSiret extends Constraint
{
    public $message = 'Le numéro SIRET "{{ siret }}" n\'est pas valide ou l\'entreprise n\'existe pas.';
}