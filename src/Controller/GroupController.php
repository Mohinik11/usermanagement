<?php

namespace UserManagement\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use UserManagement\Entity\Group;
use UserManagement\Form\GroupType;
use UserManagement\Service\DataHandlerService;

/**
 * @SWG\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         type="string",
 *         default="Bearer TOKEN",
 *         description="Authorization"
 *     )
 */
class GroupController extends FOSRestController
{

	/**
	 * @var DataHandlerService
	 */
	protected $dataService;

	public function __construct(DataHandlerService $ds)
	{
		$this->dataService = $ds;
	}

    /**
     * @SWG\Response(
     *     response=200,
     *     description="Returns the all groups",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Group::class))
     *     )
     * )
     * @SWG\Tag(name="groups")
     * @Security(name="Bearer")
     * @Annotations\Get(path="/groups")
     */
    public function indexAction()
    {
        $group = $this->getDoctrine()
            ->getRepository(Group::class)
            ->findAll();
        if (!$group) {
            throw $this->createNotFoundException('group not found');
        }

        return new Response(
            $this->dataService->serialize($group), 
            Response::HTTP_OK
        );
    }

    /**
     * Fetch the Group by Id.
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the details of the group",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Group::class))
     *     )
     * )
     * @SWG\Tag(name="groups")
     * @Security(name="Bearer")
     *
     * @Annotations\Get(path="/groups/{id}")
     */
    public function getGroupAction($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Group::class)
            ->findOneById($id);
        if (!$group) {
            throw $this->createNotFoundException('The group does not exist');
        }

        return new Response(
            $this->dataService->serialize($group), 
            Response::HTTP_OK
        );
    }

    /** 
     * Create new group
     *
     * @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="json group object",
     *       type="string",
     *       required=true,
     *       default={"name": "test group"},
     * 
     *       @SWG\Schema(
     *              type="object",
     *              @SWG\Items(ref=@Model(type=Group::class))
     *             
     *          )
     *     ),
     * @SWG\Tag(name="groups") 
     * @SWG\Response(
     *     response=201,
     *     description="Status Code"
     * )
     * @Security(name="Bearer")
     * @Annotations\Post(path="/groups")
     */
    public function postGroupAction(Request $request)
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            return new Response(
                $this->dataService->serialize($group), 
                Response::HTTP_CREATED
            );
        }

        return $this->dataService->createErrorResponse($form);
    }

    /**
     * Delete a group
     *
     * @SWG\Tag(name="groups") 
     * @SWG\Response(
     *     response=200,
     *     description="Status Code"
     * )
     * @Security(name="Bearer")
     * @Annotations\Delete(path="/groups/{id}")
     */
    public function deleteGroupAction($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Group::class)
            ->find($id);
        if (!$group) {
            throw $this->createNotFoundException('The group does not exist');
        }
        if (count($group->getGroupUsers()) > 0 ) {
            return $this->dataService->createCustomErrorResponse(
                [
                    'The group can not be deleted as it has users associated with it'
                ]
            );
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($group);
        $em->flush();
        
        return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
    }

}
