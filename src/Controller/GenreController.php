<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//Pour serializer et renvoyer du json
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class GenreController extends AbstractController
{
    /**
     * @Route("/genre/add", name="genre_add", methods={"POST"})
     */
    public function AddGenre(Request $request)
    {    
        $data=json_decode($request->getContent(), true);
        var_dump($data);
        $genre = new Genre($data['name']);
        // var_dump($data);    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($genre);
        $entityManager->flush();
        return $this->json($data);
    }

    /**
     * @Route("/genre", name="all_genre", methods={"GET"})
     */
    public function listGenre(GenreRepository $genreRepository)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $genre = $genreRepository->findAll();
        $genre = $serializer->serialize($genre, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($genre, 200, ['Content-Type' => 'application/json']);
        // return $this->json($genre);
    }

    /**
     * @Route("/genre/{id}", name="one_genre", methods={"GET"})
     */
    public function listOneGenre(GenreRepository $genreRepository, $id)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $genre = $genreRepository->find($id);
        $genre = $serializer->serialize($genre, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($genre, 200, ['Content-Type' => 'application/json']);
        // return $this->json($genre);
    }

    /**
     * @Route("/genre/delete/{id}", name="genre_delete", methods={"DELETE"})
     */
    public function delete(genreRepository $genreRepository, $id)
    {
        $genre = $genreRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($genre);
        $entityManager->flush();       
        return $this->json("Genre supprimé");
    }
    
    /**
     * @Route("/genre/edit/{id}", name="genre_edit", methods={"PUT"})
     */
    public function editGenre(GenreRepository $genreRepository, Request $request, $id)
    {
        $data= json_decode($request->getContent(), true);
        $genre = $genreRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$genre) {
            throw $this->createNotFoundException(
                'No pays found for id '.$id
            );
        }      
        $genre->setName($data['name']);
        $entityManager->flush();
        return $this->json("Genre édité");
    }
}


