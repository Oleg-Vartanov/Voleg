<?php

namespace App\Core\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityFieldValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** @inheritDoc */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityField) {
            throw new UnexpectedTypeException($constraint, UniqueEntityField::class);
        }

        $entityRepository = $this->em->getRepository($constraint->entityClass);

        $searchResults = $entityRepository->findBy([
            $constraint->field => $value
        ]);

        if (count($searchResults) > 0) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
