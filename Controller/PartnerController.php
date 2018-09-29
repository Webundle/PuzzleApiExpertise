<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Partner;
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
class PartnerController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['name', 'location'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/partners")
	 */
	public function getExpertisePartnersAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Partner::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/partners/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("partner", class="PuzzleApiExpertiseBundle:Partner")
	 */
	public function getExpertisePartnerAction(Request $request, Partner $partner) {
	    if ($partner->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $partner));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/partners")
	 */
	public function postExpertisePartnerAction(Request $request) {
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Partner $partner */
	    $partner = Utils::setter(new Partner(), $this->fields, $data);
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->persist($partner);
	    
	    if (isset($data['picture']) && $data['picture']) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Partner::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($partner){$partner->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $partner));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/partners/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("partner", class="PuzzleApiExpertiseBundle:Partner")
	 */
	public function putExpertisePartnerAction(Request $request, Partner $partner) {
	    $user = $this->getUser();
	    
	    if ($partner->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Partner $partner */
	    $partner = Utils::setter($partner, $this->fields, $data);
	    
	    if (isset($data['picture']) && $data['picture'] !== $partner->getPicture()) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Partner::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($partner){$partner->setPicture($filename);}
	        ]));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $partner));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/partners/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("partner", class="PuzzleApiExpertiseBundle:Partner")
	 */
	public function deleteExpertisePartnerAction(Request $request, Partner $partner) {
	    if ($partner->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($partner);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}