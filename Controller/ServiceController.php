<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Service;
use Puzzle\Api\MediaBundle\PuzzleApiMediaEvents;
use Puzzle\Api\MediaBundle\Event\FileEvent;
use Puzzle\Api\MediaBundle\Util\MediaUtil;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class ServiceController extends BaseFOSRestController
{
    public function __construct() {
        parent::__construct();
        $this->fields = ['name', 'description', 'ranking', 'classIcon', 'parent'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/services")
	 */
	public function getExpertiseServicesAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Service::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function getExpertiseServiceAction(Request $request, Service $service) {
	    if ($service->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $service));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/services")
	 */
	public function postExpertiseServiceAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get()->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['parent'] = isset($data['parent']) && $data['parent'] ? $em->getRepository(Service::class)->find($data['parent']) : null;
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Service $service */
	    $service = Utils::setter(new Service(), $this->fields, $data);
	    
	    $em->persist($service);
	    
	    /* Service picture listener */
	    if (isset($data['picture']) && $data['picture']) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Service::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($service){$service->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $service));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function putExpertiseServiceAction(Request $request, Service $service) {
	    $user = $this->getUser();
	    
	    if ($service->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    if (isset($data['parent']) && $data['parent'] !== null) {
	        $data['parent'] = $em->getRepository(Service::class)->find($data['parent']);
	    }
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Service $service */
	    $service = Utils::setter($service, $this->fields, $data);
	    
	    /* Article picture listener */
	    if (isset($data['picture']) && $data['picture'] !== $service->getPicture()) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Service::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($service){$service->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $service));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/services/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("service", class="PuzzleApiExpertiseBundle:Service")
	 */
	public function deleteExpertiseServiceAction(Request $request, Service $service) {
	    if ($service->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($service);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}