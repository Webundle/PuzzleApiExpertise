<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Service;
use Puzzle\Api\ExpertiseBundle\Entity\Staff;
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
class StaffController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['fullName', 'position', 'contacts', 'ranking', 'service', 'biography'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/staffs")
	 */
	public function getExpertiseStaffsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Staff::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/staffs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("staff", class="PuzzleApiExpertiseBundle:Staff")
	 */
	public function getExpertiseStaffAction(Request $request, Staff $staff) {
	    if ($staff->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $staff]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/staffs")
	 */
	public function postExpertiseStaffAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Staff $staff */
	    $staff = Utils::setter(new Staff(), $this->fields, $data);
	    
	    $em->persist($staff);
	    
	    if (isset($data['picture']) && $data['picture']) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Staff::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($staff){$staff->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $staff));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/staffs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("staff", class="PuzzleApiExpertiseBundle:Staff")
	 */
	public function putExpertiseStaffAction(Request $request, Staff $staff) {
	    $user = $this->getUser();
	    
	    if ($staff->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    if (isset($data['service']) && $data['service'] !== null) {
	        $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    }
	    
	    /** @var Staff $staff */
	    $staff = Utils::setter($staff, $this->fields, $data);
	    
	    if (isset($data['picture']) && $data['picture'] !== $staff->getPicture()) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Staff::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($staff){$staff->setPicture($filename);}
	        ]));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $staff));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/staffs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("staff", class="PuzzleApiExpertiseBundle:Staff")
	 */
	public function deleteExpertiseStaffAction(Request $request, Staff $staff) {
	    if ($staff->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($staff);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}