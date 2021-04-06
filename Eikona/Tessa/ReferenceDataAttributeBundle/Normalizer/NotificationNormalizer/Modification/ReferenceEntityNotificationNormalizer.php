<?php
/**
 * ReferenceEntityNotificationNormalizer.php
 *
 * @author      Timo Müller <t.mueller@eikona-media.de>
 * @copyright   2021 EIKONA Media (https://eikona-media.de)
 */

namespace Eikona\Tessa\ReferenceDataAttributeBundle\Normalizer\NotificationNormalizer\Modification;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Eikona\Tessa\ConnectorBundle\Normalizer\NotificationNormalizer\Modification\NotificationNormalizerInterface;
use Eikona\Tessa\ConnectorBundle\Tessa;

/**
 * Class ReferenceEntityNotificationNormalizer
 *
 * @package Eikona\Tessa\ReferenceDataAttributeBundle\Normalizer\NotificationNormalizer\Modification
 */
class ReferenceEntityNotificationNormalizer implements NotificationNormalizerInterface
{
    /**
     * @var FindRecordDetailsInterface
     */
    protected $findRecordDetailsQuery;

    /**
     * ReferenceEntityNotificationNormalizer constructor.
     *
     * @param FindRecordDetailsInterface $findRecordDetailsQuery
     */
    public function __construct(
        FindRecordDetailsInterface $findRecordDetailsQuery
    )
    {
        $this->findRecordDetailsQuery = $findRecordDetailsQuery;
    }

    /**
     * @param Record $entity
     *
     * @return array
     */
    public function normalize($entity): array
    {
        $entityDetails = $this->findRecordDetailsQuery->find(
            $entity->getReferenceEntityIdentifier(),
            $entity->getCode()
        );


        return [
            'id' => (string)$entity->getIdentifier(),
            'code' => (string)$entity->getIdentifier(),
            'type' => Tessa::TYPE_ENTITY_RECORD,
            'context' => Tessa::CONTEXT_UPDATE,
            'data' => $entityDetails->normalize()
        ];
    }

    public function supports($entity): bool
    {
        return $entity instanceof Record;
    }
}
