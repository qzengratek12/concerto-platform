<?php

namespace Concerto\PanelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Concerto\PanelBundle\Service\TestService;
use Concerto\PanelBundle\Service\TestNodeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 * @Security("has_role('ROLE_TEST') or has_role('ROLE_SUPER_ADMIN')")
 */
class TestNodeController extends ASectionController
{

    const ENTITY_NAME = "TestNode";

    private $testService;

    public function __construct(EngineInterface $templating, TestNodeService $nodeService, TestService $testService, TranslatorInterface $translator, TokenStorageInterface $securityTokenStorage)
    {
        parent::__construct($templating, $nodeService, $translator, $securityTokenStorage);

        $this->entityName = self::ENTITY_NAME;
        $this->testService = $testService;
    }

    /**
     * @Route("/TestNode/fetch/{object_id}/{format}", name="TestNode_object", defaults={"format":"json"})
     * @param $object_id
     * @param string $format
     * @return Response
     */
    public function objectAction($object_id, $format = "json")
    {
        return parent::objectAction($object_id, $format);
    }

    /**
     * @Route("/TestNode/collection/{format}", name="TestNode_collection", defaults={"format":"json"})
     * @param string $format
     * @return Response
     */
    public function collectionAction($format = "json")
    {
        return parent::collectionAction($format);
    }

    /**
     * @Route("/TestNode/flow/{test_id}/collection", name="TestNode_collection_by_flow_test")
     * @param $test_id
     * @return Response
     */
    public function collectionByFlowTestAction($test_id)
    {
        return $this->templating->renderResponse('ConcertoPanelBundle::collection.json.twig', array(
            'collection' => $this->service->getByFlowTest($test_id)
        ));
    }

    /**
     * @Route("/TestNode/{object_ids}/delete", name="TestNode_delete", methods={"POST"})
     * @param string $object_ids
     * @return Response
     */
    public function deleteAction($object_ids)
    {
        return parent::deleteAction($object_ids);
    }

    /**
     * @Route("/TestNode/{object_id}/save", name="TestNode_save", methods={"POST"})
     * @param Request $request
     * @param $object_id
     * @return Response
     */
    public function saveAction(Request $request, $object_id)
    {
        $result = $this->service->save(
            $this->securityTokenStorage->getToken()->getUser(),
            $object_id,
            $request->get("type"),
            $request->get("posX"),
            $request->get("posY"),
            $this->testService->get($request->get("flowTest")),
            $this->testService->get($request->get("sourceTest")),
            $request->get("title"));
        return $this->getSaveResponse($result);
    }

    /**
     * @Route("/TestNode/ports/expose", name="TestNode_expose_ports", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function exposePorts(Request $request)
    {
        $this->service->exposePorts(
            json_decode($request->get("exposedPorts"), true)
        );
        $response = new Response(json_encode(array("result" => 0)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/TestNode/{object_id}/port/{type}/add", name="TestNode_add_dynamic_port", methods={"POST"})
     * @param Request $request
     * @param $object_id
     * @param $type
     * @return Response
     */
    public function addDynamicPort(Request $request, $object_id, $type)
    {
        $result = $this->service->addDynamicPort(
            $this->securityTokenStorage->getToken()->getUser(),
            $object_id,
            $request->get("name"),
            (integer)$type
        );

        $response = null;
        if (count($result["errors"]) > 0) {
            $errors = array();
            foreach ($result["errors"] as $error) {
                array_push($errors, $this->translator->trans($error));
            }
            $response = new Response(json_encode(array(
                "result" => 1,
                "errors" => $errors
            )));
        } else {
            $response = new Response(json_encode(array(
                "result" => 0,
                "object" => json_encode($result["object"])
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
