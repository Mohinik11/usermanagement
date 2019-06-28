<?php

namespace UserManagement\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use UserManagement\Entity\Group;
use UserManagement\Entity\User;
use UserManagement\Form\UserType;
use UserManagement\Service\DataHandlerService;

/**
 *		@SWG\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         type="string",
 *         default="Bearer TOKEN",
 *         description="Authorization"
 *     )
 */
class UserController extends FOSRestController
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
     *     description="Returns all the users",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * 
     * @SWG\Tag(name="users")
     * @Security(name="Bearer")
     * @Annotations\Get(path="/users")
     */
    public function indexAction()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        if (!$user) {
            throw $this->createNotFoundException('user not found');
        }

        return new Response($this->dataService->serialize($user), Response::HTTP_OK);
    }

    /**
     * Fetch the User by Id.
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the details of the user",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="users")
     * @Security(name="Bearer")
     *
     * @Annotations\Get(path="/users/{id}")
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }

        return new Response(
            $this->dataService->serialize($user), 
            Response::HTTP_OK
        );
    }

    /** 
     * Create new user
     *
     * @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="json user object",
     *       type="string",
     *       required=true,
     *       default={"name": "test user", "city": "test city"},
     * 
     *       @SWG\Schema(
     *              type="object",
     *              @SWG\Items(ref=@Model(type=User::class))
     *             
     *          )
     *     ),
     * @SWG\Tag(name="users") 
     * @SWG\Response(
     *     response=201,
     *     description="User Object"
     * )
     * @Security(name="Bearer")
     * @Annotations\Post(path="/users")
     */
    public function postUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        $groups = $request->get('groups');
        if ($groups && count($groups) > 0 ) {
            foreach ($groups as $value) {
                $group = $this->getDoctrine()
                    ->getRepository(Group::class)
                    ->findOneById($value);
                if ($group) {
                    $user->addUserGroups($group);
                } else {
                    throw $this->createNotFoundException('group not found');
                }
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new Response(
                $this->dataService->serialize($user), 
                Response::HTTP_CREATED
            );
        }

        return $this->dataService->createErrorResponse($form);
    }

    /**
     * Add User to Group
     *
     * @SWG\Tag(name="userGroups") 
     * @SWG\Response(
     *     response=201,
     *     description="Status Code"
     * )
     * @Security(name="Bearer")
     * @Annotations\Post(path="/users/{userId}/groups/{groupId}")
     */
    public function postUserGroupAction($userId, $groupId)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneById($userId);
        if (!$user) {
            throw $this->createNotFoundException('user not found');
        }
        $group = $this->getDoctrine()
            ->getRepository(Group::class)
            ->findOneById($groupId);
        if (!$group) {
            throw $this->createNotFoundException('group not found');
        }
        $user->addUserGroups($group);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new Response(
            $this->dataService->serialize($user), 
            Response::HTTP_CREATED
        );
    }

    /** 
     * Removes User from Group
     *
     * @SWG\Tag(name="userGroups") 
     * @SWG\Response(
     *     response=201,
     *     description="Status Code"
     * )
     * @Security(name="Bearer")
     * @Annotations\Delete(path="/users/{userId}/groups/{groupId}")
     */
    public function deleteUserGroupAction($userId, $groupId)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneById($userId);
        if (!$user) {
            throw $this->createNotFoundException('user not found');
        }
        $group = $this->getDoctrine()
            ->getRepository(Group::class)
            ->findOneById($groupId);
        if (!$group) {
            throw $this->createNotFoundException('group not found');
        }
        $user->removeUserGroups($group);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'ok'], Response::HTTP_CREATED);
    }

    /**
     * Update a user
     *
     * @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="json user object",
     *       type="string",
     *       required=true,
     *       default={"name": "User 01"},
     *       @SWG\Schema(
     *              type="object",
     *              @SWG\Items(ref=@Model(type=User::class))
     *          )
     *     ),
     * @SWG\Tag(name="users") 
     * @SWG\Response(
     *     response=200,
     *     description="Status Code"
     * )
     * @Annotations\Put(path="/users/{id}")
     */
    public function putUserAction($id, Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
        if (empty($user)) {
            throw $this->createNotFoundException('user not found');
        }
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        $groups = $request->get('groups');
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($groups && count($groups) > 0 ) {
                foreach ($groups as $value) {
                    $group = $this->getDoctrine()
                        ->getRepository(Group::class)
                        ->findOneById($value);
                    if ($group) {
                        $user->addUserGroups($group);
                    } else {
                        throw $this->createNotFoundException(
                            "Group with id $value does not exist"
                        );
                    }
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new Response(
                $this->dataService->serialize($user), 
                Response::HTTP_OK
            );
        }

        return $this->dataService->createErrorResponse($form);
    }

    /**
     * Delete a user
     *
     * @SWG\Tag(name="users") 
     * @SWG\Response(
     *     response=200,
     *     description="Status Code"
     * )
     * @Annotations\Delete(path="/users/{id}")
     */
    public function deleteUserAction($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }        
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        
        return new JsonResponse(['status' => 'ok'], Response::HTTP_OK);
    }

}
