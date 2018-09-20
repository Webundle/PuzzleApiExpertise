<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\ExpertiseBundle\Entity\Project;
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
class ProjectController extends BaseFOSRestController
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
        $this->fields = ['name', 'location', 'service', 'client', 'startedAt', 'endedAt', 'description'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/projects")
	 */
	public function getExpertiseProjectsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Project::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function getExpertiseProjectAction(Request $request, Project $project) {
	    if ($project->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $project]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/projects")
	 */
	public function postExpertiseProjectAction(Request $request) {
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    $data['startedAt'] = is_string($data['startedAt']) ? new \DateTime($data['startedAt']) : $data['startedAt'];
	    $data['endedAt'] = is_string($data['endedAt']) ? new \DateTime($data['endedAt']) : $data['endedAt'];
	    
	    /** @var Project $project */
	    $project = Utils::setter(new Project(), $this->fields, $data);
	    
	    $em->persist($project);
	    $em->flush();
	    
	    if (isset($data['picture']) && $data['picture']){
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Project::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($project){$project->setPicture($filename);}
	        ]));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $project]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function putExpertiseProjectAction(Request $request, Project $project) {
	    $user = $this->getUser();
	    
	    if ($project->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    
	    $data = $request->request->all();
	    $data['startedAt'] = is_string($data['startedAt']) ? new \DateTime($data['startedAt']) : $data['startedAt'];
	    $data['endedAt'] = is_string($data['endedAt']) ? new \DateTime($data['endedAt']) : $data['endedAt'];
	    
	    if (isset($data['service']) && $data['service'] !== null) {
	        $data['service'] = $em->getRepository(Service::class)->find($data['service']);
	    }
	    
	    /** @var Project $project */
	    $project = Utils::setter($project, $this->fields, $data);
	    
	    if (isset($data['picture']) && $data['picture'] !== $project->getPicture()) {
	        $this->dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Project::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($project){$project->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/projects/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("project", class="PuzzleApiExpertiseBundle:Project")
	 */
	public function deleteExpertiseProjectAction(Request $request, Project $project) {
	    if ($project->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $em = $this->doctrine->getManager($this->connection);
	    $em->remove($project);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}