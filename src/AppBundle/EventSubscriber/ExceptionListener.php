<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Normalizer\NormalizerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    private $serializer;
    static $normalizers;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => [['processException', 255]]
        );
    }

    public function processException(GetResponseForExceptionEvent $event)
    {

        $result = null;
        foreach (ExceptionListener::$normalizers as $normalizer) {
            if($normalizer->supports($event->getException())){
                $result = $normalizer->normalize($event->getException());
                break;
            }
        }

        if (null == $result) {

            $result['code'] = Response::HTTP_BAD_REQUEST;

            $result['body'] = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $event->getException()->getMessage()
            ];
        }

        $body = $this->serializer->serialize($result['body'], 'json');


        $event->setResponse(new Response($body, $result['code']));
    }

    public static function addNormalizer(NormalizerInterface $normalizer)
    {
        ExceptionListener::$normalizers[] =$normalizer;
    }
}