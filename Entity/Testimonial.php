<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * Expertise Testimonial
 *
 * @ORM\Table(name="expertise_testimonial")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_testimonial")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_expertise_testimonial",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 */
class Testimonial
{
    use PrimaryKeyable,
        Pictureable,
        Timestampable,
        Blameable;
    
    /**
     * @var string
     * @ORM\Column(name="author", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $author;

    /**
     * @var string
     * @ORM\Column(name="company", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $company;
    
    /**
     * @var string
     * @ORM\Column(name="position", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $position;
    
    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $message;
    
    public function setAuthor($author) : self {
    	$this->author = $author;
        return $this;
    }

    public function getAuthor() :? string {
    	return $this->author;
    }

    public function setCompany($company) : self {
        $this->company = $company;
        return $this;
    }

    public function getCompany() :? string {
        return $this->company;
    }

    public function setPosition($position) : self {
        $this->position = $position;
        return $this;
    }

    public function getPosition() :? string {
        return $this->position;
    }
    
    public function setMessage($message) : self {
        $this->message = $message;
        return $this;
    }
    
    public function getMessage() :? string {
        return $this->message;
    }
}
