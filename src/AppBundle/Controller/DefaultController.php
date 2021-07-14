<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\TODO;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/", methods={"POST"})
     */
    public function add(Request $request)
    {
        if( $request->request ){
            $req = $request->request;
            $name = $req->get('name');

            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $todo = new TODO($name);

                //Añadir tarea al usuario que la crea
                $id_user = $this->getUser()->getId();
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($id_user);
                
                $todo->setUser($user);

                $entityManager->persist($todo);
                $entityManager->flush();

                if( $request->isXmlHttpRequest() ) {
                    return $this->json('TODO creado con exito');
                }

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->json('Error');

    }

    /**
     * @Route("/users/add", methods={"POST"})
     */
    public function add_user(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if( $request->request ){
            $req = $request->request;
            $name = $req->get('name') ?? false;
            $pass = $req->get('password') ?? 1234;

            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $user = new User();
                $user->setUsername($name);
                $user->setPassword($pass);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->json('Usuario creado con exito');

            }
        }

        return $this->json('Error',404);

    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $id_user = $this->getUser()->getId();
        
        $todoList = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->getByUserID($id_user);

        return $this->render('todo/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'todoList' => $todoList,
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin(Request $request)
    {
        $todoList = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->findAll();

        return $this->render('todo/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'todoList' => $todoList,
        ]);
    }

    /**
     * @Route("/complete/{id_todo}")
     */
    public function complete(Request $request, $id_todo)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $todo = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->find($id_todo);

        $todo->setStatus(1);
        $entityManager->persist($todo);
        $entityManager->flush();

        if( $request->isXmlHttpRequest() ) {
            return $this->json('TODO completado');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/TODO/{id_todo}/{id_user}")
     */
    public function update_TODO_user(Request $request, $id_todo, $id_user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $todo = $this->getDoctrine()
            ->getRepository(TODO::class)
            ->find($id_todo);

        $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($id_user)
                ?? false;
        if($user === false)
            return $this->json('No existe ningún usuario con ese id',404);

        $todo->setUser($user);

        $entityManager->persist($todo);
        $entityManager->flush();
        return $this->json('Cambio de usuario completado');

    }

     /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }
}
