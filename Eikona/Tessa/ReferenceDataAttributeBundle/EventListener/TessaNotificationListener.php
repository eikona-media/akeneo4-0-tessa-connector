<?php
/**
 * TessaNotificationListener.php.php
 *
 * @author      Timo Müller <t.mueller@eikona-media.de>
 * @copyright   2021 EIKONA Media (https://eikona-media.de)
 */

namespace Eikona\Tessa\ReferenceDataAttributeBundle\EventListener;

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Eikona\Tessa\ConnectorBundle\Tessa;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;

class TessaNotificationListener
{
    protected const IGNORED_API_ROUTES_FOR_TESSA = [
    ];

    /** @var Tessa */
    protected $tessa;

    /** @var ReferenceEntityRepositoryInterface */
    protected $referenceEntityRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param Tessa                              $tessa
     * @param ReferenceEntityRepositoryInterface $referenceEntityRepository
     * @param UserContext                        $userContext
     * @param RequestStack                       $requestStack
     */
    public function __construct(
        Tessa $tessa,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        UserContext $userContext,
        RequestStack $requestStack
    )
    {
        $this->tessa = $tessa;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->userContext = $userContext;
        $this->requestStack = $requestStack;
    }

    public function onEntityRecordUpdated(RecordUpdatedEvent $event)
    {
        if (!$this->shouldNotify()) {
            return;
        }

        $record = $this->referenceEntityRepository->getByIdentifier($event->getReferenceEntityIdentifier());
        $this->tessa->notifySingleModification($record);
    }

    public function onEntityRecordDeleted(RecordDeletedEvent $event)
    {
        if (!$this->shouldNotify()) {
            return;
        }

        $this->tessa->notifySingleDeletion(
            (string)$event->getRecordIdentifier(),
            (string)$event->getRecordIdentifier(),
            Tessa::TYPE_ENTITY_RECORD
        );
    }

    public function onEntityRecordsDeleted(ReferenceEntityRecordsDeletedEvent $event)
    {
        if (!$this->shouldNotify()) {
            return;
        }

        $this->tessa->sendNotificationToTessa([
            'id' => (string)$event->getReferenceEntityIdentifier(),
            'code' => (string)$event->getReferenceEntityIdentifier(),
            'type' => Tessa::TYPE_ENTITY,
            'context' => Tessa::CONTEXT_DELETE_ALL_RECORDS,
            'resourceName' => Tessa::RESOURCE_NAME_ENTITY,
        ]);
    }

    /**
     * @return bool
     */
    protected function shouldNotify(): bool
    {
        if ($this->requestStack->getCurrentRequest() === null) {
            return true;
        }

        /** @var Route $route */
        $route = $this->requestStack->getCurrentRequest()->get('_route');
        if (!in_array($route, self::IGNORED_API_ROUTES_FOR_TESSA, true)) {
            return true;
        }

        $user = $this->userContext->getUser();
        if ($user === null) {
            return true;
        }

        $tessaUser = $this->tessa->getUserUsedByTessa();
        if ($user->getUsername() !== $tessaUser) {
            return true;
        }

        return false;
    }
}
