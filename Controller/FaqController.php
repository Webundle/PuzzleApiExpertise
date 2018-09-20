<?php

namespace Puzzle\Api\ExpertiseBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Puzzle\Api\ExpertiseBundle\Entity\Faq;
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
class FaqController extends BaseFOSRestController
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
        $this->fields = ['question', 'answer'];
    }
    
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/faqs")
	 */
	public function getExpertiseFaqsAction(Request $request) {
	    $query = Utils::blameRequestQuery($request->query, $this->getUser());
	    $response = $this->repository->filter($query, Faq::class, $this->connection);
	    
	    return $this->handleView(FormatUtil::formatView($request, $response));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Get("/faqs/{id}")
	 * @ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function getExpertiseFaqAction(Request $request, Faq $faq) {
	    if ($faq->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->accessDenied($request));
	    }
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $faq]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Post("/faqs")
	 */
	public function postExpertiseFaqAction(Request $request) {
	    $data = $request->request->all();
	    /** @var Faq $faq */
	    $faq = Utils::setter(new Faq(), $this->fields, $data);
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    $em->persist($faq);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['resources' => $faq]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Put("/faqs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function putExpertiseFaqAction(Request $request, Faq $faq) {
	    $user = $this->getUser();
	    
	    if ($faq->getCreatedBy()->getId() !== $user->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    $data = $request->request->all();
	    /** @var Faq $faq */
	    $faq = Utils::setter($faq, $this->fields, $data);
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
	
	/**
	 * @FOS\RestBundle\Controller\Annotations\View()
	 * @FOS\RestBundle\Controller\Annotations\Delete("/faqs/{id}")
	 * @Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter("faq", class="PuzzleApiExpertiseBundle:Faq")
	 */
	public function deleteExpertiseFaqAction(Request $request, Faq $faq) {
	    if ($faq->getCreatedBy()->getId() !== $this->getUser()->getId()) {
	        return $this->handleView($this->errorFactory->badRequest($request));
	    }
	    
	    /** @var Doctrine\ORM\EntityManager $em */
	    $em = $this->doctrine->getManager($this->connection);
	    $em->remove($faq);
	    $em->flush();
	    
	    return $this->handleView(FormatUtil::formatView($request, ['code' => 200]));
	}
}