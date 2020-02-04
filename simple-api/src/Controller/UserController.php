<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Profile;use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;use Doctrine\ODM\MongoDB\MongoDBException;use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    private const CACHE_KEY = 'users';

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("/users", methods={"GET"})
     */
    public function list()
    {
        $users = $this->cache->getItem(self::CACHE_KEY);
        if (!$users->isHit()) {
            $users->set($this->getRepository()->findAll())
                ->expiresAt(new \DateTime('+1 hour'));
            $this->cache->save($users);
        }

        return $this->json($users->get());
    }

    /**
     * @Route("/users/{id}", methods={"GET"})
     * @param $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $user = $this->getRepository()->find($id);
        if (!$user) {
            return $this->json(null, 404);
        }

        return $this->json($user);
    }

    /**
     * @Route("/users", methods={"POST"})
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function create()
    {
        $em = $this->getDoctrine()->getManager();

        $user = new User();
        $user->setName("User_" . random_int(10000, 100000));

        $em->persist($user);
        $em->flush();

        $this->cache->deleteItem(self::CACHE_KEY);

        return $this->json($user, 201);
    }

    /**
     * @Route("/users/{id}", methods={"DELETE"})
     * @param $id
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function delete($id)
    {
        $user = $this->getRepository()->find($id);
        if (!$user) {
            return $this->json(null, 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $this->cache->deleteItem(self::CACHE_KEY);

        return $this->json(null, 204);
    }

    /**
     * @Route("/users/{id}/profile", methods={"GET"})
     * @param DocumentManager $dm
     * @param $id
     *
     * @return JsonResponse
     */
    public function getProfile(DocumentManager $dm, $id)
    {
        $profile = $this->getProfileByUserId($dm, $id);
        if (!$profile){
            return $this->json(null, 404);
        }

        return $this->json($profile);
    }

    /**
     * @Route("/users/{id}/profile", methods={"PUT"})
     * @param $id
     *
     * @return JsonResponse
     * @throws MongoDBException
     * @throws \Exception
     */
    public function updateProfile(DocumentManager $dm, $id)
    {
        $profile = $this->getProfileByUserId($dm, $id);
        if (!$profile) {
            $profile = new Profile();
            $profile->setUserId((int)$id);
        }

        $profile->setAddress("Address_" . random_int(10000, 100000));
        $profile->setPhone("Phone_" . random_int(10000, 100000));

        if (!$dm->contains($profile)) {
            $dm->persist($profile);
        }

        $dm->flush();

        return $this->json($profile);
    }

    private function getProfileByUserId(DocumentManager $dm, $id)
    {
        return $dm->getRepository(Profile::class)
            ->findOneBy(["userId" => (int)$id]);
    }

    private function getRepository()
    {
        return $this->getDoctrine()->getRepository(User::class);
    }
}
