<?php

namespace App\Message\Trait;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Stamp\HandledStamp;

trait EnvelopeExtractorTrait
{
    /**
     * Copy from Symfony\Component\Messenger\HandleTrait::handle.
     */
    protected function extractEnvelopeContent(Envelope $envelope)
    {
        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $envelope->all(HandledStamp::class);

        if (! $handledStamps) {
            throw new LogicException(sprintf(
                'Message of type "%s" was handled zero times. Exactly one handler is expected when using "%s::%s()".',
                \get_class($envelope->getMessage()),
                static::class,
                __FUNCTION__
            ));
        }

        if (\count($handledStamps) > 1) {
            $handlers = implode(', ', array_map(function (HandledStamp $stamp): string {
                return sprintf('"%s"', $stamp->getHandlerName());
            }, $handledStamps));

            throw new LogicException(sprintf(
                'Message of type "%s" was handled multiple times. Only one handler is expected when using "%s::%s()", got %d: %s.',
                \get_class($envelope->getMessage()),
                static::class,
                __FUNCTION__,
                \count($handledStamps),
                $handlers
            ));
        }

        return $handledStamps[0]->getResult();
    }
}
