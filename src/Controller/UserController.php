<?php

namespace UserManagement\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class UserController extends FOSRestController
{
    /**
     * @SWG\Response(
     *     response=200,
     *     description="Returns the all users",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="users")
     * @Annotations\Get(path="/users")
     */
    public function indexAction()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
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
     *         @SWG\Items(ref=@Model(type=Users::class))
     *     )
     * )
     * @SWG\Tag(name="users")
     *
     * @Annotations\Get(path="/users/{id}")
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(Users::class)
            ->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }

        return new Response($this->serialize($user), Response::HTTP_OK);
    }
}
