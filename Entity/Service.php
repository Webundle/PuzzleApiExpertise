<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Doctrine\Common\Collections\Collection;
use Puzzle\OAuthServerBundle\Traits\ExprTrait;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Puzzle\OAuthServerBundle\Traits\Describable;
use Puzzle\OAuthServerBundle\Traits\Nameable;

/**
 * Expertise Service
 *
 * @ORM\Table(name="expertise_service")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_service")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_expertise_service",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "parent",
 *     embedded = "expr(object.getParent())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getParent() === null)"),
 *     href = @Hateoas\Route(
 * 			"get_expertise_service", 
 * 			parameters = {"id" = "expr(object.getParent().getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "childs",
 *     href = @Hateoas\Route(
 * 			"get_expertise_services", 
 * 			parameters = {"filter" = "parent==expr(object.getId())"},
 * 			absolute = true
 * ))
 * 
 * @Hateoas\Relation(
 *     name = "count_childs",
 *     embedded = "expr(object.count(object.getChilds()))"
 * ))
 */
class Service
{
    use PrimaryKeyable,
        Sluggable,
        Timestampable,
        ExprTrait,
        Blameable,
        Nameable,
        Pictureable,
        Describable
    ;
    
    /**
     * @var string
     * @ORM\Column(name="short_description", type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $shortDescription;

    /**
     * @ORM\Column(name="slug", type="string", length=255)
     * @var string
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $slug;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $picture;
    
    /**
     * @var string
     * @ORM\Column(name="class_icon", type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $classIcon;
    
    /**
     * @var integer
     * @ORM\Column(name="ranking", type="integer", nullable=true)
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $ranking;
    
    /**
     * @ORM\OneToMany(targetEntity="Service", mappedBy="parent")
     */
    private $childs;
    
    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="childs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="service", fetch="EXTRA_LAZY", cascade={"persist","remove"})
     * @var Collection $projects
     */
    private $projects;
    
    /**
     * @ORM\OneToMany(targetEntity="Staff", mappedBy="service", fetch="EXTRA_LAZY", cascade={"persist","remove"})
     * @var Collection $staffs
     */
    private $staffs;
    
    public function __construct() {
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getSluggableFields() {
        return [ 'name' ];
    }
    
    public function generateSlugValue($values) {
        return implode('-', $values);
    }
	
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setShortDescription() {
        $this->shortDescription = strlen($this->description) > 100 ?
        mb_strimwidth($this->description, 0, 100, '...') : $this->description;
        return $this;
    }
    
    public function getShortDescription() :?string {
        return $this->shortDescription;
    }
   
    public function addProject(Project $project) : self {
        if ($this->projects === null ||$this->projects->contains($project) === false) {
            $this->projects->add($project);
        }

        return $this;
    }

    public function removeProject(Project $project) : self {
        $this->projects->removeElement($project);
    }

    public function getProjects() :? Collection {
        return $this->projects;
    }

    public function setClassIcon($classIcon) : self {
        $this->classIcon = $classIcon;
        return $this;
    }

    public function getClassIcon() :? string {
        return $this->classIcon;
    }
    
    public function setRanking($ranking) : self {
        $this->ranking = $ranking;
        return $this;
    }
    
    public function getRanking():? int {
        return $this->ranking;
    }
    
    public function setParent(Service $parent = null) {
        $this->parent = $parent;
        return $this;
    }
    
    public function getParent() :?self {
        return $this->parent;
    }
    
    public function addChild(Service $child) :self {
        $this->childs[] = $child;
        return $this;
    }
    
    public function removeChild(Service $child) :self {
        $this->childs->removeElement($child);
        return $this;
    }
    
    public function getChilds() :?Collection {
        return $this->childs;
    }
    
    public function setStaffs(Collection $staffs = null) : self {
        $this->staffs = $staffs;
        return $this;
    }
    
    public function addStaff(Staff $staff) : self {
        if ($this->staffs === null || $this->staffs->contains($staff) === false) {
            $this->staffs->add($staff);
        }
        
        return $this;
    }
    
    public function removeStaff(Staff $staff) : self {
        $this->staffs->removeElement($staff);
        return $this;
    }
    
    public function getStaffs() :? Collection {
        return $this->staffs;
    }
}
