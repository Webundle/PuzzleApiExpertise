<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use Puzzle\Api\ExpertiseBundle\Entity\Faq;
use Puzzle\OAuthServerBundle\Controller\BaseFOSRestController;
use Puzzle\OAuthServerBundle\Service\Utils;
use Puzzle\OAuthServerBundle\Util\FormatUtil;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 * 
 */
class FaqController extends BaseFOSRestController
{
    public function __construct(){
        parent::__construct();
        $this->fields = ['question', 'answer'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/faqs")
	 */
	public function getExpertiseFaqsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    
	    /** @var Puzzle\OAuthServerBundle\Service\Repository $repository */
	    $repository = $this->get('papis.repository');
	    $response = $repository->filter($query, Faq::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/faqs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function getExpertiseFaqAction(Request $request, Faq $faq) {
	    if ($faq->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, $faq));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/faqs")
	 */
	public function postExpertiseFaqAction(Request $request) {
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Faq $faq */
	    $faq = Utils::setter(new Faq(), $this->fields, $data);
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->persist($faq);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $faq));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/faqs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function putExpertiseFaqAction(Request $request, Faq $faq) {
	    $user = $this->getUser();
	    
	    if ($faq->getCreatedBy()->getId() !== $user->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    
	    /** @var Puzzle\Api\ExpertiseBundle\Entity\Faq $faq */
	    $faq = Utils::setter($faq, $this->fields, $data);
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, $faq));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/faqs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function deleteExpertiseFaqAction(Request $request, Faq $faq) {
	    if ($faq->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        /** @var Puzzle\OAuthServerBundle\Service\ErrorFactory $errorFactory */
	        $errorFactory = $this->get('papis.error_factory');
	        return $this->handleView($errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->get('doctrine')->getManager($this->connection);
	    $em->remove($faq);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, null, 204));
	}
}