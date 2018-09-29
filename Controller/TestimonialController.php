<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Testimonial;
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
class TestimonialController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['author', 'company', 'position', 'message'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/testimonials")
	 */
	public function getExpertiseTestimonialsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Testimonial::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/testimonials/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("testimonial", class="PuzzleApiExpertiseBundle:Testimonial")
	 */
	public function getExpertiseTestimonialAction(Request $request, Testimonial $testimonial) {
	    if ($testimonial->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $testimonial));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/testimonials")
	 */
	public function postExpertiseTestimonialAction(Request $request) {
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Testimonial $testimonial */
	    $testimonial = Utils::setter(new Testimonial(), $this->fields, $data);
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->persist($testimonial);
	    
	    if (isset($data['picture']) && $data['picture']) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Testimonial::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($testimonial){$testimonial->setPicture($filename);}
	        ]));
	    }
	    
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $testimonial));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/testimonials/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("testimonial", class="PuzzleApiExpertiseBundle:Testimonial")
	 */
	public function putExpertiseTestimonialAction(Request $request, Testimonial $testimonial) {
	    $user = $this->getUser();
	    
	    if ($testimonial->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Testimonial $testimonial */
	    $testimonial = Utils::setter($testimonial, $this->fields, $data);
	    
	    if (isset($data['picture']) && $data['picture'] !== $testimonial->getPicture()) {
	        /** @var Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(PuzzleApiMediaEvents::MEDIA_COPY_FILE, new FileEvent([
	            'path'     => $data['picture'],
	            'folder'   => $data['uploadDir'] ?? MediaUtil::extractFolderNameFromClass(Testimonial::class),
	            'user'     => $this->getUser(),
	            'closure'  => function($filename) use ($testimonial){$testimonial->setPicture($filename);}
	        ]));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $testimonial));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/testimonials/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("testimonial", class="PuzzleApiExpertiseBundle:Testimonial")
	 */
	public function deleteExpertiseTestimonialAction(Request $request, Testimonial $testimonial) {
	    if ($testimonial->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($testimonial);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}