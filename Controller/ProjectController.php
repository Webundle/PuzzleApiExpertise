<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Project;
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
class ProjectController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['name', 'location', 'service', 'client', 'startedAt', 'endedAt', 'description'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/projects")
	 */
	public function getExpertiseProjectsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Project::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function getExpertiseProjectAction(Request $request, Project $project) {
	    if ($project->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $project));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/projects")
	 */
	public function postExpertiseProjectAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    $data['startedAt'] = is_string($data['startedAt']) ? new \DateTime($data['startedAt']) : $data['startedAt'];
	    $data['endedAt'] = is_string($data['endedAt']) ? new \DateTime($data['endedAt']) : $data['endedAt'];
	    
	    /** @var Project $project */
	    $project = Utils::setter(new Project(), $this->fields, $data);
	    
	    $em->persist($project);
	    
	    if (isset($data['picture']) && $data['picture']) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Project::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($project){$project->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $project));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function putExpertiseProjectAction(Request $request, Project $project) {
	    $user = $this->getUser();
	    
	    if ($project->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['startedAt'] = is_string($data['startedAt']) ? new \DateTime($data['startedAt']) : $data['startedAt'];
	    $data['endedAt'] = is_string($data['endedAt']) ? new \DateTime($data['endedAt']) : $data['endedAt'];
	    
	    if (isset($data['service']) && $data['service'] !== null) {
	        $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    }
	    
	    /** @var Project $project */
	    $project = Utils::setter($project, $this->fields, $data);
	    
	    if (isset($data['picture']) && $data['picture'] !== $project->getPicture()) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Project::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($project){$project->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $project));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function deleteExpertiseProjectAction(Request $request, Project $project) {
	    if ($project->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($project);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}