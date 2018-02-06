<?php

namespace BookBundle\Controller;

use BookBundle\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Book controller.
 *
 * @Route("/")
 */
class BookController extends Controller
{
    /**
     * Lists all book entities.
     *
     * @Route("/", name="_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $cache = new FilesystemAdapter;
        $booksAll = $cache->getItem('books.all');

        if (!$booksAll->isHit()) {
            $em = $this->getDoctrine()->getManager();
            $books = $em->getRepository('BookBundle:Book')->findAll();
            $booksAll->set($books);
            $booksAll->expiresAfter(\DateInterval::createFromDateString('24 hour'));
            $cache->save($booksAll);
        }

        $booksCache = $booksAll->get();

        return $this->render('@Book/book/index.html.twig', array(
            'books' => $booksCache,
        ));
    }

    /**
     * Creates a new book entity.
     *
     * @Route("/new", name="_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $book = new Book();
        $form = $this->createForm('BookBundle\Form\BookType', $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('_index');
        }

        return $this->render('@Book/book/new.html.twig', array(
            'book' => $book,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing book entity.
     *
     * @Route("/{id}/edit", name="_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Book $book)
    {
        $uploadsPath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads';
        $cover = $book->getCover();
        $file = $book->getFile();

        $deleteForm = $this->createDeleteForm($book);
        $editForm = $this->createForm('BookBundle\Form\BookType', $book);

        $editForm
            ->add(
                'clear_cover',
                CheckBoxType::class,
                array(
                    'label' => 'Clear cover',
                    'mapped' => false,
                    'required' => false,
                )
            )
            ->add(
                'clear_file',
                CheckBoxType::class,
                array(
                    'label' => 'Clear file',
                    'mapped' => false,
                    'required' => false,
                )
            );

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($editForm->get('clear_cover')->getData() && !empty($cover)) {
                unlink($uploadsPath."/covers/{$cover}");
                $book->clearCover();
            }

            if ($editForm->get('clear_file')->getData() && !empty($file)) {
                unlink($uploadsPath."/files/{$file}");
                $book->clearFile();
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('_edit', array('id' => $book->getId()));
        }

        return $this->render('@Book/book/edit.html.twig', array(
            'book' => $book,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a book entity.
     *
     * @Route("/book/{id}", name="_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Book $book)
    {
        $form = $this->createDeleteForm($book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($book);
            $em->flush();
        }

        return $this->redirectToRoute('_index');
    }

    /**
     * Creates a form to delete a book entity.
     *
     * @param Book $book The book entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Book $book)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('_delete', array('id' => $book->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
