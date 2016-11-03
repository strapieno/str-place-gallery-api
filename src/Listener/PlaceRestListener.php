<?php
namespace Strapieno\PlaceGallery\Api\Listener;

use ImgMan\Image\SrcAwareInterface;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Matryoshka\Model\Object\IdentityAwareInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use Strapieno\NightClub\Model\NightClubModelAwareInterface;
use Strapieno\NightClub\Model\NightClubModelAwareTrait;
use Strapieno\NightClubCover\Model\Entity\CoverAwareInterface;
use Strapieno\Place\Model\PlaceModelAwareInterface;
use Strapieno\Place\Model\PlaceModelAwareTrait;
use Strapieno\User\Model\Entity\UserInterface;
use Strapieno\Utils\Model\Object\CollectionAwareInterface;
use Zend\EventManager\Event;
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
 * Class PlaceRestListener
 */
class PlaceRestListener implements ListenerAggregateInterface,
    ServiceLocatorAwareInterface,
    PlaceModelAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;
    use PlaceModelAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('create', [$this, 'onPostCreate']);
        $this->listeners[] = $events->attach('delete', [$this, 'onPostDelete']);
    }

    /**
     * @param Event $e
     * @return mixed
     */
    public function onPostCreate(Event $e)
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        /** @var $application \Zend\Mvc\Application */
        $application = $serviceLocator->get('Application');

        $placeId = $application->getMvcEvent()->getRouteMatch()->getParam('place_id');
        $place = $this->getPlaceModelService()->find((new ActiveRecordCriteria())->setId($placeId))->current();

        $image = $e->getParam('image');


        if ($place instanceof CollectionAwareInterface) {

            $reference = new GalleryReference();
            $reference->setId($image->getId());

            $media = new Media();
            $media->setEmbedUrl($this->getUrlFromImage($image, $serviceLocator));
            $media->setGalleryReference($reference);

            $medias = $place->getCollection();
            if (!$medias->has($media)) {
                $medias->append($media);
                $place->save();
            }
        }

        return $image;
    }

    /**
     * @param ResourceEvent $e
     * @return bool
     */
    public function onPostDelete(ResourceEvent $e)
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        /** @var $application \Zend\Mvc\Application */
        $application = $serviceLocator->get('Application');

        $placeId = $application->getMvcEvent()->getRouteMatch()->getParam('place_id');
        $place = $this->getPlaceModelService()->find((new ActiveRecordCriteria())->setId($placeId))->current();

        $image = $e->getParam('image');


        if ($place instanceof CollectionAwareInterface) {

            $reference = new GalleryReference();
            $reference->setId($image->getId());

            $media = new Media();
            $media->setGalleryReference($reference);

            $medias = $place->getCollection();
            if ($medias->remove($media)) {
                $place->save();
            }
        }

        return true;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        return $serviceLocator;
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
            ['place_id' => $image->getId()],
            ['name' => 'api-rest/place/gallery', 'force_canonical' => true]
        );

        return $url . '?lastUpdate=' . $now->getTimestamp();
    }
}