<?php

namespace AppBundle\Normalizer;
use AppBundle\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface
{
    protected $exceptionTypes;

    public function __construct(array $exceptionTypes)
    {
        $this->exceptionTypes = $exceptionTypes;
    }

    public function supports(\Exception $exception)
    {
        return in_array(get_class($exception), $this->exceptionTypes);
    }
}