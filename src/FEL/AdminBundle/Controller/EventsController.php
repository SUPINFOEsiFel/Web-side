<?php

namespace FEL\AdminBundle\Controller;

use Buzz;
use FEL\AdminBundle\Form\NewEventForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Class EventsController
 * @package FEL\AdminBundle\Controller
 * @Route("/events")
 */
class EventsController extends Controller
{
    /**
     * Lists all Article entities.
     *
     * @Route("/", name="fel_admin_events_homepage")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $browser = new Buzz\Browser();
        $url = "http".(($this->container->getParameter(
                'meteor_secure'
            ) ) ? 's' : '')."://".$this->container->getParameter('meteor_host').":".(($this->container->getParameter(
                    'meteor_port'
                ) == null) ? "3000" : $this->container->getParameter('meteor_port'))."/api/";
        $response = null;
        try {
            $response = $browser->get(
                $url.'events/',
                array(
                    'X-User-Id' => $this->get('security.token_storage')->getToken()->getUser()->getUserid(),
                    'X-Auth-Token' => $this->get('security.token_storage')->getToken()->getUser()->getMeteortoken(),
                )
            );
        } catch (Buzz\Exception\RequestException $e) {
            throw new ServiceUnavailableHttpException(null, "Meteor Service Unavailable, cannot reach the server.");
        }

        $json = json_decode($response->getContent(), true);

        return array(
            "meteor" => $json
        );
    }

    /**
     * Creates a new Article entity.
     *
     * @Route("/")
     * @Method("POST")
     * @Template("FELAdminBundle:Events:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $browser = new Buzz\Browser();
            $url = "http".(($this->container->getParameter(
                    'meteor_secure'
                )) ? 's' : '')."://".$this->container->getParameter('meteor_host').":".(($this->container->getParameter(
                        'meteor_port'
                    ) == null) ? "3000" : $this->container->getParameter('meteor_port'))."/api/";
            $response = null;
            try {
                $response = $browser->post(
                    $url.'event/',
                    array(
                        'X-User-Id' => $this->get('security.token_storage')->getToken()->getUser()->getUserid(),
                        'X-Auth-Token' => $this->get('security.token_storage')->getToken()->getUser()->getMeteortoken(),
                    ),
                    $this->array2url($form->getData())
                );
                dump($response);
            } catch (Buzz\Exception\RequestException $e) {
                throw new ServiceUnavailableHttpException(null, "Meteor Service Unavailable, cannot reach the server.");
            }

            $json = json_decode($response->getContent(), true);

            if ($json["status"] == "fail") {
                dump($json);
                return array(
                    'form' => $form->createView(),
                );
            }

            return $this->redirect($this->generateUrl('fel_admin_events_show', array('id' => $json["id"])));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    private function array2url($data = array()){
        $ret = "";
        foreach($data as $key => $value){
            switch(gettype($value)){
                case "boolean":
                case "integer":
                case "double":
                case "string":
                case "array":
                    $ret .= "&" . urlencode((string)$key) . "=" . urlencode((string)$value);
                    break;
                case "object":
                    switch($value instanceof \DateTime){
                        default:
                            $ret .= "&" . urlencode((string)$key) . "=" . urlencode($value->format("d/m/Y H:i"));
                    }
                    break;
                default:
                    throw new Exception("Unknown type to be converted in string");
            }
        }
        return substr($ret, 1);
    }

    /**
     * Creates a form to create a Article entity.
     *
     * @param Article $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm()
    {
        $form = $this->createForm(
            new NewEventForm(),
            null,
            array(
                'action' => $this->generateUrl('fel_admin_events_create'),
                'method' => 'POST',
            )
        );

        $form->add('submit', 'submit', array('label' => 'Create', "attr" => array("class" => "btn-success")));

        return $form;
    }

    /**
     * Displays a form to create a new Article entity.
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createCreateForm();

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Article entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     * @param $id
     * @return array
     */
    public function showAction($id)
    {
        $browser = new Buzz\Browser();
        $url = "http".(($this->container->getParameter(
                'meteor_secure'
            )) ? 's' : '')."://".$this->container->getParameter('meteor_host').":".(($this->container->getParameter(
                    'meteor_port'
                ) == null) ? "3000" : $this->container->getParameter('meteor_port'))."/api/";
        $response = null;
        try {
            $response = $browser->get(
                $url.'event/'.$id,
                array(
                    'X-User-Id' => $this->get('security.token_storage')->getToken()->getUser()->getUserid(),
                    'X-Auth-Token' => $this->get('security.token_storage')->getToken()->getUser()->getMeteortoken(),
                )
            );
        } catch (Buzz\Exception\RequestException $e) {
            throw new ServiceUnavailableHttpException(null, "Meteor Service Unavailable, cannot reach the server.");
        }

        $json = json_decode($response->getContent(), true);

        if ($json["status"] == "fail") {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        return array(
            "meteor" => $json
        );
    }

    /**
     * Displays a form to edit an existing Article entity.
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     * @param $id
     * @return array
     */
//    public function editAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('FELAdminBundle:Article')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Article entity.');
//        }
//
//        $editForm = $this->createEditForm($entity);
//
//        return array(
//            'entity' => $entity,
//            'edit_form' => $editForm->createView(),
//        );
//    }

    /**
     * Creates a form to edit a Article entity.
     *
     * @param Article $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
//    private function createEditForm(Article $entity)
//    {
//        $form = $this->createForm(
//            new EditArticleForm(),
//            $entity,
//            array(
//                'action' => $this->generateUrl('fel_admin_events_update', array('id' => $entity->getId())),
//                'method' => 'PUT',
//            )
//        );
//
//        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        return $form;
//    }

    /**
     * Edits an existing Article entity.
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("FELAdminBundle:Events:edit.html.twig")
     * @param Request $request
     * @param $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
//    public function updateAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('FELAdminBundle:Article')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Article entity.');
//        }
//
//        $editForm = $this->createEditForm($entity);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isValid()) {
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('fel_admin_events_show', array('id' => $id)));
//        }
//
//        return array(
//            'entity' => $entity,
//            'edit_form' => $editForm->createView(),
//        );
//    }

    /**
     * Confirm deletes a Article entity.
     *
     * @Route("/{id}/delete")
     * @Method("GET")
     * @Template()
     * @param $id
     * @return array
     */
//    public function confirmAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('FELAdminBundle:Article')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Article entity.');
//        }
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity' => $entity,
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

    /**
     * Deletes a Article entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
//    public function deleteAction(Request $request, $id)
//    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('FELAdminBundle:Article')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find Article entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
//
//        return $this->redirect($this->generateUrl('fel_admin_events_homepage'));
//    }

    /**
     * Creates a form to delete a Article entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
//    private function createDeleteForm($id)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('fel_admin_events_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Supprimer', 'attr' => array('class' => 'btn btn-danger')))
//            ->getForm();
//    }

}
