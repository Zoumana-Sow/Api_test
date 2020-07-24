<?php

namespace App\Controller;

use App\Entity\Region;
use Symfony\Flex\Response;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/regions/api", name="api_add_region_api",methods={"GET"})
     */
    public function AddRegionByApi(SerializerInterface $serializer)
    {
        //Recuperation des Regions en Json
        $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
       /*
       //Méthode 1
        //Decode JSON vers le Tableau
       $regionTab=$serializer->decode($regionJson,"json");
       //Denormalisation
       $regionObject=$serializer->denormalize($regionTab, 'App\Entity\Region[]');
        dd($regionObject);
        */
        //Méthode 2
        $regionObject = $serializer->deserialize($regionJson,'App\Entity\Region[]','json');
        $entityManager = $this->getDoctrine()->getManager();
       foreach($regionObject as $region){
           $entityManager->persist($region);

        }
           $entityManager->flush();
           return new JsonResponse("succes",201,[],true);

    }
    /**
     * @Route("/api/regions", name="api_show_region",methods={"GET"})
     */
    public function showRegion(SerializerInterface $serializer, RegionRepository $repo)
    {
        $regionsObject=$repo->findAll();
         $regionsJson =$serializer->serialize($regionsObject,"json",
         [
            "groups"=>["region:read_all"]
            ]
        );
        // dd($regionsJson);
           return new JsonResponse($regionsJson,201,[],true);
    }
      /**
     * @Route("/api/regions", name="api_add_region",methods={"POST"})
     */
    public function addRegion(SerializerInterface $serializer, Request $request, ValidatorInterface $validator)
    {
        //Recupérer le contenu du Body de la requete
        $regionJson=$request->getContent();
        $region = $serializer->deserialize($request->getContent(), Region::class,'json');
        //validation
           $errors = $validator->validate($region);
        if (count($errors) > 0) {
        $errorsString =$serializer->serialize($errors,"json");
        return new JsonResponse( $errorsString ,201,[],true);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($region);
        $entityManager->flush();
        return new JsonResponse("succes",201,[],true);

    }
    
}
