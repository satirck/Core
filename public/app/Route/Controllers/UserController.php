<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Response\HtmlResponse;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};

use App\Models\User;
use App\Repository\Exceptions\EntityNotFoundException;
use App\Repository\UserRepository;

use App\Route\Exceptions\StatusErrorException;
use Exception;

#[DomainKeyAttribute('/users')]
class UserController implements RouteControllerInterface
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    #[MethodRouteAttribute('GET', '/users')]
    public function index(): void
    {
        $users = $this->userRepository->getAll();

        $data['users'] = $users;

        HtmlResponse::View('users', $data);
    }

    /**
     * @throws Exception
     */
    #[MethodRouteAttribute('GET', '/users/{id}')]
    public function getUserById(int $id): void
    {
        $data = array();
        try {
            $user = $this->userRepository->getById($id);
            $data['user'] = $user;
        }catch (EntityNotFoundException $exception){
            throw new StatusErrorException( $exception->getMessage(), 404);
        }

        HtmlResponse::View('user',  $data);
    }

    #[MethodRouteAttribute('POST', '/users')]
    public function createUser(User $user): void
    {
        $data = array();

        $data['user'] = $savedUser = $this->userRepository->save(json_encode($user));

        HtmlResponse::View('user',  $data);
    }


}
