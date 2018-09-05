<?php
/**
 * Created by PhpStorm.
 * User: alikaviani
 * Date: 9/4/18
 * Time: 7:55 PM
 */

namespace App\Repositories;

use App\Models\Organization;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Collection;
use Psr\Container\ContainerInterface;

/**
 * Class Organization
 * @package App\Models
 * @property
 */
class OrganizationRepository
{
    /**
     *  Default
     */
    const DEFAULT_PAGE_SIZE = 100;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Organization constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // Set Container
        $this->container = $container;
    }

    /**
     * Create a new organization based on provided name and children
     *
     * @param string $organizationName
     * @param array $childOrganizations
     * @param array $result
     * @return array
     */
    public function create(string $organizationName, array $childOrganizations, array $result)
    {
        // Create the organization if not exists already
        if(!$this->findOrganizationByName($organizationName)){
            $organization = new Organization();
            $organization->name = $organizationName;
            $organization->save();
            $result['createdOrganizations']++;
        }

        foreach ($childOrganizations as $child) {
            if (!$this->findOrganizationByName($organizationName)) {
                $organization = new Organization();
                $organization->name = $organizationName;
                $organization->save();
                $result['createdOrganizations']++;
            }

            if (!$this->findRelation($organizationName,$child['org_name'])) {
                $relation = new Relation();
                $relation->parent = $organizationName;
                $relation->organization = $child['org_name'];
                $relation->save();
                $result['createdRelations']++;
            }

            $result = $this->create($child['org_name'], $child['daughters'] ? $child['daughters'] : [], $result);
        }
        return $result;

    }

    /**
     * Geterate the paginated result of organization relations based on it's name
     *
     * @param string $basePath
     * @param string $name
     * @param int|null $limit
     * @param int|null $page
     * @return array
     */
    public function showRelations(string $basePath, string $name, ?int $limit, ?int $page): array
    {
        $limit = $limit ? $limit : OrganizationRepository::DEFAULT_PAGE_SIZE;
        $page = $page ?? 1;

        // convert page number to offset, ex: [ page=1 -> offset=0 || page=2 -> offset=2 ]
        $offset = ($page -1) * $limit;

        $modelName = urldecode($name);
        $parents = $this->findParents($modelName); // Relation::find('all', ['conditions' => ['child=?', $modelName]]);
        $parentsArray = self::toArray($parents, 'parent', 'parent');
        $children = $this->findChildren($modelName); //Relation::find('all', ['conditions' => ['parent=?', $modelName]]);
        $childrenArray = self::toArray($children, 'organization', 'daughter');

        $resultArray = array_merge($parentsArray, $childrenArray);

        $sistersResultArray = [];
        foreach ($parents as $parent) {
            $sisters = $this->findSisters($parent->parent, $modelName); //Relation::find('all', ['conditions' => ['parent=? and child<>?', $parent->parent, $modelName]]);
            $sistersArray = self::toArray($sisters, 'organization', 'sister');
            foreach ($sistersArray as $array) {
                if (!in_array($array, $sistersResultArray)) $sistersResultArray[] = $array;
            }
        }

        $resultArray = array_merge($resultArray, $sistersResultArray);

        // result array sorting
        $dataNames = [];
        foreach($resultArray as $key => $arr){
            $dataNames[$key] = $arr['org_name'];
        }
        array_multisort($dataNames, SORT_ASC, $resultArray);

        // build pagination links

        $records = count($resultArray);
        $numberOfPages = ceil($records / $limit);
        $nextPage = ($page + 1 < $numberOfPages) ? $page+1 : $numberOfPages;
        $prevPage = ($page - 1 > 1) ? $page-1 : 1;

        $links = [
            'self'  => "$basePath?limit=$limit&page=$page",
            'first' => "$basePath?limit=$limit&page=1",
            'prev'  => "$basePath?limit=$limit&page=$prevPage",
            'next'  => "$basePath?limit=$limit&page=$nextPage",
            'last'  => "$basePath?limit=$limit&page=$numberOfPages"
        ];

        // result limitation
        $resultArray = array_splice($resultArray, $offset, $limit);

        return ["data" => $resultArray, "links" => $links];
    }

    /**
     * Finds the organization based on name
     *
     * @param $name
     * @return mixed
     */
    private function findOrganizationByName($name)
    {
        return Organization::where('name', $name)->first();
    }

    /**
     * Finds relation of parent <-> child if there is  any !
     *
     * @param string $parent
     * @param string $organization
     * @return mixed
     */
    private function findRelation(string $parent, string $organization)
    {
        return Relation::where("parent", $parent)->where("organization", $organization)->first();
    }

    /**
     * Finds the possible parents of a single organization
     * @param string $child
     * @return mixed
     */
    private function findParents(string $child)
    {
        return Relation::where("organization", $child)->get();
    }

    /**
     * Finds List of Children for a given organization name
     * @param string $parent
     * @return mixed
     */
    private function findChildren(string $parent)
    {
        return Relation::where("parent", $parent)->get();
    }

    /**
     * Finds List of Sisters for a given organization
     * @param string $parent
     * @param string $child
     * @return mixed
     */
    private function findSisters(string $parent, string $child)
    {
        return Relation::where('parent',$parent)->where('organization','<>', $child)->get();
    }

    /**
     * Convert Model Collection to Array
     * @param Collection $models
     * @param string $nameField
     * @param string $type
     * @return array
     */
    private static function toArray(Collection $models, string $nameField, string $type): array
    {
        $resultArray = [];
        foreach ($models as $model) {
            $resultArray[] = [
                'relationship_type' => $type,
                'org_name' => $model->{$nameField}
            ];
        }

        return $resultArray;
    }


}