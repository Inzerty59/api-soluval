<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Service\SiretVerificationService;

class ValidSiretValidator extends ConstraintValidator
{
    private $siretVerificationService;

    public function __construct(SiretVerificationService $siretVerificationService)
    {
        $this->siretVerificationService = $siretVerificationService;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->siretVerificationService->verifySiret($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ siret }}', $value)
                ->addViolation();
        }
    }
}