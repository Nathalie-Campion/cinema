<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use App\Repository\ActeurRepository;

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

class FilmController extends AbstractController
{
    /**
     * @Route("/film/add", name="film_add", methods={"POST"})
     */
    public function AddFilm(Request $request, GenreRepository $genreRepository, ActeurRepository $acteurRepository)
    {                   
        $data=json_decode($request->getContent(),true);
        // var_dump($data);

        $genre = $genreRepository->find($data['genre']);
        // var_dump($genre);

        $film = new Film($data['title'], $data['description'], $data['year'], $data['picture'], $data['note'], $genre);
        // var_dump($film);    

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($film);
        $entityManager->flush();

        // foreach ($data)
        foreach($data['acteur'] as $acteur){
            $acteur = $acteurRepository->find($acteur);
            $film->addActeur($acteur);
            $entityManager->persist($film);   
        }
        $entityManager->flush();
        return $this->json($data);
    }

    /**
     * @Route("/film", name="film", methods={"GET"})
     */
    public function listFilms(FilmRepository $filmRepository)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $films = $filmRepository->findBy([], ['year'=>'ASC']);

        $films = $serializer->serialize($films, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($films, 200, ['Content-Type' => 'application/json']);
    } 

    /**
     * @Route("/film/edit/{id}", name="film_edit", methods={"PUT"})
     */
    public function editFilm(FilmRepository $filmRepository, GenreRepository $genreRepository, ActeurRepository $acteurRepository, Request $request, $id)
    {
        $data= json_decode($request->getContent(), true);
        $film = $filmRepository->find($id); 
        $entityManager = $this->getDoctrine()->getManager();
        if (!$film) {
            throw $this->createNotFoundException(
                'No film found for id '.$id
            );
        }      
        $film->setTitle($data['title']);
        $film->setDescription($data['description']);
        $film->setYear($data['year']);
        $film->setPicture($data['picture']);
        $film->setNote($data['note']);
        $genre = $genreRepository->find($data['genre']);
        $film->setGenre($genre);
    
        foreach($data['acteur'] as $acteur){
            $acteur = $acteurRepository->find($acteur);
            $film->addActeur($acteur);   
        }

        $entityManager->flush();
        return $this->json("Film édité");
    }

    /**
     * @Route("/film/delete/{id}", name="film_delete", methods={"DELETE"})
     */
    public function deleteFilm(FilmRepository $filmRepository, $id)
    {
        $film = $filmRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($film);
        $entityManager->flush();       
        return $this->json("Film supprimé");
    }
    
    /**
     * @Route("/film/{id}", name="one_film", methods={"GET"})
     */
    public function listOneFilm(FilmRepository $filmRepository, $id)
    {
        $encoders = [new JsonEncoder()]; // If no need for XmlEncoder
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $film = $filmRepository->find($id);
        $film = $serializer->serialize($film, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($film, 200, ['Content-Type' => 'application/json']);
    }
}


