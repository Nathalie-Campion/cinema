<?php

namespace App\Controller;

use App\Entity\Acteur;
use App\Repository\ActeurRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; 
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

//Pour serializer et renvoyer du json
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class ActeurController extends AbstractController
{
    /**
     * @Route("/acteur/add", name="acteur_add", methods={"POST"})
     */
    public function AddActeur(Request $request)
    {    
        $data=json_decode($request->getContent(),true);
        $acteur= new Acteur($data['name'], $data['firstname'], $data['birth'], $data['gender'], $data['nationality']);
        // var_dump($data);    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($acteur);
        $entityManager->flush();
        return $this->json($data);
    }

    /**
     * @Route("/acteur", name="all_acteurs", methods={"GET"})
     */
    public function listActeurs(ActeurRepository $acteurRepository)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $acteur = $acteurRepository->findAll();
        $acteur = $serializer->serialize($acteur, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($acteur, 200, ['Content-Type' => 'application/json']);
        // return $this->json($pays);
    }

    /**
     * @Route("/acteur/{id}", name="one_acteur", methods={"GET"})
     */
    public function listOneActeur(ActeurRepository $acteurRepository, $id)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $acteur = $acteurRepository->find($id);
        $acteur = $serializer->serialize($acteur, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($acteur, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/acteur/delete/{id}", name="acteur_delete", methods={"DELETE"})
     */
    public function deleteActeur(ActeurRepository $acteurRepository, $id)
    {
        $acteur = $acteurRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($acteur);
        $entityManager->flush();       
        return $this->json("Acteur supprimé");
    }
    
    /**
     * @Route("/acteur/edit/{id}", name="acteur_edit", methods={"PUT"})
     */
    public function editActeur(ActeurRepository $acteurRepository, Request $request, $id)
    {
        $data= json_decode($request->getContent(), true);
        $acteur = $acteurRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$acteur) {
            throw $this->createNotFoundException(
                'No acteur found for id '.$id
            );
        }      
        $acteur->setName($data['name']);
        $acteur->setFirstname($data['firstname']);
        $acteur->setBirth($data['birth']);
        $acteur->setGender($data['gender']);
        $acteur->setNationality($data['nationality']);
        $entityManager->flush();
        return $this->json("Acteur edité");
    }
}


