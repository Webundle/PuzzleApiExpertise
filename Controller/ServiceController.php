<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\ExpertiseBundle\Entity\Service;
use Puzzle\Api\MediaBundle\PuzzleApiMediaEvents;
use Puzzle\Api\MediaBundle\Event\FileEvent;
use Puzzle\Api\MediaBundle\Util\MediaUtil;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\ErrorFactory;
use Puzzle\OAuthServerBundle\Service\Repository;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class ServiceController extends BaseFOSRestController
{
    /**
     * @param RegistryInterface         $doctrine
     * @param Repository                $repository
     * @param SerializerInterface       $serializer
     * @param EventDispatcherInterface  $dispatcher
     * @param ErrorFactory              $errorFactory
     */
    public function __construct(
        RegistryInterface $doctrine,
        Repository $repository,
        SerializerInterface $serializer,
        EventDispatcherInterface $dispatcher,
        ErrorFactory $errorFactory
    ){
        parent::__construct($doctrine, $repository, $serializer, $dispatcher, $errorFactory);
        $this->fields = ['name', 'description', 'ranking', 'classIcon', 'parent'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/services")
	 */
	public function getExpertiseServicesAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Service::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function getExpertiseServiceAction(Request $request, Service $service) {
	    if ($service->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $service]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/services")
	 */
	public function postExpertiseServiceAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Service::class)->find($data['parent']) : null;
	    
	    /** @var Service $service */
	    $service = Utils::setter(new Service(), $this->fields, $data);
	    
	    $em->persist($service);
	    $em->flush();
	    
	    /* Service picture listener */
	    if (isset($data['picture']) && $data['picture']){
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Service::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($service){$service->setPicture($filename);}
	        ]));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $service]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function putExpertiseServiceAction(Request $request, Service $service) {
	    $user = $this->getUser();
	    
	    if ($service->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    if (isset($data['parent']) && $data['parent'] !== null) {
	        $data['parent'] = $em->getRepository(Service::class)->find($data['parent']);
	    }
	    
	    /** @var Service $service */
	    $service = Utils::setter($service, $this->fields, $data);
	    
	    /* Article picture listener */
	    if (isset($data['picture']) && $data['picture'] !== $service->getPicture()) {
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Service::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($service){$service->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function deleteExpertiseServiceAction(Request $request, Service $service) {
	    if ($service->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $em = $this->doctrine->getManager($this->connection);
	    $em->remove($service);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}