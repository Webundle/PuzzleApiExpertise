<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * Expertise Partner
 *
 * @ORM\Table(name="expertise_partner")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_partner")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_expertise_partner",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 */
class Partner
{
    use PrimaryKeyable,
        Timestampable,
        Nameable,
        Pictureable,
        Blameable;
    
    /**
     * @var string
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $location;
    
    public function setLocation($location) : self {
        $this->location = $location;
        return $this;
    }
    
    public function getLocation() :? string {
        return $this->location;
    }
}
