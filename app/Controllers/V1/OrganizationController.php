<?php
/**
 * Created by PhpStorm.
 * User: alikaviani
 * Date: 9/4/18
 * Time: 7:24 PM
 */

namespace App\Controllers\V1;

use App\Repositories\OrganizationRepository;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;
use Slim\Http\Request;
use Slim\Http\Response;

class OrganizationController
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var OrganizationRepository $organizationRepository
     */
    protected $organizationRepository;

    /**
     * OrganizationController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->organizationRepository = new OrganizationRepository($container);
    }

    /**
     * Method to store organization and relationship in single json payload to Database
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function store(Request $request, Response $response)
    {
        $requestData = (array) $request->getParsedBody();
        if ($this->_inputArrayIsValid($requestData)){

            $data = $requestData;
            $parentOrganizationName = $data['org_name'];
            $children = $data['daughters'];
            $result = [
                'status' => "Organization(s) created successfully !",
                'createdOrganizations' => 0,
                'createdRelations' => 0
            ];
            $result = $this->organizationRepository->create($parentOrganizationName,$children,$result);
            return $response->withJson($result, 201);
        }else{
            return $response->withJson(['status' => "request body validation error, please check the input JSON"], 400);
        }
    }

    /**
     * Method to get queried result of an organization
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function index(Request $request, Response $response, $args)
    {
        $limit = $request->getQueryParam("limit", null);
        $page = $request->getQueryParam("page", null);
        $queryString = $args['organization'];
        $currentPath = $this->_getCurrentPath($request);
        $result = $this->organizationRepository->showRelations($currentPath, $queryString, $limit, $page);
        return $response->withJson($result, 200);
    }

    /**
     * Get the Base path of request without query parameters
     * @param Request $request
     * @return string
     */
    public function _getCurrentPath(Request $request)
    {
        //return $path_only = parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST);
        $URI = $request->getUri();
        $protocol = $URI->getScheme();
        $baseHost = $URI->getHost();
        $port = ($URI->getPort() && $URI->getPort() != 80) ? ":".$URI->getPort() :  "";
        $baseUrl = "$protocol://$baseHost" . $port;
        $path = $URI->getPath();
        return trim($baseUrl,'/') . '/' . trim($path, '/');
    }

    /**
     * @param array $inputArray
     * @param bool $valid
     * @return bool
     */
    public function _inputArrayIsValid(array $inputArray, $valid = true)
    {
        $result = v::key('org_name')->validate($inputArray);
        if (!$result)
        {
            $valid = false;
        }
        if (array_key_exists("daughters",$inputArray)){
            foreach ($inputArray['daughters'] as $daughter){
                $valid = $this->_inputArrayIsValid((array)$daughter, $valid);
            }
        }
        return $valid;
    }
}