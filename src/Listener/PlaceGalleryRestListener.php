<?php
namespace Strapieno\NightClubCover\Api\Listener;

use ImgMan\Image\SrcAwareInterface;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Matryoshka\Model\Object\IdentityAwareInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use Strapieno\NightClub\Model\NightClubModelAwareInterface;
use Strapieno\NightClub\Model\NightClubModelAwareTrait;
use Strapieno\NightClubCover\Model\Entity\CoverAwareInterface;
use Strapieno\User\Model\Entity\UserInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\ResourceEvent;

/**
 * Class NightClubRestListener
 */
class PlaceGalleryRestListener implements ListenerAggregateInterface,
    ServiceLocatorAwareInterface,
    NightClubModelAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;
    use NightClubModelAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('update', [$this, 'onPostUpdate']);
        $this->listeners[] = $events->attach('delete', [$this, 'onPostDelete']);
    }

    /**
     * @param ResourceEvent $e
     * @return mixed
     */
    public function onPostUpdate(ResourceEvent $e)
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $id  = $e->getParam('id');
        $nightClub = $this->getNightClubFromId($id);

        $image = $e->getParam('image');

        if ($nightClub instanceof CoverAwareInterface && $nightClub instanceof ActiveRecordInterface) {

            $nightClub->setCover($this->getUrlFromImage($image, $serviceLocator));
            $nightClub->save();
            $e->setParam('nightClub', $nightClub);
        }

        return $image;
    }

    /**
     * @param ResourceEvent $e
     * @return bool
     */
    public function onPostDelete(ResourceEvent $e)
    {

        $id  = $e->getParam('id');
        $nightClub = $this->getNightClubFromId($id);

        if ($nightClub instanceof CoverAwareInterface && $nightClub instanceof ActiveRecordInterface) {

            $nightClub->setCover(null);
            $nightClub->save();
            $e->setParam('nightClub', $nightClub);
        }

        return true;
    }

    /**
     * @param $id
     * @return UserInterface|null
     */
    protected function getNightClubFromId($id)
    {
        return $this->getNightClubModelService()->find((new ActiveRecordCriteria())->setId($id))->current();

    }

    /**
     * @param IdentityAwareInterface $image
     * @param $serviceLocator
     * @return string
     */
    protected function getUrlFromImage(IdentityAwareInterface $image, ServiceLocatorInterface $serviceLocator)
    {
        $now = new \DateTime();
        if ($image instanceof SrcAwareInterface && $image->getSrc()) {

            return $image->getSrc(). '?lastUpdate=' . $now->getTimestamp();
        }

        /** @var $router RouteInterface */
        $router = $serviceLocator->get('Router');
        $url = $router->assemble(
            ['nightclub_id' => $image->getId()],
            ['name' => 'api-rest/nightclub/cover', 'force_canonical' => true]
        );

        return $url . '?lastUpdate=' . $now->getTimestamp();
    }
}