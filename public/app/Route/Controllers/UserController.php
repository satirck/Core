<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Repository\RepositoryInterface;
use App\Response\ResponseInterface;
use App\Validation\UserValidator;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};

use App\Models\User;
use App\Repository\Exceptions\EntityNotFoundException;
use App\Repository\UserRepository;

use App\Route\Exceptions\StatusErrorException;
use Exception;

#[DomainKeyAttribute('/users')]
class UserController implements RouteControllerInterface
{
    protected RepositoryInterface $userRepository;
    protected UserValidator $userValidator;

    public function __construct(
        protected ResponseInterface $response
    )
    {
        $this->userRepository = new UserRepository();
        $this->userValidator = new UserValidator();
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
                ResponseInterface::HTTP_ACTION_STATUS => 'Hello at users page',
                ResponseInterface::MESSAGES => [],
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
                ResponseInterface::HTTP_ACTION_STATUS => 'Hello at user page',
                ResponseInterface::MESSAGES => [],
            ]
        );
    }

    private function setBadRequestHeaders(): array
    {
        $headers[ResponseInterface::MESSAGES] = json_encode(
            $this->userValidator->getMessages()
        );
        $headers[ResponseInterface::HTTP_STATUS_CODE] = 400;
        $headers[ResponseInterface::HTTP_ACTION_STATUS] = 'Failed saving!';

        return $headers;
    }

    #[MethodRouteAttribute('POST', '/users')]
    public function createUser(User $user): void
    {
        $data = array();


        $this->userValidator->setUser($user);
        $shouldSave = $this->userValidator->validate();

        if (!$shouldSave) {
            $headers = $this->setBadRequestHeaders();

        }else{
            $headers[ResponseInterface::MESSAGES] = [];
            $savedUserJson = $this->userRepository->save(json_encode($user));
            $savedUser = User::fromJson($savedUserJson);
            $headers[ResponseInterface::HTTP_ACTION_STATUS] =
                sprintf(
                    'Saved with id [%s]!',
                    $savedUser->getId()
                );
            $data['user'] = $savedUser;
        }

        $this->response->view(
            'user',
            $data,
            $headers
        );
    }


}
