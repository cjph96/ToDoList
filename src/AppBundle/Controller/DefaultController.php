<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\TODO;
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
                $entityManager->persist($todo);
                $entityManager->flush();

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->json('Error');

    }
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
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

        return $this->redirectToRoute('homepage');
    }
}
