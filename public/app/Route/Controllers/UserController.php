<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Response\HtmlResponse;
use App\Response\ResponseInterface;
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

    public function __construct(
        protected ResponseInterface $response
    )
    {
        $this->userRepository = new UserRepository();
    }

    #[MethodRouteAttribute('GET', '/users')]
    public function index(): void
    {
        $users = $this->userRepository->getAll();

        $data['users'] = $users;

        $this->response->view(
            'users',
            $data,
            [
                ResponseInterface::HTTP_STATUS_CODE => 200,
                ResponseInterface::HTTP_MESSAGE_TEXT => 'Hello at users page',
            ]
        );
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
        } catch (EntityNotFoundException $exception) {
            throw new StatusErrorException($exception->getMessage(), 404);
        }

        $this->response->view(
            'user',
            $data,
            [
                ResponseInterface::HTTP_STATUS_CODE => 200,
                ResponseInterface::HTTP_MESSAGE_TEXT => 'Hello at user page',
            ]
        );
    }

    #[MethodRouteAttribute('POST', '/users')]
    public function createUser(User $user): void
    {
        $data = array();

//        $savedUser = $this->userRepository->save(json_encode($user));

//        $data['user'] = User::fromJson($savedUser);
        $data['user'] = $user;

        $this->response->view(
            'user',
            $data,
            [
                ResponseInterface::HTTP_STATUS_CODE => 200,
                ResponseInterface::HTTP_MESSAGE_TEXT => 'User have been created!',
            ]
        );
    }


}
