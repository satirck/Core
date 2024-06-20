<?php

declare(strict_types=1);

namespace App\Route\Controllers;

use App\Repository\RepositoryInterface;
use App\Response\HttpHeaders;
use App\Response\ResponseInterface;
use App\Validation\UserValidator;
use App\Route\Attributes\{DomainKeyAttribute, MethodRouteAttribute};

use App\Models\User;
use App\Repository\Exceptions\EntityNotFoundException;
use App\Repository\UserRepository;

use App\Route\Exceptions\StatusErrorException;
use Exception;
use Monolog\Logger;

#[DomainKeyAttribute('/users')]
class UserController implements RouteControllerInterface
{
    protected RepositoryInterface $userRepository;
    protected UserValidator $userValidator;

    public function __construct(
        protected ResponseInterface $response,
        protected Logger            $logger,
    )
    {
        $this->userRepository = new UserRepository();
        $this->userValidator = new UserValidator();
    }

    #[MethodRouteAttribute('GET', '/users')]
    public function index(): void
    {
        $this->logger->info('get users in UserController at index');

        $users = $this->userRepository->getAll();

        $data['users'] = $users;

        $this->response->view(
            'users',
            $data,
            new HttpHeaders(
                'Get users',
                200,
            )
        );
    }

    /**
     * @throws Exception
     */
    #[MethodRouteAttribute('GET', '/users/{id}')]
    public function get(int $id): void
    {
        $this->logger->info('get users in user  at index');

        $data = array();
        try {
            $user = $this->userRepository->getById($id);
            $data['user'] = $user;

            $this->logger->info(
                sprintf('Get user with id %s', $id)
            );
        } catch (EntityNotFoundException $exception) {
            $this->logger->warning(
                sprintf('Not found user with id %s', $id)
            );

            throw new StatusErrorException($exception->getMessage(), 404);
        }

        $this->response->view(
            'user',
            $data,
            new HttpHeaders(
                'Get user',
                200,
            )
        );
    }

    #[MethodRouteAttribute('POST', '/users')]
    public function save(User $user): void
    {
        $data = array();

        $this->userValidator->setUser($user);
        $shouldSave = $this->userValidator->validate();

        if (!$shouldSave) {
            $this->logger->warning(
                sprintf(
                    'Cannot save user. Reasons: %s',
                    json_encode(
                        $this->userValidator->getMessages()
                    )
                )
            );

            $this->response->view(
                '400',
                $data,
                new HttpHeaders(
                    'Error saving users',
                    400
                )
            );
        } else {
            $savedUserJson = $this->userRepository->save(json_encode($user));
            $savedUser = User::fromJson($savedUserJson);
            $data['user'] = $savedUser;

            $this->response->view(
                'user',
                $data,
                new HttpHeaders(
                    sprintf(
                        'Saved with id [%s]!',
                        $savedUser->getId()
                    ),
                    201
                )
            );
        }
    }
}
